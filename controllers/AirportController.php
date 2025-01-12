<?php
require_once 'services/AirportService.php';
require_once 'repositories/AirportRepository.php';
require_once 'config/Database.php';
require_once 'domain/Airport.php';

use repositories\AirportRepository;
use services\AirportService;
use config\Database;

$pdo = Database::connect();

$airportRepository = new AirportRepository($pdo);
$airportService = new AirportService($airportRepository);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    if ($method === 'GET' && $path[0] === 'airports') {
        // GET /airports
        $airports = $airportService->getAllAirports();

        echo json_encode($airports);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}