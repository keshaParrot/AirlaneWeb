<?php

namespace controllers;

require_once __DIR__ . '/../services/AuthService.php';

use Exception;
use repositories\UserRepository;
use repositories\VerificationCodeRepository;
use services\AuthService;

class AuthController {
    private AuthService $service;

    public function __construct($pdo, $jwtSecret) {
        $userRepository = new UserRepository($pdo);
        $validationCodeRepository = new VerificationCodeRepository($pdo);
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
        if (!$data || !isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input"]);
            return;
        }

        $response = $this->service->login($data['username'], $data['password']);
        if ($response) {
            echo json_encode($response);
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
        $email = $_GET['email'] ?? null;
        $code = $_GET['code'] ?? null;

        $response = $this->service->validateMail($email, $code);
        if ($response) {
            http_response_code(201);
            echo json_encode($response);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Registration failed"]);
        }
    }

    private function refreshToken() {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? null;

        if (!$token) {
            http_response_code(400);
            echo json_encode(["error" => "Token is required."]);
            return;
        }

        try {
            $newToken = $this->service->refresh($token);
            echo json_encode(["success" => true, "token" => $newToken]);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => "Invalid or expired token."]);
        }
    }

}
