<?php

require_once 'services/PaymentService.php';
require_once 'repositories/UserRepository.php';
require_once 'repositories/TransactionRepository.php';
require_once 'repositories/CardRepository.php';
require_once 'config/Database.php';
require_once 'Middleware.php';
require_once 'services/AuthService.php';

use repositories\UserRepository;
use repositories\TransactionRepository;
use repositories\CardRepository;
use services\AuthService;
use services\PaymentService;
use config\Database;

$pdo = Database::connect();
$config = include './config/config.php';
$jwtSecret = $config['jwtSecret'];

$userRepository = new UserRepository($pdo);
$transactionRepository = new TransactionRepository($pdo);
$cardRepository = new CardRepository($pdo);
$paymentService = new PaymentService($userRepository, $transactionRepository, $cardRepository);
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

    if ($method === 'GET' && $path[0] === 'user' && $path[1] === 'cards') {
        // GET /user/cards?userId=123
        $userId = $_GET['userId'] ?? null;
        if (!$userId) {
            throw new RuntimeException("User ID is required.");
        }

        // Ensure the user is the owner or superuser
        authorizeOwner($user['id'], (int)$userId);

        $cards = $paymentService->GetAllUserCard((int)$userId);
        echo json_encode($cards);
    } elseif ($method === 'POST' && $path[0] === 'user' && $path[1] === 'deposit') {
        // POST /user/deposit
        $data = json_decode(file_get_contents('php://input'), true);
        $amount = $data['amount'] ?? null;
        $userId = $data['userId'] ?? null;
        $cardId = $data['cardId'] ?? null;

        if (!$amount || !$userId || !$cardId) {
            throw new RuntimeException("Missing required fields.");
        }

        // Ensure the user is the owner or superuser
        authorizeOwner($user['id'], (int)$userId);

        $success = $paymentService->depositMoneyFromDebit((float)$amount, (int)$userId, $cardId);
        echo json_encode(["success" => $success]);
    } elseif ($method === 'POST' && $path[0] === 'user' && $path[1] === 'assign-card') {
        // POST /user/assign-card
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'] ?? null;
        $cardNumber = $data['cardNumber'] ?? null;
        $expiryDate = $data['expiryDate'] ?? null;

        if (!$userId || !$cardNumber || !$expiryDate) {
            throw new RuntimeException("Missing required fields.");
        }

        // Ensure the user is the owner or superuser
        authorizeOwner($user['id'], (int)$userId);

        $paymentService->assignCardToUser((int)$userId, $cardNumber, $expiryDate);
        echo json_encode(["success" => true]);
    } elseif ($method === 'DELETE' && $path[0] === 'user' && $path[1] === 'remove-card') {
        // DELETE /user/remove-card?userId=123&cardId=456
        $userId = $_GET['userId'] ?? null;
        $cardId = $_GET['cardId'] ?? null;

        if (!$userId || !$cardId) {
            throw new RuntimeException("User ID and Card ID are required.");
        }

        // Ensure the user is the owner or superuser
        authorizeOwner($user['id'], (int)$userId);

        $success = $paymentService->removeCardFromUser((int)$userId, (int)$cardId);
        echo json_encode(["success" => $success]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
