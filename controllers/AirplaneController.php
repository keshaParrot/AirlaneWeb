<?php

require_once __DIR__ . '/../services/AirplaneService.php';
require_once __DIR__ . '/../repositories/AirplaneRepository.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../domain/Airplane.php';

use repositories\AirplaneRepository;
use services\AirplaneService;
use config\Database;

$pdo = Database::connect();

$airplaneRepository = new AirplaneRepository($pdo);
$airplaneService = new AirplaneService($airplaneRepository);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    if ($method === 'GET' && $path[0] === 'airplanes') {
        // GET /airplanes
        $airplanes = $airplaneService->getAllAirplanes();

        echo json_encode($airplanes);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
