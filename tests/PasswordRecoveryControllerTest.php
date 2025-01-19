<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use controllers\PasswordRecoveryController;

class PasswordRecoveryControllerTest extends TestCase {

    private $pdo;
    private $controller;

    protected function setUp(): void {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=airlinemanagementtest', 'TicketWeb', 'Y1FKaOnZig1JLS(7');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->controller = new PasswordRecoveryController($this->pdo, 'airlinemanagementtest');

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("TRUNCATE TABLE verification");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.users (id, email, password, first_name, last_name, is_active, is_superuser) VALUES 
            (1, 'testuser@example.com', 'hashed_password', 'John', 'Doe', 1, 0)");
        $this->pdo->exec("INSERT INTO airlinemanagementtest.verification (Verification_id, User_id, Verification_code, used, Create_date) VALUES 
            (1, 1, '123456', 0, '2023-12-31')");
    }

    public function testSendResetLink() {
        $_POST = json_encode([
            'email' => 'testuser@example.com',
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['password-recovery', 'send-reset-link']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);
    }

    public function testValidateCode() {
        $_POST = json_encode([
            'code' => '123456',
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['password-recovery', 'validate-code']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);
    }

    public function testUpdatePassword() {
        $_POST = json_encode([
            'userId' => 1,
            'code' => '123456',
            'newPassword' => 'new_secure_password',
        ]);

        ob_start();
        $this->controller->handleRequest('POST', ['password-recovery', 'update-password']);
        $output = ob_get_clean();

        $this->assertStringContainsString('"success":true', $output);

        $result = $this->pdo->query("SELECT * FROM airlinemanagementtest.users WHERE id = 1")->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals('new_secure_password', $result['password']);
    }

    protected function tearDown(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("TRUNCATE TABLE users");
        $this->pdo->exec("TRUNCATE TABLE verification");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}

