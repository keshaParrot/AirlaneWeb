<?php

namespace repositories;

use MongoDB\BSON\ObjectId;
use PDO;

class CardRepository
{
    private PDO $pdo;
    private string $dbName;

    public function __construct(PDO $pdo, string $dbName)
    {
        $this->pdo = $pdo;
        $this->dbName = $dbName;
    }

    public function cardExists(string $cardNumber): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->dbName}.card WHERE card_number = :cardNumber";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cardNumber' => $cardNumber]);

        return $stmt->fetchColumn() > 0;
    }

    public function addCard(string $cardNumber, string $expiryDate): int
    {
        $sql = "
            INSERT INTO {$this->dbName}.card (
                card_number,
                expiry_date
            )
            VALUES (
                :cardNumber,
                :expiryDate
            )
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':cardNumber' => $cardNumber,
            ':expiryDate' => $expiryDate,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function deleteCardById(int $cardId): bool
    {
        $sql = "DELETE FROM {$this->dbName}.card WHERE id = :cardId";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':cardId' => $cardId]);
    }
    public function getAllByUserId(int $userId): array
    {
        $sql = "
            SELECT ac.* 
            FROM {$this->dbName}.card ac
            INNER JOIN airlinemanagement.users u ON u.card_id = ac.id
            WHERE u.id = :userId
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCardId(int $cardId): ?Object
    {
        $sql = "SELECT * FROM {$this->dbName}.card WHERE id = :cardId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cardId' => $cardId]);

        $card = $stmt->fetch(PDO::FETCH_OBJ);
        return $card ?: null;
    }
}