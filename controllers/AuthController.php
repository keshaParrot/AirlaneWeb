<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../domain/User.php';
require_once __DIR__ . '/../Middleware.php';

use services\AuthService;
use repositories\UserRepository;
use config\Database;

// Database connection
$pdo = Database::connect();
$config = include './config/config.php';
$jwtSecret = $config['jwtSecret'];

$userRepository = new UserRepository($pdo);
$authService = new AuthService($userRepository, $jwtSecret);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    if ($method === 'POST' && $path[0] === 'auth' && $path[1] === 'register') {
        // POST /auth/register
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;

        if (!$email || !$password || !$firstName || !$lastName) {
            throw new RuntimeException("Missing required fields.");
        }

        $authService->register($email, $password, $firstName, $lastName);
        echo json_encode(["success" => true, "message" => "User registered successfully."]);
    } elseif ($method === 'POST' && $path[0] === 'auth' && $path[1] === 'login') {
        // POST /auth/login
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            throw new RuntimeException("Email and password are required.");
        }

        $token = $authService->login($email, $password);
        echo json_encode(["success" => true, "token" => $token]);
    } elseif ($method === 'POST' && $path[0] === 'auth' && $path[1] === 'refresh') {
        // POST /auth/refresh
        $data = json_decode(file_get_contents('php://input'), true);

        $token = $data['token'] ?? null;

        if (!$token) {
            throw new RuntimeException("Token is required.");
        }

        $newToken = $authService->refresh($token);
        echo json_encode(["success" => true, "token" => $newToken]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}