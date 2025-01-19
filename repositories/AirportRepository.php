<?php

namespace repositories;

use PDO;

class AirportRepository
{
    private PDO $pdo;
    private string $dbName;

    public function __construct(PDO $pdo, string $dbName)
    {
        $this->pdo = $pdo;
        $this->dbName = $dbName;
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->dbName}.airports";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->dbName}.airports WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}