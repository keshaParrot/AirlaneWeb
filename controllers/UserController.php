<?php

namespace controllers;

require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../config/Database.php';

use Exception;
use RuntimeException;
use services\UserService;
use repositories\UserRepository;

class UserController {
    private UserService $userService;
    private $jwtSecret;

    public function __construct($pdo, $jwtSecret) {
        $userRepository = new UserRepository($pdo);
        $this->userService = new UserService($userRepository);
        $this->jwtSecret = $jwtSecret;
    }

    public function handleRequest($method, $path) {
        try {
            if ($method === 'PUT' && count($path) === 2 && $path[0] === 'users' && $path[1] === 'update') {
                $this->updateUser();
            } elseif ($method === 'GET' && count($path) === 2 && $path[0] === 'users' && $path[1] === 'get') {
                $this->getUserById();
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint not found"]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function updateUser() {
        $user = authMiddleware($this->jwtSecret);

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

        authorizeOwner($user['id'], (int)$id);

        $success = $this->userService->updateUser((int)$id, $email, $firstName, $lastName, $walletBalance, $cardId);
        echo json_encode(["success" => $success]);
    }


    private function getUserById() {
        $user = authMiddleware($this->jwtSecret);

        $id = $_GET['id'] ?? null;

        if (!$id) {
            throw new RuntimeException("User ID is required.");
        }

        authorizeOwner($user['id'], (int)$id);

        $userData = $this->userService->getById((int)$id);
        if (!$userData) {
            throw new RuntimeException("User not found.");
        }

        echo json_encode($userData);
    }

}
