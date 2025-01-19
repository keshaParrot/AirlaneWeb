<?php

namespace domain;


class Airport implements \JsonSerializable
{
    private int $id ;
    private string $name;
    private string $country;
    private string $region ;
    private string $city ;
    private string $street ;

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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'street' => $this->street,
        ];
    }
}