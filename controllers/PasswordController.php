<?php

require_once 'services/PasswordRecoveryService.php';
require_once 'repositories/UserRepository.php';
require_once 'repositories/VerificationCodeRepository.php';
require_once 'config/Database.php';

use repositories\UserRepository;
use repositories\VerificationCodeRepository;
use services\PasswordRecoveryService;
use config\Database;

// Database connection
$pdo = Database::connect();

$userRepository = new UserRepository($pdo);
$verificationCodeRepository = new VerificationCodeRepository($pdo);
$passwordRecoveryService = new PasswordRecoveryService($verificationCodeRepository, $userRepository);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    if ($method === 'POST' && $path[0] === 'password-recovery' && $path[1] === 'send-reset-link') {
        // POST /password-recovery/send-reset-link
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            throw new RuntimeException("Email is required.");
        }

        $passwordRecoveryService->sendResetLink($email);
        echo json_encode(["success" => true, "message" => "Reset link sent successfully."]);
    } elseif ($method === 'POST' && $path[0] === 'password-recovery' && $path[1] === 'validate-code') {
        // POST /password-recovery/validate-code
        $data = json_decode(file_get_contents('php://input'), true);
        $code = $data['code'] ?? null;

        if (!$code) {
            throw new RuntimeException("Code is required.");
        }

        $isValid = $passwordRecoveryService->validateCode($code);
        echo json_encode(["success" => $isValid]);
    } elseif ($method === 'POST' && $path[0] === 'password-recovery' && $path[1] === 'update-password') {
        // POST /password-recovery/update-password
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'] ?? null;
        $code = $data['code'] ?? null;
        $newPassword = $data['newPassword'] ?? null;

        if (!$userId || !$code || !$newPassword) {
            throw new RuntimeException("User ID, code, and new password are required.");
        }

        $passwordRecoveryService->updatePassword((int)$userId, $code, $newPassword);
        echo json_encode(["success" => true, "message" => "Password updated successfully."]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
