<?php
//чернетка йопта

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once '../controllers/PasswordController.php';
require_once '../controllers/UserController.php';
require_once '../controllers/FlightController.php';
require_once '../controllers/TicketController.php';

// Простий маршрутизатор
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

$response = [];

try {
    if ($requestUri[0] === 'api') {
        switch ($requestUri[1]) {
            case 'password':
                $controller = new PasswordController();
                handlePasswordApi($controller, $requestMethod, $requestUri);
                break;

            case 'user':
                $controller = new UserController();
                handleUserApi($controller, $requestMethod, $requestUri);
                break;

            case 'flight':
                $controller = new FlightController();
                handleFlightApi($controller, $requestMethod, $requestUri);
                break;

            case 'ticket':
                $controller = new TicketController();
                handleTicketApi($controller, $requestMethod, $requestUri);
                break;

            default:
                throw new Exception("Маршрут не знайдено.");
        }
    } else {
        throw new Exception("Невірний маршрут.");
    }
} catch (Exception $e) {
    http_response_code(400);
    $response = ["error" => $e->getMessage()];
}

echo json_encode($response);

// Маршрути для Password API
function handlePasswordApi($controller, $method, $uri) {
    switch ($uri[2]) {
        case 'reset-link':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $controller->sendResetLink($data['email']);
                echo json_encode(["message" => "Посилання для скидання пароля відправлено."]);
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        case 'validate-code':
            if ($method === 'GET') {
                $controller->validateCode($_GET['code']);
                echo json_encode(["message" => "Код валідний."]);
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        case 'update':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $controller->updatePassword($data['userId'], $data['code'], $data['newPassword']);
                echo json_encode(["message" => "Пароль успішно оновлено."]);
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        default:
            throw new Exception("Запит не знайдено.");
    }
}

// Маршрути для інших API (аналогічно)
function handleUserApi($controller, $method, $uri) {
    switch ($uri[2]) {
        case 'register':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $controller->register($data);
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        case 'get-profile':
            if ($method === 'GET') {
                $controller->getProfile($_GET['userId']);
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        default:
            throw new Exception("Запит не знайдено.");
    }
}

function handleFlightApi($controller, $method, $uri) {
    switch ($uri[2]) {
        case 'list':
            if ($method === 'GET') {
                $controller->getAllFlights();
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        case 'details':
            if ($method === 'GET') {
                $controller->getFlightDetails($_GET['flightId']);
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        default:
            throw new Exception("Запит не знайдено.");
    }
}

function handleTicketApi($controller, $method, $uri) {
    switch ($uri[2]) {
        case 'purchase':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $controller->purchaseTicket($data);
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        case 'refund':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $controller->requestRefund($data);
            } else {
                throw new Exception("Метод не підтримується.");
            }
            break;

        default:
            throw new Exception("Запит не знайдено.");
    }
}
