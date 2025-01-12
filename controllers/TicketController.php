<?php

require_once 'services/TicketService.php';
require_once 'services/PaymentService.php';
require_once 'repositories/TicketRepository.php';
require_once 'repositories/FlightRepository.php';
require_once 'repositories/UserRepository.php';
require_once 'config/Database.php';
require_once 'domain/Ticket.php';
require_once 'Middleware.php';
require_once 'services/AuthService.php';

use repositories\CardRepository;
use repositories\TransactionRepository;
use services\AuthService;
use services\TicketService;
use services\PaymentService;
use repositories\TicketRepository;
use repositories\FlightRepository;
use repositories\UserRepository;
use config\Database;

$pdo = Database::connect();
$config = include './config/config.php';
$jwtSecret = $config['jwtSecret'];

$ticketRepository = new TicketRepository($pdo);
$flightRepository = new FlightRepository($pdo);
$userRepository = new UserRepository($pdo);
$transactionRepository = new TransactionRepository($pdo);
$cardRepository = new CardRepository($pdo);
$paymentService = new PaymentService($userRepository, $transactionRepository, $cardRepository);
$ticketService = new TicketService($paymentService, $ticketRepository, $flightRepository, $userRepository);
$authService = new AuthService($userRepository, $jwtSecret);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    // Middleware integration: Authenticate user unless endpoint is public
    $user = null;
    if (!in_array($path[1], ['login', 'register'], true)) {
        $user = authMiddleware($jwtSecret);
    }

    if ($method === 'POST' && $path[0] === 'tickets' && $path[1] === 'sell') {
        // POST /tickets/sell
        $data = json_decode(file_get_contents('php://input'), true);

        $flightId = $data['flightId'] ?? null;
        $userId = $data['userId'] ?? null;
        $cardId = $data['cardId'] ?? null;
        $ticketOwnerFullName = $data['ticketOwnerFullName'] ?? null;
        $paymentMethod = $data['paymentMethod'] ?? null;

        if (!$flightId || !$userId || !$cardId || !$ticketOwnerFullName || !$paymentMethod) {
            throw new RuntimeException("Missing required fields.");
        }

        // Ensure the user is the owner or superuser
        authorizeOwner($user['id'], (int)$userId);

        $ticketService->sellTicket($flightId, $userId, $cardId, $ticketOwnerFullName, $paymentMethod);
        echo json_encode(["success" => true, "message" => "Ticket sold successfully."]);
    } elseif ($method === 'POST' && $path[0] === 'tickets' && $path[1] === 'refund') {
        // POST /tickets/refund
        $data = json_decode(file_get_contents('php://input'), true);

        $userId = $data['userId'] ?? null;
        $ticketId = $data['ticketId'] ?? null;
        $cardId = $data['cardId'] ?? null;

        if (!$userId || !$ticketId || !$cardId) {
            throw new RuntimeException("Missing required fields.");
        }

        // Ensure the user is the owner or superuser
        authorizeOwner($user['id'], (int)$userId);

        $ticketService->refundTicket($userId, $ticketId);
        echo json_encode(["success" => true, "message" => "Ticket refunded successfully."]);
    } elseif ($method === 'GET' && $path[0] === 'tickets' && $path[1] === 'user') {
        // GET /tickets/user?id=123
        $userId = $_GET['id'] ?? null;

        if (!$userId) {
            throw new RuntimeException("User ID is required.");
        }

        // Ensure the user is the owner or superuser
        authorizeOwner($user['id'], (int)$userId);

        $tickets = $ticketService->getAllTicketsByUserId($userId);
        echo json_encode($tickets);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
