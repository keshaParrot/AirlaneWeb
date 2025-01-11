<?php

namespace domain;
class ticket
{
    public $id;
    public $purchasedDate;
    public $purchasedTime;
    public $ownerFullName;
    public $price;

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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPurchasedDate()
    {
        return $this->purchasedDate;
    }

    /**
     * @return mixed
     */
    public function getPurchasedTime()
    {
        return $this->purchasedTime;
    }

    /**
     * @return mixed
     */
    public function getOwnerFullName()
    {
        return $this->ownerFullName;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $purchasedDate
     */
    public function setPurchasedDate($purchasedDate)
    {
        $this->purchasedDate = $purchasedDate;
    }

    /**
     * @param mixed $purchasedTime
     */
    public function setPurchasedTime($purchasedTime)
    {
        $this->purchasedTime = $purchasedTime;
    }

    /**
     * @param mixed $ownerFullName
     */
    public function setOwnerFullName($ownerFullName)
    {
        $this->ownerFullName = $ownerFullName;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }


}