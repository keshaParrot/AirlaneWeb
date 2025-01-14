<?php

namespace services;

use domain\Airport;
use repositories\AirportRepository;

class AirportService
{
    private AirportRepository $airportRepository;

    public function __construct(AirportRepository $airportRepository)
    {
        $this->airportRepository = $airportRepository;
    }
    public function getAllAirports(): array {
        $rawAirports = $this->airportRepository->getAll();

        $airports = [];
        foreach ($rawAirports as $airportData) {
            $airports[] = new Airport(
                $airportData['id'],
                $airportData['name'],
                $airportData['country'],
                $airportData['region'],
                $airportData['city'],
                $airportData['street']
            );
        }

        return $airports;
    }

    public function getAirportsById(mixed $id)
    {
        return $this->airportRepository->getById($id)??null;
    }
}