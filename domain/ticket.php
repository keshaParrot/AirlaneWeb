<?php

namespace domain;
class ticket implements \JsonSerializable
{
    public $id;
    public $purchasedDate;
    public $departuretime;
    public $ownerFullName;
    public $price;

    /**
     * @param $id
     * @param $purchasedDate
     * @param $departuretime
     * @param $ownerFullName
     * @param $price
     */
    public function __construct($id, $purchasedDate, $departuretime, $ownerFullName, $price)
    {
        $this->id = $id;
        $this->purchasedDate = $purchasedDate;
        $this->departuretime = $departuretime;
        $this->ownerFullName = $ownerFullName;
        $this->price = $price;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'purchasedDate' => $this->purchasedDate,
            'departureTime' => $this->departuretime,
            'ownerFullName' => $this->ownerFullName,
            'price' => $this->price,
        ];
    }
}