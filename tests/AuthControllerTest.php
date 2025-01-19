<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use controllers\AuthController;
use repositories\UserRepository;
use repositories\VerificationCodeRepository;
use services\AuthService;

class AuthControllerTest extends TestCase {

    private $pdo;
    private $controller;

    protected function setUp(): void {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=airlinemanagementtest', 'TicketWeb', 'Y1FKaOnZig1JLS(7');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $config = include './config/config.php';
        $jwtSecret = $config['jwtSecret'];
        $this->controller = new AuthController($this->pdo, $jwtSecret, 'airlinemanagementtest');

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("TRUNCATE TABLE verification_codes");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.users (id, email, password, first_name, last_name, is_active, is_superuser) VALUES 
            (1, 'testuser@example.com', 'hashed_password', 'John', 'Doe', 1, 0)");
        $this->pdo->exec("INSERT INTO airlinemanagementtest.verification (Verification_id, User_id, Verification_code, used, Create_date) VALUES 
            (1, 1, '123456', 0, '2023-12-31')");
    }

    public function testLogin() {
        $_POST = json_encode([
            'email' => 'testuser@example.com',
            'password' => 'hashed_password',
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['auth', 'login']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"token":', $output);
    }

    public function testRegister() {
        $_POST = json_encode([
            'email' => 'newuser@example.com',
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'password' => 'secure_password',
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['auth', 'register']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);

        $result = $this->pdo->query("SELECT * FROM users WHERE email = 'newuser@example.com'")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $result);
    }

    public function testValidateMail() {
        $_POST = json_encode([
            'email' => 'testuser@example.com',
            'code' => '123456',
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['auth', 'validate-mail']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);
    }

    public function testRefreshToken() {
        $headers = ['Authorization' => 'Bearer valid_token'];
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid_token';

        ob_start();
        $this->controller->handleRequest('POST', ['auth', 'refresh']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);
    }

    protected function tearDown(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("TRUNCATE TABLE verification_codes");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}

