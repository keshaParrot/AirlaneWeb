<?php

namespace controllers;

require_once __DIR__ . '/../services/AirportService.php';
require_once __DIR__ . '/../repositories/AirportRepository.php';
require_once __DIR__ . '/../config/Database.php';

use Exception;
use repositories\AirportRepository;
use services\AirportService;

class AirportController {
    private $airportService;

    public function __construct($pdo) {
        $airportRepository = new AirportRepository($pdo);
        $this->airportService = new AirportService($airportRepository);
    }

    public function handleRequest($method, $path) {
        try {
            if ($method === 'GET' && count($path) === 1 && $path[0] === 'airports') {
                $this->getAllAirports();
            }else if ($method === 'GET' && count($path) === 2 && $path[0] === 'airports' && $path[1] === 'get') {
                $this->getAirportById();
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint not found"]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function getAllAirports() {
        $airports = $this->airportService->getAllAirports();
        echo json_encode($airports);
    }
    private function getAirportById() {
        $id = $_GET['id'] ?? null;
        $airports = $this->airportService->getAirportsById($id);
        echo json_encode($airports);
    }
}
