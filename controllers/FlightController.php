<?php

require_once 'services/FlightService.php';
require_once 'repositories/FlightRepository.php';
require_once 'config/Database.php';

use repositories\FlightRepository;
use services\FlightService;
use config\Database;

// Database connection
$pdo = Database::connect();

$flightRepository = new FlightRepository($pdo);
$flightService = new FlightService($flightRepository);

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    if ($method === 'GET' && $path[0] === 'flights') {
        // GET /flights with optional query parameters
        $departure = $_GET['departure'] ?? null;
        $destination = $_GET['destination'] ?? null;
        $minPrice = isset($_GET['minPrice']) ? (float)$_GET['minPrice'] : null;
        $maxPrice = isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : null;
        $departureDate = $_GET['departureDate'] ?? null;
        $timeFilter = $_GET['timeFilter'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $flights = $flightService->getFilteredFlights(
            $departure,
            $destination,
            $minPrice,
            $maxPrice,
            $departureDate,
            $timeFilter,
            $limit,
            $offset
        );

        echo json_encode($flights);
    } elseif ($method === 'POST' && $path[0] === 'flights') {
        // POST /flights to add a new flight
        $data = json_decode(file_get_contents('php://input'), true);

        $price = $data['price'] ?? null;
        $departureDateTime = $data['departureDateTime'] ?? null;
        $arrivalDateTime = $data['arrivalDateTime'] ?? null;
        $departureAirportId = $data['departureAirportId'] ?? null;
        $destinationAirportId = $data['destinationAirportId'] ?? null;
        $airplaneId = $data['airplaneId'] ?? null;
        $createdBy = $data['createdBy'] ?? null;

        if (!$price || !$departureDateTime || !$arrivalDateTime || !$departureAirportId || !$destinationAirportId || !$airplaneId || !$createdBy) {
            throw new RuntimeException("Missing required fields.");
        }

        $success = $flightService->addFlight(
            (float)$price,
            $departureDateTime,
            $arrivalDateTime,
            (int)$departureAirportId,
            (int)$destinationAirportId,
            (int)$airplaneId,
            (int)$createdBy
        );

        echo json_encode(["success" => $success]);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
