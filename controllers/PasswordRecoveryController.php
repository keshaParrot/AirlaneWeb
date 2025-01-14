<?php

namespace controllers;

require_once __DIR__ . '/../services/PasswordRecoveryService.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/VerificationCodeRepository.php';
require_once __DIR__ . '/../config/Database.php';

use repositories\UserRepository;
use repositories\VerificationCodeRepository;
use services\PasswordRecoveryService;

class PasswordRecoveryController {
    private PasswordRecoveryService $passwordRecoveryService;

    public function __construct($pdo) {
        $userRepository = new UserRepository($pdo);
        $verificationCodeRepository = new VerificationCodeRepository($pdo);
        $this->passwordRecoveryService = new PasswordRecoveryService($verificationCodeRepository, $userRepository);
    }

    public function handleRequest($method, $path) {
        try {
            if ($method === 'POST' && count($path) === 2 && $path[0] === 'password-recovery' && $path[1] === 'send-reset-link') {
                $this->sendResetLink();
            } elseif ($method === 'POST' && count($path) === 2 && $path[0] === 'password-recovery' && $path[1] === 'validate-code') {
                $this->validateCode();
            } elseif ($method === 'POST' && count($path) === 2 && $path[0] === 'password-recovery' && $path[1] === 'update-password') {
                $this->updatePassword();
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint not found"]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function sendResetLink() {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            throw new RuntimeException("Email is required.");
        }

        $this->passwordRecoveryService->sendResetLink($email);
        echo json_encode(["success" => true, "message" => "Reset link sent successfully."]);
    }

    private function validateCode() {
        $data = json_decode(file_get_contents('php://input'), true);
        $code = $data['code'] ?? null;

        if (!$code) {
            throw new RuntimeException("Code is required.");
        }

        $isValid = $this->passwordRecoveryService->validateCode($code);
        echo json_encode(["success" => $isValid]);
    }

    private function updatePassword() {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'] ?? null;
        $code = $data['code'] ?? null;
        $newPassword = $data['newPassword'] ?? null;

        if (!$userId || !$code || !$newPassword) {
            throw new RuntimeException("User ID, code, and new password are required.");
        }

        $this->passwordRecoveryService->updatePassword((int)$userId, $code, $newPassword);
        echo json_encode(["success" => true, "message" => "Password updated successfully."]);
    }
}
