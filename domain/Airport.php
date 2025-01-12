<?php

namespace domain;

class Airport
{
    private int $id {
        get {
            return $this->id;
        }
        set {
            $this->id = $value;
        }
    }
    private string $name {
        get {
            return $this->name;
        }
        set {
            $this->name = $value;
        }
    }
    private string $country {
        get {
            return $this->country;
        }
        set {
            $this->country = $value;
        }
    }
    private string $region {
        get {
            return $this->region;
        }
        set {
            $this->region = $value;
        }
    }
    private string $city {
        get {
            return $this->city;
        }
        set {
            $this->city = $value;
        }
    }
    private string $street {
        get {
            return $this->street;
        }
        set {
            $this->street = $value;
        }
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $country
     * @param string $region
     * @param string $city
     * @param string $street
     */
    public function __construct(int $id, string $name, string $country, string $region, string $city, string $street)
    {
        $this->id = $id;
        $this->name = $name;
        $this->country = $country;
        $this->region = $region;
        $this->city = $city;
        $this->street = $street;
    }


}