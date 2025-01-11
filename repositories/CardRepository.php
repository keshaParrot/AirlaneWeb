<?php

namespace repositories;

use PDO;

class CardRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addCard(string $cardNumber, string $expiryDate): int
    {
        $sql = "
            INSERT INTO airlinemanagement.card (
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
        $sql = "DELETE FROM airlinemanagement.card WHERE id = :cardId";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':cardId' => $cardId]);
    }
    public function getAllByUserId(int $userId): array
    {
        $sql = "
            SELECT ac.* 
            FROM airlinemanagement.card ac
            INNER JOIN airlinemanagement.users u ON u.card_id = ac.id
            WHERE u.id = :userId
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCardId(int $cardId): ?array
    {
        $sql = "SELECT * FROM airlinemanagement.card WHERE id = :cardId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':cardId' => $cardId]);

        $card = $stmt->fetch(PDO::FETCH_ASSOC);
        return $card ?: null;
    }
}