<?php

namespace controllers;

require_once __DIR__ . '/../services/AirplaneService.php';
require_once __DIR__ . '/../repositories/AirplaneRepository.php';
require_once __DIR__ . '/../config/Database.php';

use repositories\AirplaneRepository;
use services\AirplaneService;

class AirplaneController {
    private $airplaneService;

    public function __construct($pdo) {
        $airplaneRepository = new AirplaneRepository($pdo);
        $this->airplaneService = new AirplaneService($airplaneRepository);
    }

    public function handleRequest($method, $path) {
        try {
            if ($method === 'GET' && count($path) === 1 && $path[0] === 'airplanes') {
                $this->getAllAirplanes();
            }else if ($method === 'GET' && count($path) === 2 && $path[0] === 'airplanes' && $path[1] === 'get') {
                $this->getAirplaneById();
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint not found"]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function getAllAirplanes() {
        $airplanes = $this->airplaneService->getAllAirplanes();
        echo json_encode($airplanes);
    }
    private function getAirplaneById() {
        $id = $_GET['id'] ?? null;
        $airplanes = $this->airplaneService->getById($id);
        echo json_encode($airplanes);
    }
}
