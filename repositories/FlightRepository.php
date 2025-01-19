<?php

namespace repositories;

use domain\Flights;
use PDO;

class FlightRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getFilteredFlightsWithDetails(
        ?string $departure = null,
        ?string $destination = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?string $departureDate = null,
        ?string $timeFilter = null,
        int $limit = 10,
        int $offset = 0
    ): array {
        $sql = "
        SELECT 
            f.id,
            f.price,
            f.departure_date AS departureDateTime,
            f.arrival_date AS arrivalDateTime,
            ap_from.name AS departure,
            ap_to.name AS destination,
            a.seat_count - COALESCE((
                SELECT COUNT(*) 
                FROM airlinemanagement.Purchased_ticket pt 
                WHERE pt.Flight_id = f.id
            ), 0) AS availableSeats,
            CONCAT(a.brand, ' ', a.model) AS airplane
        FROM airlinemanagement.Flights f
        JOIN airlinemanagement.Airports ap_from ON f.departure_airport_id = ap_from.id
        JOIN airlinemanagement.Airports ap_to ON f.destination_airport_id = ap_to.id
        JOIN airlinemanagement.Airplanes a ON f.airplane_id = a.id
        WHERE f.departure_date > DATE_ADD(NOW(), INTERVAL 6 HOUR)
        ";

        $params = [];

        if ($departure) {
            $sql .= " AND ap_from.name LIKE :departure";
            $params[':departure'] = '%' . $departure . '%';
        }
        if ($destination) {
            $sql .= " AND ap_to.name LIKE :destination";
            $params[':destination'] = '%' . $destination . '%';
        }
        if ($minPrice) {
            $sql .= " AND f.price >= :minPrice";
            $params[':minPrice'] = $minPrice;
        }
        if ($maxPrice) {
            $sql .= " AND f.price <= :maxPrice";
            $params[':maxPrice'] = $maxPrice;
        }
        if ($departureDate) {
            $sql .= " AND DATE(f.departure_date) = :departureDate";
            $params[':departureDate'] = $departureDate;
        }
        if ($timeFilter) {
            $sql .= " AND TIME(f.departure_date) = :timeFilter";
            $params[':timeFilter'] = $timeFilter;
        }

        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            if (in_array($key, [':limit', ':offset'])) {
                $stmt->bindValue($key, (int) $value, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save(
        float $price,
        string $departureDateTime,
        string $arrivalDateTime,
        int $departureAirportId,
        int $destinationAirportId,
        int $airplaneId
    ): bool {
        $sql = "
            INSERT INTO airlinemanagement.flights (
                price, 
                departure_date, 
                arrival_date, 
                departure_airport_id, 
                destination_airport_id, 
                airplane_id
            ) 
            VALUES (
                :price, 
                :departureDateTime, 
                :arrivalDateTime, 
                :departureAirportId, 
                :destinationAirportId, 
                :airplaneId
            )
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':price' => $price,
            ':departureDateTime' => $departureDateTime,
            ':arrivalDateTime' => $arrivalDateTime,
            ':departureAirportId' => $departureAirportId,
            ':destinationAirportId' => $destinationAirportId,
            ':airplaneId' => $airplaneId,
        ]);
    }

    public function getById(int $id): ?Flights {
        $sql = "
        SELECT 
            f.id,
            f.price,
            f.departure_date AS departureDateTime,
            f.arrival_date AS arrivalDateTime,
            ap_from.name AS departure,
            ap_to.name AS destination,
            a.seat_count - COALESCE((
                SELECT COUNT(*) 
                FROM airlinemanagement.Purchased_ticket pt 
                WHERE pt.Flight_id = f.id
            ), 0) AS availableSeats,
            CONCAT(a.brand, ' ', a.model) AS airplane
        FROM airlinemanagement.Flights f
        JOIN airlinemanagement.Airports ap_from ON f.departure_airport_id = ap_from.id
        JOIN airlinemanagement.Airports ap_to ON f.destination_airport_id = ap_to.id
        JOIN airlinemanagement.Airplanes a ON f.airplane_id = a.id
        WHERE f.id = :id
        LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $flightData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$flightData) {
            return null; // Якщо рейс із вказаним ID не знайдено
        }

        return new Flights(
            $flightData['id'],
            $flightData['price'],
            $flightData['departure'],
            $flightData['destination'],
            $flightData['departureDateTime'],
            $flightData['arrivalDateTime'],
            $flightData['availableSeats'],
            $flightData['airplane']
        );
    }

}
