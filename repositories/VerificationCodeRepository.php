<?php

namespace repositories;

use PDO;

class VerificationCodeRepository
{
    private PDO $pdo;
    private string $dbName;

    public function __construct(PDO $pdo, string $dbName)
    {
        $this->pdo = $pdo;
        $this->dbName = $dbName;
    }

    public function addVerificationCode(int $userId, string $verificationCode, string $createDate): bool
    {
        $sql = "
            INSERT INTO  {$this->dbName}.verification (
                User_id,
                Verification_code,
                used,
                Create_date
            )
            VALUES (
                :userId,
                :verificationCode,
                :isUsed,
                :createDate
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':userId' => $userId,
            ':verificationCode' => $verificationCode,
            ':isUsed' => 0,
            ':createDate' => $createDate,
        ]);
    }

    public function findVerificationCode(string $verificationCode): ?array
    {
        $sql = "
            SELECT * 
            FROM {$this->dbName}.Verification 
            WHERE Verification_code = :verificationCode AND used = 0
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':verificationCode' => $verificationCode,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function markAsUsed(int $verificationId): bool
    {
        $sql = "UPDATE {$this->dbName}.Verification SET used = 1 WHERE Verification_id = :verificationId";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':verificationId' => $verificationId,
        ]);
    }
}
