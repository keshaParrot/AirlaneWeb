<?php

namespace domain;

class Airplane
{
    private $id {
        get {
            return $this->id;
        }
        set {
            $this->id = $value;
        }
    }
    private $brand {
        get {
            return $this->brand;
        }
        set {
            $this->brand = $value;
        }
    }
    private $model {
        get {
            return $this->model;
        }
        set {
            $this->model = $value;
        }
    }
    private $internalNumber {
        get {
            return $this->internalNumber;
        }
        set {
            $this->internalNumber = $value;
        }
    }
    private $registrationNumber {
        get {
            return $this->registrationNumber;
        }
        set {
            $this->registrationNumber = $value;
        }
    }
    private $seatCount {
        get {
            return $this->seatCount;
        }
        set {
            $this->seatCount = $value;
        }
    }

    /**
     * @param $id
     * @param $brand
     * @param $model
     * @param $internamNumber
     * @param $registrationNumber
     * @param $seatCount
     */
    public function __construct($id, $brand, $model, $internamNumber, $registrationNumber, $seatCount)
    {
        $this->id = $id;
        $this->brand = $brand;
        $this->model = $model;
        $this->internalNumber = $internamNumber;
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