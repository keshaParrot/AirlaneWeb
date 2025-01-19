<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use controllers\AirplaneController;
use repositories\AirplaneRepository;
use services\AirplaneService;

class AirplaneControllerTest extends TestCase {

    private $pdo;
    private $controller;

    protected function setUp(): void {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=airlinemanagementtest', 'TicketWeb', 'Y1FKaOnZig1JLS(7');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->controller = new AirplaneController($this->pdo, "airlinemanagementtest");

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("DELETE FROM airlinemanagementtest.flights");
        $this->pdo->exec("DELETE FROM airlinemanagementtest.airplanes");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.airplanes (id, brand, model, internal_number, registration_number, seat_count) VALUES (1, 'Boeing', '737', 'INT123', 'REG123', 200)");
        $this->pdo->exec("INSERT INTO airlinemanagementtest.airplanes (id, brand, model, internal_number, registration_number, seat_count) VALUES (2, 'Airbus', 'A320', 'INT456', 'REG456', 180)");
    }

    public function testGetAllAirplanes() {
        ob_start();
        $this->controller->handleRequest('GET', ['airplanes']);
        $output = ob_get_clean();

        $expected = [
            ['id' => 1, 'brand' => 'Boeing', 'model' => '737', 'internal_number' => 'INT123', 'registration_number' => 'REG123', 'seat_count' => 200],
            ['id' => 2, 'brand' => 'Airbus', 'model' => 'A320', 'internal_number' => 'INT456', 'registration_number' => 'REG456', 'seat_count' => 180]
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testGetAirplaneById() {
        $_GET['id'] = 1;

        ob_start();
        $this->controller->handleRequest('GET', ['airplanes', 'get']);
        $output = ob_get_clean();

        $expected = ['id' => 1, 'brand' => 'Boeing', 'model' => '737', 'internal_number' => 'INT123', 'registration_number' => 'REG123', 'seat_count' => 200];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    protected function tearDown(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("DELETE FROM airlinemanagementtest.flights");
        $this->pdo->exec("DELETE FROM airlinemanagementtest.airplanes");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}


