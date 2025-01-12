<?php

namespace Tests;

require_once 'controllers/AirplaneController.php';
require_once 'Middleware.php';


class AirplaneControllerTest
{
    public function runTests()
    {
        echo "Running tests for AirplaneController...\n";
        $this->testGetAirplanes();
        $this->testAddAirplaneAsSuperuser();
        $this->testAddAirplaneUnauthorized();
    }

    private function testGetAirplanes()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/airplanes';

        ob_start();
        include 'controllers/AirplaneController.php';
        $response = ob_get_clean();

        echo "Test GET airplanes: " . (str_contains($response, '"airplanes"') ? 'Passed' : 'Failed') . "\n";
    }

    private function testAddAirplaneAsSuperuser()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/airplanes';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid_superuser_token';
        $postData = [
            'model' => 'Boeing 747',
            'capacity' => 400,
        ];
        file_put_contents('php://input', json_encode($postData));

        ob_start();
        include 'controllers/AirplaneController.php';
        $response = ob_get_clean();

        echo "Test POST add airplane as superuser: " . (str_contains($response, '"success":true') ? 'Passed' : 'Failed') . "\n";
    }

    private function testAddAirplaneUnauthorized()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['PATH_INFO'] = '/airplanes';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid_token';
        $postData = [
            'model' => 'Unauthorized Model',
            'capacity' => 200,
        ];
        file_put_contents('php://input', json_encode($postData));

        ob_start();
        include 'controllers/AirplaneController.php';
        $response = ob_get_clean();

        echo "Test POST add airplane unauthorized: " . (str_contains($response, '"error":"Unauthorized') ? 'Passed' : 'Failed') . "\n";
    }
}

// Run tests
$test = new AirplaneControllerTest();
$test->runTests();