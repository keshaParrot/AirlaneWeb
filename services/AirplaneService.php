<?php

namespace services;

use domain\Airplane;
use repositories\AirplaneRepository;

class AirplaneService
{
    private AirplaneRepository $airplaneRepository;

    public function __construct(AirplaneRepository $airplaneRepository)
    {
        $this->airplaneRepository = $airplaneRepository;
    }

    public function getAllAirplanes(): array
    {
        $rawAirplanes = $this->airplaneRepository->findAll();

        $airplanes = [];
        foreach ($rawAirplanes as $airplaneData) {
            $airplanes[] = new Airplane(
                $airplaneData['id'],
                $airplaneData['brand'],
                $airplaneData['model'],
                $airplaneData['internal_number'],
                $airplaneData['registration_number'],
                $airplaneData['seat_count']
            );
        }

        return $airplanes;
    }
}