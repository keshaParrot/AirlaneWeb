<?php

require_once 'services/UserService.php';
require_once 'repositories/UserRepository.php';
require_once 'config/Database.php';
require_once 'domain/User.php';

use services\UserService;
use repositories\UserRepository;
use config\Database;

// Database connection
$pdo = Database::connect();

$userRepository = new UserRepository($pdo);
$userService = new UserService($userRepository);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    if ($method === 'PUT' && $path[0] === 'users' && $path[1] === 'update') {
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
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
