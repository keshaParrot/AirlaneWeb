<?php

namespace domain;

class Flights implements \JsonSerializable
{
    private $id;
    private $price;
    private $departure;
    private $destination;
    private $departureDateTime;
    private $arrivalDateTime;
    private $availableSeats;
    private $airplane;

    /**
     * Constructor for the Flights class.
     *
     * @param int $id
     * @param float $price
     * @param string $departure
     * @param string $destination
     * @param string $departureDateTime
     * @param string $arrivalDateTime
     * @param int $availableSeats
     * @param string $airplane
     */
    public function __construct(
        int $id,
        float $price,
        string $departure,
        string $destination,
        string $departureDateTime,
        string $arrivalDateTime,
        int $availableSeats,
        string $airplane
    ) {
        $this->id = $id;
        $this->price = $price;
        $this->departure = $departure;
        $this->destination = $destination;
        $this->departureDateTime = $departureDateTime;
        $this->arrivalDateTime = $arrivalDateTime;
        $this->availableSeats = $availableSeats;
        $this->airplane = $airplane;
    }

    // Getters and setters for each property
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getDeparture(): string
    {
        return $this->departure;
    }

    public function setDeparture(string $departure): void
    {
        $this->departure = $departure;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): void
    {
        $this->destination = $destination;
    }

    public function getDepartureDateTime(): string
    {
        return $this->departureDateTime;
    }

    public function setDepartureDateTime(string $departureDateTime): void
    {
        $this->departureDateTime = $departureDateTime;
    }

    public function getArrivalDateTime(): string
    {
        return $this->arrivalDateTime;
    }

    public function setArrivalDateTime(string $arrivalDateTime): void
    {
        $this->arrivalDateTime = $arrivalDateTime;
    }

    public function getAvailableSeats(): int
    {
        return $this->availableSeats;
    }

    public function setAvailableSeats(int $availableSeats): void
    {
        $this->availableSeats = $availableSeats;
    }

    public function getAirplane(): string
    {
        return $this->airplane;
    }

    public function setAirplane(string $airplane): void
    {
        $this->airplane = $airplane;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'departure' => $this->departure,
            'destination' => $this->destination,
            'departureDateTime' => $this->departureDateTime,
            'arrivalDateTime' => $this->arrivalDateTime,
            'availableSeats' => $this->availableSeats,
            'airplane' => $this->airplane,
        ];
    }
}
