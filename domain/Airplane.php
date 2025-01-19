<?php

namespace domain;

class Airplane
{
    private $id;
    private $brand;
    private $model;
    private $internalNumber;
    private $registrationNumber;
    private $seatCount;

    /**
     * @param $id
     * @param $brand
     * @param $model
     * @param $internalNumber
     * @param $registrationNumber
     * @param $seatCount
     */
    public function __construct($id, $brand, $model, $internalNumber, $registrationNumber, $seatCount)
    {
        $this->id = $id;
        $this->brand = $brand;
        $this->model = $model;
        $this->internalNumber = $internalNumber;
        $this->registrationNumber = $registrationNumber;
        $this->seatCount = $seatCount;
    }
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'brand' => $this->brand,
            'model' => $this->model,
            'internalNumber' => $this->internalNumber,
            'registrationNumber' => $this->registrationNumber,
            'seatCount' => $this->seatCount,
        ];
    }

}