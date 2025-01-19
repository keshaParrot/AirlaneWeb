<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use controllers\FlightController;

class FlightControllerTest extends TestCase {

    private $pdo;
    private $controller;

    protected function setUp(): void {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=airlinemanagementtest', 'TicketWeb', 'Y1FKaOnZig1JLS(7');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $config = include './config/config.php';
        $jwtSecret = $config['jwtSecret'];
        $this->controller = new FlightController($this->pdo, $jwtSecret, 'airlinemanagementtest');

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE flights");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.flights (id, price, departure_date, arrival_date, departure_airport_id, destination_airport_id, airplane_id) VALUES 
            (1, 200.00, '2023-01-01 10:00:00', '2023-01-01 14:00:00', 1, 2, 1),
            (2, 150.00, '2023-01-02 09:00:00', '2023-01-02 13:00:00', 2, 3, 2)");
    }

    public function testGetFlights() {
        $_GET = [
            'departure' => 1,
            'destination' => 2,
            'minPrice' => 100,
            'maxPrice' => 300,
            'departureDate' => '2023-01-01',
            'limit' => 10,
            'offset' => 0,
        ];

        ob_start();
        $this->controller->handleRequest('GET', ['flights']);
        $output = ob_get_clean();

        $expected = [
            ['id' => 1, 'price' => 200.00, 'departure_date' => '2023-01-01 10:00:00', 'arrival_date' => '2023-01-01 14:00:00', 'departure_airport_id' => 1, 'destination_airport_id' => 2, 'airplane_id' => 1],
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testGetFlightById() {
        $_GET['id'] = 1;

        ob_start();
        $this->controller->handleRequest('GET', ['flights', 'get']);
        $output = ob_get_clean();

        $expected = ['id' => 1, 'price' => 200.00, 'departure_date' => '2023-01-01 10:00:00', 'arrival_date' => '2023-01-01 14:00:00', 'departure_airport_id' => 1, 'destination_airport_id' => 2, 'airplane_id' => 1];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testAddFlight() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        file_put_contents('php://input', json_encode([
            'price' => 300.00,
            'departureDateTime' => '2023-01-03 11:00:00',
            'arrivalDateTime' => '2023-01-03 15:00:00',
            'departureAirportId' => 1,
            'destinationAirportId' => 3,
            'airplaneId' => 1,
            'createdBy' => 1,
        ]));

        ob_start();
        $this->controller->handleRequest('POST', ['flights']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);

        $result = $this->pdo->query("SELECT * FROM airlinemanagementtest.flights WHERE price = 300.00")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $result);
    }

    protected function tearDown(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE flights");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}

