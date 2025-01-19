<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use controllers\UserController;
use repositories\UserRepository;
use services\UserService;

class UserControllerTest extends TestCase {

    private $pdo;
    private $controller;

    protected function setUp(): void {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=airlinemanagementtest', 'TicketWeb', 'Y1FKaOnZig1JLS(7');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->controller = new UserController($this->pdo, 'test_jwt_secret', "airlinemanagementtest");

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.users (id, email, password, first_name, last_name, wallet_balance, card_id, is_active, is_superuser) VALUES 
            (1, 'test1@example.com', 'hashed_password_1', 'John', 'Doe', 100.50, 1, 1, 0),
            (2, 'test2@example.com', 'hashed_password_2', 'Jane', 'Doe', 200.00, 2, 1, 1)");
    }

    public function testGetUserById() {
        $_GET['id'] = 1;

        ob_start();
        $this->controller->handleRequest('GET', ['users', 'get']);
        $output = ob_get_clean();

        $expected = [
            'id' => 1,
            'email' => 'test1@example.com',
            'password' => 'hashed_password_1',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'wallet_balance' => 100.50,
            'card_id' => 1,
            'is_active' => 1,
            'is_superuser' => 0
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testUpdateUser() {
        $updatedData = [
            'id' => 1,
            'email' => 'updated@example.com',
            'firstName' => 'Johnathan',
            'lastName' => 'Doe',
            'walletBalance' => 150.75,
            'cardId' => 2
        ];

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        file_put_contents('php://input', json_encode($updatedData));

        ob_start();
        $this->controller->handleRequest('PUT', ['users', 'update']);
        $output = ob_get_clean();

        $this->assertJsonStringEqualsJsonString(json_encode(["success" => true]), $output);
    }

    protected function tearDown(): void {
        $this->pdo->exec("TRUNCATE TABLE users");
    }
}

