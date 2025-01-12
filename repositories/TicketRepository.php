<?php

namespace repositories;

use PDO;

class TicketRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function addTicket(
        string $purchasedDate,
        int $flightId,
        int $userId,
        string $userName,
        float $price
    ): bool {
        $sql = "
            INSERT INTO airlinemanagement.Purchased_ticket (
                ticket_number,
                purchased_date,
                Flight_id,
                User_id,
                user_name,
                price
            )
            VALUES (
                :ticketNumber,
                :purchasedDate,
                :flightId,
                :userId,
                :userName,
                :price
            )
        ";
        $ticketNumber = uniqid('TICKET_', true);
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':ticketNumber' => $ticketNumber,
            ':purchasedDate' => $purchasedDate,
            ':flightId' => $flightId,
            ':userId' => $userId,
            ':userName' => $userName,
            ':price' => $price
        ]);
    }

    public function deleteTicketById(int $id): bool
    {
        $sql = "DELETE FROM airlinemanagement.Purchased_ticket WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    public function getTicketsByUserId(int $userId): array
    {
        $sql = "SELECT * FROM airlinemanagement.Purchased_ticket WHERE User_id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTicketById(int $ticketId): ?object
    {
        $sql = "SELECT * FROM airlinemanagement.Purchased_ticket WHERE id = :ticketId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $ticketId]);

        return $stmt->fetch(PDO::FETCH_ASSOC)?:null;
    }

}
