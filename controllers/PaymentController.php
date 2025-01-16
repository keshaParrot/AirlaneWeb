<?php

namespace controllers;

require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';
require_once __DIR__ . '/../repositories/CardRepository.php';
require_once __DIR__ . '/../Middleware.php';
require_once __DIR__ . '/../services/AuthService.php';

use Exception;
use repositories\UserRepository;
use repositories\TransactionRepository;
use repositories\CardRepository;
use RuntimeException;
use services\AuthService;
use services\PaymentService;

class PaymentController {
    private PaymentService $paymentService;
    private $jwtSecret;

    public function __construct($pdo, $jwtSecret) {
        $userRepository = new UserRepository($pdo);
        $transactionRepository = new TransactionRepository($pdo);
        $cardRepository = new CardRepository($pdo);
        $this->paymentService = new PaymentService($userRepository, $transactionRepository, $cardRepository);
        $this->jwtSecret = $jwtSecret;
    }

    public function handleRequest($method, $path) {
        try {
            $user = null;
            if (!in_array($path[1], ['login', 'register'], true)) {
                $user = authMiddleware($this->jwtSecret);
            }

            if ($method === 'GET' && count($path) === 2 && $path[0] === 'user' && $path[1] === 'cards') {
                $this->getUserCards($user);
            } elseif ($method === 'POST' && count($path) === 2 && $path[0] === 'user' && $path[1] === 'deposit') {
                $this->deposit($user);
            } elseif ($method === 'POST' && count($path) === 2 && $path[0] === 'user' && $path[1] === 'assign-card') {
                $this->assignCard($user);
            } elseif ($method === 'DELETE' && count($path) === 2 && $path[0] === 'user' && $path[1] === 'remove-card') {
                $this->removeCard($user);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint not found"]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function getUserCards($user) {
        $userId = $_GET['userId'] ?? null;
        if (!$userId) {
            throw new RuntimeException("User ID is required.");
        }

        authorizeOwner($user['id'], (int)$userId);

        $cards = $this->paymentService->GetAllUserCard((int)$userId);
        echo json_encode($cards);
    }

    private function deposit($user) {
        $data = json_decode(file_get_contents('php://input'), true);
        $amount = $data['amount'] ?? null;
        $userId = $data['userId'] ?? null;
        $cardId = $data['cardId'] ?? null;

        if (!$amount || !$userId || !$cardId) {
            throw new RuntimeException("Missing required fields.");
        }

        authorizeOwner($user['id'], (int)$userId);

        $success = $this->paymentService->depositMoneyFromDebit((float)$amount, (int)$userId, $cardId);
        echo json_encode(["success" => $success]);
    }

    private function assignCard($user) {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'] ?? null;
        $cardNumber = $data['cardNumber'] ?? null;
        $expiryDate = $data['expiryDate'] ?? null;

        if (!$userId || !$cardNumber || !$expiryDate) {
            throw new RuntimeException("Missing required fields.");
        }

        authorizeOwner($user['id'], (int)$userId);

        $this->paymentService->assignCardToUser((int)$userId, $cardNumber, $expiryDate);
        echo json_encode(["success" => true]);
    }

    private function removeCard($user) {
        $userId = $_GET['userId'] ?? null;
        $cardId = $_GET['cardId'] ?? null;

        if (!$userId || !$cardId) {
            throw new RuntimeException("User ID and Card ID are required.");
        }

        authorizeOwner($user['id'], (int)$userId);

        $success = $this->paymentService->removeCardFromUser((int)$userId, (int)$cardId);
        echo json_encode(["success" => $success]);
    }
}
