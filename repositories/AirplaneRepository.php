<?php

namespace repositories;

use PDO;

class AirplaneRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(): array{
        $sql = $this->pdo->prepare("SELECT * FROM airlinemanagement.Airplanes");
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}