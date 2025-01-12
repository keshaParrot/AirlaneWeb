<?php

namespace Tests;

use services\AuthService;
use services\FlightService;


class FlightControllerTest
{
    private $flightService;
    private $authService;

    public function __construct()
    {
        $this->flightService = $this->createMock(FlightService::class);
        $this->authService = $this->createMock(AuthService::class);
    }

    public function runTests()
    {
        $this->testGetFlights();
        $this->testAddFlightAsSuperuser();
        $this->testAddFlightAsRegularUser();
        $this->testAddFlightUnauthenticated();
    }

    private function createMock($class)
    {
        return new class {
            public function __call($name, $arguments)
            {
                return null;
            }
        };
    }

    public function testGetFlights()
    {
        // Симуляція GET-запиту
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/flights';
        $_GET['departure'] = 'NYC';
        $_GET['destination'] = 'LAX';

        ob_start();
        include 'controllers/FlightController.php';
        $response = ob_get_clean();

        echo "Test GET flights: " . (str_contains($response, '[]') ? 'Passed' : 'Failed') . PHP_EOL;
    }

    public function testAddFlightAsSuperuser()
    {
        // Симуляція POST-запиту
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/flights';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid_superuser_token';

        // Дані для тіла запиту
        $postData = [
            'price' => 200.0,
            'departureDateTime' => '2025-01-15 08:00:00',
            'arrivalDateTime' => '2025-01-15 12:00:00',
            'departureAirportId' => 1,
            'destinationAirportId' => 2,
            'airplaneId' => 1,
            'createdBy' => 1
        ];
        file_put_contents('php://input', json_encode($postData));

        ob_start();
        include 'controllers/FlightController.php';
        $response = ob_get_clean();

        echo "Test POST add flight as superuser: " . (str_contains($response, '"success":true') ? 'Passed' : 'Failed') . PHP_EOL;
    }

    public function testAddFlightAsRegularUser()
    {
        // Симуляція POST-запиту від звичайного користувача
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/flights';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid_regular_user_token';

        $postData = [
            'price' => 200.0,
            'departureDateTime' => '2025-01-15 08:00:00',
            'arrivalDateTime' => '2025-01-15 12:00:00',
            'departureAirportId' => 1,
            'destinationAirportId' => 2,
            'airplaneId' => 1,
            'createdBy' => 1
        ];
        file_put_contents('php://input', json_encode($postData));

        ob_start();
        include 'controllers/FlightController.php';
        $response = ob_get_clean();

        echo "Test POST add flight as regular user: " . (str_contains($response, '"error":"Forbidden') ? 'Passed' : 'Failed') . PHP_EOL;
    }

    public function testAddFlightUnauthenticated()
    {
        // Симуляція POST-запиту без авторизації
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/flights';

        $postData = [
            'price' => 200.0,
            'departureDateTime' => '2025-01-15 08:00:00',
            'arrivalDateTime' => '2025-01-15 12:00:00',
            'departureAirportId' => 1,
            'destinationAirportId' => 2,
            'airplaneId' => 1,
            'createdBy' => 1
        ];
        file_put_contents('php://input', json_encode($postData));

        ob_start();
        include 'controllers/FlightController.php';
        $response = ob_get_clean();

        echo "Test POST add flight unauthenticated: " . (str_contains($response, '"error":"Unauthorized') ? 'Passed' : 'Failed') . PHP_EOL;
    }
}

// Запуск тестів
$test = new FlightControllerTest();
$test->runTests();
