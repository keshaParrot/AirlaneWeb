<?php

use controllers\AirplaneController;

require_once __DIR__ . '/../controllers/AirplaneController.php';
require_once __DIR__ . '/../services/AirplaneService.php';
require_once __DIR__ . '/../repositories/AirplaneRepository.php';

class AirplaneControllerTest {
    private $pdo;
    private AirplaneController $controller;

    public function __construct() {
        $this->pdo = $this->createMockPDO();
        $this->controller = new AirplaneController($this->pdo);
    }

    private function createMockPDO() {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("CREATE TABLE airplanes (
            id INTEGER PRIMARY KEY,
            brand TEXT NOT NULL,
            model TEXT NOT NULL,
            internal_number TEXT NOT NULL,
            registration_number TEXT NOT NULL,
            seat_count INTEGER NOT NULL
        );");

        return $pdo;
    }

    public function testGetAllAirplanes() {
        $this->pdo->exec("INSERT INTO airlinemanagement.airplanes (brand, model, internal_number, registration_number, seat_count) 
                          VALUES ('Boeing', '737', 'INT001', 'REG001', 180),
                                 ('Airbus', 'A320', 'INT002', 'REG002', 150)");

        ob_start();
        $this->controller->handleRequest('GET', ['airplanes']);
        $output = ob_get_clean();

        $airplanes = json_decode($output, true);

        assert(is_array($airplanes), 'Expected an array of airplanes.');
        assert(count($airplanes) === 2, 'Expected 2 airplanes in the response.');
        assert($airplanes[0]['brand'] === 'Boeing', 'First airplane brand mismatch.');

        echo "testGetAllAirplanes passed!\n";
    }

    public function testGetAirplaneById() {
        $this->pdo->exec("INSERT INTO airlinemanagement.airplanes (id, brand, model, internal_number, registration_number, seat_count) 
                          VALUES (1, 'Boeing', '737', 'INT001', 'REG001', 180)");

        $_GET['id'] = 1;

        ob_start();
        $this->controller->handleRequest('GET', ['airplanes', 'get']);
        $output = ob_get_clean();

        $airplane = json_decode($output, true);

        assert(is_array($airplane), 'Expected an array for airplane details.');
        assert($airplane['brand'] === 'Boeing', 'Airplane brand mismatch.');
        assert($airplane['seat_count'] === 180, 'Airplane seat count mismatch.');

        echo "testGetAirplaneById passed!\n";
    }
}