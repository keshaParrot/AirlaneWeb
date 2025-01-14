<?php

require_once __DIR__ . '/../services/FlightService.php';
require_once __DIR__ . '/../repositories/FlightRepository.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../Middleware.php';

use repositories\FlightRepository;
use services\AuthService;
use services\FlightService;

class FlightController {
    private FlightService $service;
    private AuthService $authService;
    private $jwtSecret;

    public function __construct($pdo, $jwtSecret) {
        $repository = new FlightRepository($pdo);
        $this->service = new FlightService($repository);
        $this->authService = new AuthService(new repositories\UserRepository($pdo), $jwtSecret);
        $this->jwtSecret = $jwtSecret;
    }

    public function handleRequest($method, $path) {
        try {
            // GET /flights
            if ($method === 'GET' && count($path) === 1) {
                $this->getFlights();
                return;
            }

            // POST /flights
            if ($method === 'POST' && count($path) === 1) {
                $this->addFlight();
                return;
            }

            http_response_code(404);
            echo json_encode(["error" => "Endpoint not found"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function getFlights() {
        $departure = $_GET['departure'] ?? null;
        $destination = $_GET['destination'] ?? null;
        $minPrice = isset($_GET['minPrice']) ? (float)$_GET['minPrice'] : null;
        $maxPrice = isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : null;
        $departureDate = $_GET['departureDate'] ?? null;
        $timeFilter = $_GET['timeFilter'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $flights = $this->service->getFilteredFlights(
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
    }

    private function addFlight() {
        $user = authMiddleware($this->jwtSecret);
        authorizeSuperuser($user);

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

        $success = $this->service->addFlight(
            (float)$price,
            $departureDateTime,
            $arrivalDateTime,
            (int)$departureAirportId,
            (int)$destinationAirportId,
            (int)$airplaneId,
            (int)$createdBy
        );

        echo json_encode(["success" => $success]);
    }
}
