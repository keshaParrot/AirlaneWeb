<?php

namespace controllers;

require_once __DIR__ . '/../services/AuthService.php';

use Exception;
use repositories\UserRepository;
use repositories\VerificationCodeRepository;
use services\AuthService;

class AuthController {
    private AuthService $service;

    public function __construct($pdo, $jwtSecret, string $dbName = 'airlinemanagement') {
        $userRepository = new UserRepository($pdo, $dbName);
        $validationCodeRepository = new VerificationCodeRepository($pdo, $dbName);
        $this->service = new AuthService($userRepository, $validationCodeRepository, $jwtSecret);
    }

    public function handleRequest($method, $path) {
        try {
            // POST /auth/login
            if ($method === 'POST' && count($path) === 2 && $path[1] === 'login') {
                $this->login();
                return;
            }

            // POST /auth/register
            if ($method === 'POST' && count($path) === 2 && $path[1] === 'register') {
                $this->register();
                return;
            }

            // POST /auth/refresh
            if ($method === 'POST' && count($path) === 2 && $path[1] === 'refresh') {
                $this->refreshToken();
                return;
            }

            if ($method === 'POST' && count($path) === 2 && $path[1] === 'validate-mail') {
                $this->checkValidationCode();
                return;
            }

            http_response_code(404);
            echo json_encode(["error" => "Endpoint not found"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input"]);
            return;
        }

        $response = $this->service->login($data['email'], $data['password']);
        if ($response) {
            echo json_encode(["token" => $response]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid credentials"]);
        }
    }

    private function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['email']) || !isset($data['lastname']) || !isset($data['firstname']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input"]);
            return;
        }

        $response = $this->service->register($data['email'], $data['password'], $data['firstname'], $data['lastname']);
        if ($response) {
            http_response_code(201);
            echo json_encode($response);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Registration failed"]);
        }
    }
    private function checkValidationCode() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input"]);
            return;
        }

        $response = $this->service->validateMail($data['email'], $data['code']);
        if ($response) {
            http_response_code(201);
            echo json_encode($response);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Registration failed"]);
        }
    }

    private function refreshToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(400);
            echo json_encode(["error" => "Authorization header with Bearer token is required."]);
            return;
        }

        $token = substr($authHeader, 7);

        try {
            $user = $this->service->authenticate($token);

            $newToken = $this->service->refresh($token);

            echo json_encode([
                "success" => true,
                "token" => $newToken,
                "user_id" => $user->getId()
            ]);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => "Invalid or expired token."]);
        }
    }
}
