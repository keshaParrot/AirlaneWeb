<?php

namespace domain;
class Flights
{
    public $id;
    public $price;
    public $departure;
    public $destination;
    public $departureDateTime;
    public $arrivalDateTime;
    public $availableSeats;
    public $airplane;

    /**
     * @param $id
     * @param $price
     * @param $departure
     * @param $destination
     * @param $departureDateTime
     * @param $arrivalDateTime
     * @param $availableSeats
     * @param $airplane
     */
    public function __construct($id, $price, $departure, $destination, $departureDateTime, $arrivalDateTime, $availableSeats, $airplane)
    {
        $this->id = $id;
        $this->price = $price;
        $this->departure = $departure;
        $this->destination = $destination;
        $this->departureDateTime = $departureDateTime;
        $this->arrivalDateTime = $arrivalDateTime;
        $this->availableSeats = $availableSeats;
        $this->airplane = $airplane;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getDeparture()
    {
        return $this->departure;
    }

    /**
     * @param mixed $departure
     */
    public function setDeparture($departure)
    {
        $this->departure = $departure;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return mixed
     */
    public function getDepartureDateTime()
    {
        return $this->departureDateTime;
    }

    /**
     * @param mixed $departureDateTime
     */
    public function setDepartureDateTime($departureDateTime)
    {
        $this->departureDateTime = $departureDateTime;
    }

    /**
     * @return mixed
     */
    public function getArrivalDateTime()
    {
        return $this->arrivalDateTime;
    }

    /**
     * @param mixed $arrivalDateTime
     */
    public function setArrivalDateTime($arrivalDateTime)
    {
        $this->arrivalDateTime = $arrivalDateTime;
    }

    /**
     * @return mixed
     */
    public function getAvailableSeats()
    {
        return $this->availableSeats;
    }

    /**
     * @param mixed $availableSeats
     */
    public function setAvailableSeats($availableSeats)
    {
        $this->availableSeats = $availableSeats;
    }

    /**
     * @return mixed
     */
    public function getAirplane()
    {
        return $this->airplane;
    }

    /**
     * @param mixed $airplane
     */
    public function setAirplane($airplane)
    {
        $this->airplane = $airplane;
    }


}