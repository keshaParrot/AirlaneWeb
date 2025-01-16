<?php

namespace domain;
class ticket implements \JsonSerializable
{
    public $id {
        get {
            return $this->id;
        }
        set {
            $this->id = $value;
        }
    }
    public $purchasedDate {
        get {
            return $this->purchasedDate;
        }
        set {
            $this->purchasedDate = $value;
        }
    }
    public $purchasedTime {
        get {
            return $this->purchasedTime;
        }
        set {
            $this->purchasedTime = $value;
        }
    }
    public $ownerFullName {
        get {
            return $this->ownerFullName;
        }
        set {
            $this->ownerFullName = $value;
        }
    }
    public $price {
        get {
            return $this->price;
        }
        set {
            $this->price = $value;
        }
    }

    /**
     * @param $id
     * @param $purchasedDate
     * @param $purchasedTime
     * @param $ownerFullName
     * @param $price
     */
    public function __construct($id, $purchasedDate, $purchasedTime, $ownerFullName, $price)
    {
        $this->id = $id;
        $this->purchasedDate = $purchasedDate;
        $this->purchasedTime = $purchasedTime;
        $this->ownerFullName = $ownerFullName;
        $this->price = $price;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'purchasedDate' => $this->purchasedDate,
            'purchasedTime' => $this->purchasedTime,
            'ownerFullName' => $this->ownerFullName,
            'price' => $this->price,
        ];
    }
}