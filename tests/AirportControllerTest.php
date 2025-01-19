<?php

namespace tests;

use PHPUnit\Framework\TestCase;
use controllers\AirportController;
use repositories\AirportRepository;
use services\AirportService;

class AirportControllerTest extends TestCase {

    private $pdo;
    private $controller;

    protected function setUp(): void {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=airlinemanagementtest', 'TicketWeb', 'Y1FKaOnZig1JLS(7');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->controller = new AirportController($this->pdo , "airlinemanagementtest");

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("DELETE FROM airlinemanagementtest.flights");
        $this->pdo->exec("DELETE FROM airlinemanagementtest.airplanes");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $this->pdo->exec("INSERT INTO airlinemanagementtest.airports (id, name, country, region, city, street) VALUES (1, 'JFK', 'USA', 'New York', 'New York', 'Main St')");
        $this->pdo->exec("INSERT INTO airlinemanagementtest.airports (id, name, country, region, city, street) VALUES (2, 'LAX', 'USA', 'California', 'Los Angeles', 'Sunset Blvd')");
    }

    public function testGetAllAirports() {
        ob_start();
        $this->controller->handleRequest('GET', ['airports']);
        $output = ob_get_clean();

        $expected = [
            ['id' => 1, 'name' => 'JFK', 'country' => 'USA', 'region' => 'New York', 'city' => 'New York', 'street' => 'Main St'],
            ['id' => 2, 'name' => 'LAX', 'country' => 'USA', 'region' => 'California', 'city' => 'Los Angeles', 'street' => 'Sunset Blvd']
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    public function testGetAirportById() {
        $_GET['id'] = 1;

        ob_start();
        $this->controller->handleRequest('GET', ['airports', 'get']);
        $output = ob_get_clean();

        $expected = ['id' => 1, 'name' => 'JFK', 'country' => 'USA', 'region' => 'New York', 'city' => 'New York', 'street' => 'Main St'];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), $output);
    }

    protected function tearDown(): void {
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $this->pdo->exec("DELETE FROM airlinemanagementtest.flights");
        $this->pdo->exec("DELETE FROM airlinemanagementtest.airports");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }
}

