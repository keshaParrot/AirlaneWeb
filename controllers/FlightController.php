<?php

namespace controllers;

use services\FlightService;

class FlightController {
    private FlightService $flightService;

    public function __construct(FlightService $flightService)
    {
        $this->flightService = $flightService;
    }

    public function getFilteredFlights(array $queryParams): void
    {
        $flights = $this->flightService->getFilteredFlights(
            $queryParams['departure'] ?? null,
            $queryParams['destination'] ?? null,
            isset($queryParams['minPrice']) ? (float)$queryParams['minPrice'] : null,
            isset($queryParams['maxPrice']) ? (float)$queryParams['maxPrice'] : null,
            $queryParams['departureDate'] ?? null,
            $queryParams['timeFilter'] ?? null,
            isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10,
            isset($queryParams['offset']) ? (int)$queryParams['offset'] : 0
        );

        echo json_encode($flights);
    }

    public function addFlight(array $requestBody): void
    {
        $result = $this->flightService->addFlight(
            (float)$requestBody['price'],
            $requestBody['departureDateTime'],
            $requestBody['arrivalDateTime'],
            (int)$requestBody['departureAirportId'],
            (int)$requestBody['destinationAirportId'],
            (int)$requestBody['airplaneId'],
            (int)$requestBody['createdBy']
        );

        echo json_encode(['success' => $result]);
    }
}