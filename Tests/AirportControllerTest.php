<?php

namespace Tests;

require_once __DIR__ . '/../controllers/AirportController.php';
require_once __DIR__ . '/../Middleware.php';

class AirportControllerTest
{
    public function runTests()
    {
        echo "Running tests for AirportController...\n";
        $this->testGetAirports();
        $this->testAddAirportAsSuperuser();
        $this->testAddAirportUnauthorized();
    }

    private function testGetAirports()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/airports';
        $_GET = [
            'country' => 'USA',
        ];

        ob_start();
        include 'controllers/AirportController.php';
        $response = ob_get_clean();

        echo "Test GET airports: " . (str_contains($response, '"airports"') ? 'Passed' : 'Failed') . "\n";
    }

    private function testAddAirportAsSuperuser()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/airports';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid_superuser_token';
        $postData = [
            'name' => 'Test Airport',
            'country' => 'USA',
            'city' => 'Test City',
        ];
        file_put_contents('php://input', json_encode($postData));

        ob_start();
        include 'controllers/AirportController.php';
        $response = ob_get_clean();

        echo "Test POST add airport as superuser: " . (str_contains($response, '"success":true') ? 'Passed' : 'Failed') . "\n";
    }

    private function testAddAirportUnauthorized()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/airports';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid_token';
        $postData = [
            'name' => 'Unauthorized Airport',
            'country' => 'Canada',
            'city' => 'Unauthorized City',
        ];
        file_put_contents('php://input', json_encode($postData));

        ob_start();
        include 'controllers/AirportController.php';
        $response = ob_get_clean();

        echo "Test POST add airport unauthorized: " . (str_contains($response, '"error":"Unauthorized') ? 'Passed' : 'Failed') . "\n";
    }
}

$test = new AirportControllerTest();
$test->runTests();