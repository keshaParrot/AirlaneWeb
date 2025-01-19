<?php

namespace repositories;

use PDO;

class TransactionRepository
{
    private PDO $pdo;
    private string $dbName;

    public function __construct(PDO $pdo, string $dbName)
    {
        $this->pdo = $pdo;
        $this->dbName = $dbName;
    }

    public function addTransaction(
        int $amount,
        int $userId,
        string $transactionType,
        string $date,
        ?int $cardId,
        string $paymentMethod
    ): bool {
        $sql = "
            INSERT INTO {$this->dbName}.Transaction (
                amount,
                User_id,
                transaction_type,
                transaction_date,
                card_id,
                payment_method
            )
            VALUES (
                :amount,
                :userId,
                :transactionType,
                :date,
                :cardId,
                :paymentMethod
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':amount' => $amount,
            ':userId' => $userId,
            ':transactionType' => $transactionType,
            ':date' => $date,
            ':cardId' => $cardId,
            ':paymentMethod' => $paymentMethod,
        ]);
    }

    public function deleteTransactionById(int $id): bool
    {
        $sql = "DELETE FROM {$this->dbName}.Transaction WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    public function getTransactionsByUserId(int $userId): array
    {
        $sql = "SELECT * FROM {$this->dbName}.Transaction WHERE User_id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
