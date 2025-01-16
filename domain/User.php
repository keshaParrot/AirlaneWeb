<?php

namespace domain;
class User implements \JsonSerializable
{

    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $isSuperUser;
    public $walletBalance;

    /**
     * @param $id
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $isSuperUser
     */
    public function __construct($id, $firstName, $lastName, $email, $isSuperUser, $walletBalance)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->isSuperUser = $isSuperUser;
        $this->walletBalance = $walletBalance;
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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getIsSuperUser()
    {
        return $this->isSuperUser;
    }

    /**
     * @param mixed $isSuperUser
     */
    public function setIsSuperUser($isSuperUser)
    {
        $this->isSuperUser = $isSuperUser;
    }
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'isSuperUser' => $this->isSuperUser,
            'walletBalance' => $this->walletBalance,
        ];
    }
}
