<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use controllers\PaymentController;

class PaymentControllerTest extends TestCase {

    private $pdo;
    private $controller;

    protected function setUp(): void {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=airlinemanagementtest', 'TicketWeb', 'Y1FKaOnZig1JLS(7');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $jwtSecret = 'test_secret_key';
        $this->controller = new PaymentController($this->pdo, $jwtSecret, 'airlinemanagementtest');

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("TRUNCATE TABLE card");
        $this->pdo->exec("TRUNCATE TABLE transaction");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.users (id, email, password, first_name, last_name, is_active, is_superuser) VALUES 
            (1, 'testuser@example.com', 'hashed_password', 'John', 'Doe', 1, 0)");
        $this->pdo->exec("INSERT INTO airlinemanagementtest.card (id, card_number, expiry_date) VALUES 
            (1, 1234567890123456, '2025-12-31')");
        $this->pdo->exec("INSERT INTO airlinemanagementtest.transaction (id, amount, transaction_date, transaction_type, user_id, card_id, payment_method) VALUES 
            (1, 100.00, '2023-01-01 10:00:00', 'deposit', 1, 1, 'credit_card')");
    }

    public function testGetUserCards() {
        $_GET['userId'] = 1;

        ob_start();
        $this->controller->handleRequest('GET', ['payments', 'user', 'cards']);
        $output = ob_get_clean();

        $expected = [
            ['id' => 1, 'card_number' => '1234567890123456', 'expiry_date' => '2025-12-31'],
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testDeposit() {
        $_POST = json_encode([
            'amount' => 50.00,
            'userId' => 1,
            'cardId' => 1,
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['payments', 'user', 'deposit']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);

        $result = $this->pdo->query("SELECT * FROM airlinemanagementtest.transaction WHERE amount = 50.00")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $result);
    }

    public function testAssignCard() {
        $_POST = json_encode([
            'userId' => 1,
            'cardNumber' => '9876543210987654',
            'expiryDate' => '2026-12-31',
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['payments', 'user', 'assign-card']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);

        $result = $this->pdo->query("SELECT * FROM airlinemanagementtest.card WHERE card_number = '9876543210987654'")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $result);
    }

    public function testRemoveCard() {
        $_GET = [
            'userId' => 1,
            'cardId' => 1,
        ];

        ob_start();
        $this->controller->handleRequest('DELETE', ['payments', 'user', 'remove-card']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);

        $result = $this->pdo->query("SELECT * FROM airlinemanagementtest.card WHERE id = 1")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNull($result);
    }

    protected function tearDown(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("TRUNCATE TABLE card");
        $this->pdo->exec("TRUNCATE TABLE transaction");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}


