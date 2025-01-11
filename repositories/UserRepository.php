<?php

namespace repositories;

use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save($email, $password, $firstName, $lastName): bool
    {
        $walletBalance = 0.00;
        $cardId = null;
        $sql = "INSERT INTO Users (email, password, first_name, last_name, wallet_balance, card_id)
                VALUES (:email, :password, :first_name, :last_name, :wallet_balance, :card_id)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':wallet_balance' => $walletBalance,
            ':card_id' => $cardId
        ]);
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM Users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function update($id, $email = null, $firstName = null, $lastName = null, $walletBalance = null, $cardId = null): bool
    {
        $fields = [];
        $params = [':id' => $id];

        if ($email !== null) {
            $fields[] = "email = :email";
            $params[':email'] = $email;
        }

        if ($firstName !== null) {
            $fields[] = "first_name = :first_name";
            $params[':first_name'] = $firstName;
        }

        if ($lastName !== null) {
            $fields[] = "last_name = :last_name";
            $params[':last_name'] = $lastName;
        }

        if ($walletBalance !== null) {
            $fields[] = "wallet_balance = :wallet_balance";
            $params[':wallet_balance'] = $walletBalance;
        }

        if ($cardId !== null) {
            $fields[] = "card_id = :card_id";
            $params[':card_id'] = $cardId;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE Users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function getByEmail($email): ?array
    {
        $sql = "SELECT * FROM Users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        $sql = "UPDATE Users SET password = :newPassword WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':newPassword' => password_hash($newPassword, PASSWORD_BCRYPT)
        ]);
    }

    public function addMoneyToWallet($id, $amount): bool
    {
        $sql = "UPDATE Users 
            SET wallet_balance = wallet_balance + :amount 
            WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':amount' => $amount
        ]);
    }
    public function subtractMoneyFromWallet($id, $amount): bool
    {
        $sql = "UPDATE Users 
            SET wallet_balance = wallet_balance - :amount 
            WHERE id = :id AND wallet_balance >= :amount";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':amount' => $amount
        ]);
    }
}
