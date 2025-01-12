<?php

require_once 'services/UserService.php';
require_once 'repositories/UserRepository.php';
require_once 'repositories/VerificationCodeRepository.php';
require_once 'config/Database.php';
require_once 'domain/User.php';

use repositories\UserRepository;
use repositories\VerificationCodeRepository;
use services\UserService;
use config\Database;

// Database connection
$pdo = Database::connect();

$userRepository = new UserRepository($pdo);
$verificationCodeRepository = new VerificationCodeRepository($pdo);
$userService = new UserService($userRepository, $verificationCodeRepository);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    if ($method === 'POST' && $path[0] === 'users' && $path[1] === 'register') {
        // POST /users/register
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;

        if (!$email || !$password || !$firstName || !$lastName) {
            throw new RuntimeException("Missing required fields.");
        }

        $success = $userService->save($email, $password, $firstName, $lastName);
        echo json_encode(["success" => $success]);
    } elseif ($method === 'PUT' && $path[0] === 'users' && $path[1] === 'update') {
        // PUT /users/update
        $data = json_decode(file_get_contents('php://input'), true);

        $id = $data['id'] ?? null;
        $email = $data['email'] ?? null;
        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;
        $walletBalance = isset($data['walletBalance']) ? (float)$data['walletBalance'] : null;
        $cardId = isset($data['cardId']) ? (int)$data['cardId'] : null;

        if (!$id) {
            throw new RuntimeException("User ID is required.");
        }

        $success = $userService->updateUser((int)$id, $email, $firstName, $lastName, $walletBalance, $cardId);
        echo json_encode(["success" => $success]);
    } elseif ($method === 'GET' && $path[0] === 'users' && $path[1] === 'get') {
        // GET /users/get?id=123
        $id = $_GET['id'] ?? null;

        if (!$id) {
            throw new RuntimeException("User ID is required.");
        }

        $user = $userService->getById((int)$id);
        if (!$user) {
            throw new RuntimeException("User not found.");
        }

        echo json_encode($user);
    } elseif ($method === 'POST' && $path[0] === 'users' && $path[1] === 'login') {
        // POST /users/login
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            throw new RuntimeException("Email and password are required.");
        }

        $user = $userService->login($email, $password);
        if (!$user) {
            throw new RuntimeException("Invalid email or password.");
        }

        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
