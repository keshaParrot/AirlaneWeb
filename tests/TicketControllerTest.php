<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use controllers\TicketController;

class TicketControllerTest extends TestCase {

    private $pdo;
    private $controller;

    protected function setUp(): void {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=airlinemanagementtest', 'TicketWeb', 'Y1FKaOnZig1JLS(7');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $config = include './config/config.php';
        $jwtSecret = $config['jwtSecret'];

        $this->controller = new TicketController($this->pdo, $jwtSecret, 'airlinemanagementtest');

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE flights");
        $this->pdo->exec("TRUNCATE TABLE purchased_ticket");
        $this->pdo->exec("TRUNCATE TABLE transaction");
        $this->pdo->exec("TRUNCATE TABLE card");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("TRUNCATE TABLE airports");
        $this->pdo->exec("TRUNCATE TABLE airplanes");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->pdo->exec("ALTER TABLE airlinemanagementtest.flights AUTO_INCREMENT = 1");
        $this->pdo->exec("ALTER TABLE airlinemanagementtest.purchased_ticket AUTO_INCREMENT = 1");
        $this->pdo->exec("ALTER TABLE airlinemanagementtest.transaction AUTO_INCREMENT = 1");
        $this->pdo->exec("ALTER TABLE airlinemanagementtest.card AUTO_INCREMENT = 1");
        $this->pdo->exec("ALTER TABLE airlinemanagementtest.users AUTO_INCREMENT = 1");
        $this->pdo->exec("ALTER TABLE airlinemanagementtest.airports AUTO_INCREMENT = 1");
        $this->pdo->exec("ALTER TABLE airlinemanagementtest.airplanes AUTO_INCREMENT = 1");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.airports (name, country, region, city, street) VALUES 
                      ('JFK', 'USA', 'New York', 'New York', 'Main St'),
                      ('LAX', 'USA', 'California', 'Los Angeles', 'Sunset Blvd')");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.airplanes (brand, model, internal_number, registration_number, seat_count) VALUES 
                      ('Boeing', '737', 'INT123', 'REG123', 200)");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.card (card_number, expiry_date) VALUES 
                      (1234567890123456, '2025-12-31')");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.users (email, password, first_name, last_name, wallet_balance, card_id, is_active, is_superuser) VALUES 
                      ('testuser@example.com', 'password', 'John', 'Doe', 100.00, 1, 1, 0)");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.flights (price, departure_date, arrival_date, departure_airport_id, destination_airport_id, airplane_id) VALUES 
                      (200.00, '2023-01-01 10:00:00', '2023-01-01 14:00:00', 1, 2, 1)");
    }

    public function testSellTicket() {
        $_POST = json_encode([
            'flightId' => 1,
            'userId' => 1,
            'cardId' => 1,
            'ticketOwnerFullName' => 'John Doe',
            'paymentMethod' => 'credit_card',
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['tickets', 'sell']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);

        $result = $this->pdo->query("SELECT * FROM airlinemanagementtest.purchased_ticket WHERE user_id = 1")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $result);
        $this->assertEquals('John Doe', $result[0]['user_name']);
    }

    public function testRefundTicket() {
        $this->pdo->exec("INSERT INTO airlinemanagementtest.purchased_ticket (id, ticket_number, purchased_date, flight_id, user_id, user_name, price) 
                          VALUES (1, 'TICKET123', '2023-01-01', 1, 1, 'John Doe', 200.00)");

        $_POST = json_encode([
            'userId' => 1,
            'ticketId' => 1,
            'cardId' => 1,
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['tickets', 'refund']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);

        $result = $this->pdo->query("SELECT * FROM airlinemanagementtest.purchased_ticket WHERE id = 1")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNull($result);
    }

    public function testGetTicketsByUser() {
        $this->pdo->exec("INSERT INTO airlinemanagementtest.purchased_ticket (id, ticket_number, purchased_date, flight_id, user_id, user_name, price) 
                          VALUES (1, 'TICKET123', '2023-01-01', 1, 1, 'John Doe', 200.00)");

        $_GET['id'] = 1;

        ob_start();
        $this->controller->handleRequest('GET', ['tickets', 'user']);
        $output = ob_get_clean();

        $expected = [
            ['id' => 1, 'ticket_number' => 'TICKET123', 'purchased_date' => '2023-01-01', 'flight_id' => 1, 'user_id' => 1, 'user_name' => 'John Doe', 'price' => 200.00],
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    protected function tearDown(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE flights");
        $this->pdo->exec("TRUNCATE TABLE purchased_ticket");
        $this->pdo->exec("TRUNCATE TABLE transaction");
        $this->pdo->exec("TRUNCATE TABLE card");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}
