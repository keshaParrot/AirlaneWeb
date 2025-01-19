<?php

namespace controllers;

require_once __DIR__ . '/../services/TicketService.php';
require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../repositories/TicketRepository.php';
require_once __DIR__ . '/../repositories/FlightRepository.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/TransactionRepository.php';
require_once __DIR__ . '/../repositories/CardRepository.php';
require_once __DIR__ . '/../Middleware.php';
require_once __DIR__ . '/../services/AuthService.php';

use Exception;
use repositories\TicketRepository;
use repositories\FlightRepository;
use repositories\UserRepository;
use repositories\TransactionRepository;
use repositories\CardRepository;
use RuntimeException;
use services\TicketService;
use services\PaymentService;
use services\AuthService;

class TicketController {
    private TicketService $ticketService;
    private $jwtSecret;

    public function __construct($pdo, $jwtSecret, string $dbName = 'airlinemanagement') {
        $ticketRepository = new TicketRepository($pdo, $dbName);
        $flightRepository = new FlightRepository($pdo, $dbName);
        $userRepository = new UserRepository($pdo, $dbName);
        $transactionRepository = new TransactionRepository($pdo, $dbName);
        $cardRepository = new CardRepository($pdo, $dbName);

        $paymentService = new PaymentService($userRepository, $transactionRepository, $cardRepository);
        $this->ticketService = new TicketService($paymentService, $ticketRepository, $flightRepository, $userRepository);
        $this->jwtSecret = $jwtSecret;
    }

    public function handleRequest($method, $path) {
        try {
            $user = null;
            if (!in_array($path[1], ['login', 'register'], true)) {
                $user = authMiddleware($this->jwtSecret);
            }

            //ту логічна помилка з count($path) === 3
            if ($method === 'POST' && count($path) === 2 && $path[1] === 'sell') {
                $this->sellTicket($user);
            } elseif ($method === 'POST' && count($path) === 2 && $path[1] === 'refund') {
                $this->refundTicket($user);
            } elseif ($method === 'GET' && count($path) === 2 && $path[1] === 'user') {
                $this->getTicketsByUser($user);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint not found"]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function sellTicket($user) {
        $data = json_decode(file_get_contents('php://input'), true);

        $flightId = $data['flightId'] ?? null;
        $userId = $data['userId'] ?? null;
        $cardId = $data['cardId'] ?? null;
        $ticketOwnerFullName = $data['ticketOwnerFullName'] ?? null;
        $paymentMethod = $data['paymentMethod'] ?? null;

        if (!$flightId || !$userId || !$cardId || !$ticketOwnerFullName || !$paymentMethod) {
            throw new RuntimeException("Missing required fields.");
        }

        authorizeOwner($user['id'], (int)$userId);

        $this->ticketService->sellTicket($flightId, $userId, $cardId, $ticketOwnerFullName, $paymentMethod);
        echo json_encode(["success" => true, "message" => "Ticket sold successfully."]);
    }

    private function refundTicket($user) {
        $data = json_decode(file_get_contents('php://input'), true);

        $userId = $data['userId'] ?? null;
        $ticketId = $data['ticketId'] ?? null;
        $cardId = $data['cardId'] ?? null;

        if (!$userId || !$ticketId || !$cardId) {
            throw new RuntimeException("Missing required fields.");
        }

        authorizeOwner($user['id'], (int)$userId);

        $this->ticketService->refundTicket($userId, $ticketId);
        echo json_encode(["success" => true, "message" => "Ticket refunded successfully."]);
    }

    private function getTicketsByUser($user) {
        $userId = $_GET['id'] ?? null;

        if (!$userId) {
            throw new RuntimeException("User ID is required.");
        }

        authorizeOwner($user['id'], (int)$userId);

        $tickets = $this->ticketService->getAllTicketsByUserId($userId);
        echo json_encode($tickets);
    }
}
