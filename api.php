<?php


require_once __DIR__ . '/config/Database.php';

// Підключаємо всі контролери
require_once __DIR__ . '/controllers/AirplaneController.php';
require_once __DIR__ . '/controllers/AirportController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/FlightController.php';
require_once __DIR__ . '/controllers/PasswordRecoveryController.php';
require_once __DIR__ . '/controllers/PaymentController.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/controllers/UserController.php';

use config\Database;
use controllers\AirplaneController;
use controllers\AirportController;
use controllers\AuthController;
use controllers\FlightController;
use controllers\PasswordRecoveryController;
use controllers\PaymentController;
use controllers\TicketController;
use controllers\UserController;

// Підключення до бази даних
$pdo = Database::connect();
$config = include './config/config.php';
$jwtSecret = $config['jwtSecret'];

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));

    // Перевірка наявності маршруту
    if (empty($path[0])) {
        http_response_code(404);
        echo json_encode(["error" => "No endpoint specified"]);
        exit;
    }

    // Головний маршрутизатор
    switch ($path[0]) {
        case 'airplanes':
            $controller = new AirplaneController($pdo);
            $controller->handleRequest($method, $path);
            break;

        case 'airports':
            $controller = new AirportController($pdo);
            $controller->handleRequest($method, $path);
            break;

        case 'auth':
            $controller = new AuthController($pdo, $jwtSecret);
            $controller->handleRequest($method, $path);
            break;

        case 'flights':
            $controller = new FlightController($pdo, $jwtSecret);
            $controller->handleRequest($method, $path);
            break;

        case 'password':
            $controller = new PasswordRecoveryController($pdo);
            $controller->handleRequest($method, $path);
            break;

        case 'payments':
            $controller = new PaymentController($pdo, $jwtSecret);
            $controller->handleRequest($method, $path);
            break;

        case 'tickets':
            $controller = new TicketController($pdo, $jwtSecret);
            $controller->handleRequest($method, $path);
            break;

        case 'users':
            $controller = new UserController($pdo, $jwtSecret);
            $controller->handleRequest($method, $path);
            break;

        default:
            http_response_code(404);
            echo json_encode(["error" => "Endpoint not found"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
