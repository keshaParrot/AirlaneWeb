<?php

namespace services;

use repositories\FlightRepository;

class FlightService {
    private FlightRepository $flightRepository;

    public function __construct(FlightRepository $flightRepository)
    {
        $this->flightRepository = $flightRepository;
    }

    public function getFilteredFlights(
        ?string $departure = null,
        ?string $destination = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?string $departureDate = null,
        ?string $timeFilter = null,
        int $limit = 10,
        int $offset = 0
    ): array {
        $rawFlights = $this->flightRepository->getFilteredFlightsWithDetails(
            $departure,
            $destination,
            $minPrice,
            $maxPrice,
            $departureDate,
            $timeFilter,
            $limit,
            $offset
        );

        $flights = [];
        foreach ($rawFlights as $flightData) {
            $flights[] = new Flights(
                $flightData['id'],
                $flightData['price'],
                $flightData['departure'],
                $flightData['destination'],
                $flightData['departureDateTime'],
                $flightData['arrivalDateTime'],
                $flightData['availableSeats'],
                $flightData['airplane']
            );
        }

        return $flights;
    }

    public function addFlight(
        float $price,
        string $departureDateTime,
        string $arrivalDateTime,
        int $departureAirportId,
        int $destinationAirportId,
        int $airplaneId,
        int $createdBy
    ): bool {
        return $this->flightRepository->save(
            $price,
            $departureDateTime,
            $arrivalDateTime,
            $departureAirportId,
            $destinationAirportId,
            $airplaneId,
            $createdBy
        );
    }
}