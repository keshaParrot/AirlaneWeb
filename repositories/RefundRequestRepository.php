<?php

namespace repositories;

use PDO;

class RefundRequestRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addRefundRequest(
        string $requestDate,
        string $refundStatus,
        float $refundAmount,
        int $purchasedTicketId,
        int $userId
    ): bool {
        $sql = "
            INSERT INTO airlinemanagement.RefundRequest (
                Request_date,
                Refund_status,
                Refund_amount,
                Purchased_ticket_id,
                User_id
            )
            VALUES (
                :requestDate,
                :refundStatus,
                :refundAmount,
                :purchasedTicketId,
                :userId
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':requestDate' => $requestDate,
            ':refundStatus' => $refundStatus,
            ':refundAmount' => $refundAmount,
            ':purchasedTicketId' => $purchasedTicketId,
            ':userId' => $userId,
        ]);
    }

    public function updateRefundStatus(int $refundId, string $newStatus): bool
    {
        $sql = "UPDATE airlinemanagement.RefundRequest SET Refund_status = :newStatus WHERE Refund_id = :refundId";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':newStatus' => $newStatus,
            ':refundId' => $refundId,
        ]);
    }

    public function getRefundRequestsByUserId(int $userId): array
    {
        $sql = "SELECT * FROM airlinemanagement.RefundRequest WHERE User_id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRefundRequestById(int $refundId): ?array
    {
        $sql = "SELECT * FROM airlinemanagement.RefundRequest WHERE Refund_id = :refundId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':refundId' => $refundId]);

        $refundRequest = $stmt->fetch(PDO::FETCH_ASSOC);
        return $refundRequest ?: null;
    }
}
