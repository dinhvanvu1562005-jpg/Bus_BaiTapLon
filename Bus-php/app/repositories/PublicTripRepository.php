<?php
require_once __DIR__ . '/../config/database.php';

class PublicTripRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conn();
    }

    public function searchTrips(string $fromCity, string $toCity, ?string $date = null): array
    {
        $sql = "
            SELECT
                t.id,
                t.depart_at,
                t.status,
                r.from_city,
                r.to_city,
                r.base_price,
                b.plate_no,
                b.bus_type,
                b.seat_count,
                COALESCE(x.booked_count, 0) AS booked_count
            FROM trips t
            JOIN routes r ON r.id = t.route_id
            JOIN buses b ON b.id = t.bus_id
            LEFT JOIN (
                SELECT trip_id, COUNT(*) AS booked_count
                FROM tickets
                GROUP BY trip_id
            ) x ON x.trip_id = t.id
            WHERE r.from_city LIKE ?
              AND r.to_city LIKE ?
        ";

        $params = [
            '%' . $fromCity . '%',
            '%' . $toCity . '%'
        ];

        if (!empty($date)) {
            $sql .= " AND DATE(t.depart_at) = ? ";
            $params[] = $date;
        }

        $sql .= " ORDER BY t.depart_at ASC ";

        $st = $this->pdo->prepare($sql);
        $st->execute($params);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findTripById(int $tripId): ?array
    {
        $sql = "
            SELECT
                t.id,
                t.depart_at,
                t.status,
                r.from_city,
                r.to_city,
                r.base_price,
                b.id AS bus_id,
                b.plate_no,
                b.bus_type,
                b.seat_count,
                COALESCE(x.booked_count, 0) AS booked_count
            FROM trips t
            JOIN routes r ON r.id = t.route_id
            JOIN buses b ON b.id = t.bus_id
            LEFT JOIN (
                SELECT trip_id, COUNT(*) AS booked_count
                FROM tickets
                GROUP BY trip_id
            ) x ON x.trip_id = t.id
            WHERE t.id = ?
            LIMIT 1
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([$tripId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getBookedSeats(int $tripId): array
    {
        $sql = "
            SELECT seat_no
            FROM tickets
            WHERE trip_id = ?
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([$tripId]);

        return array_map('intval', array_column($st->fetchAll(PDO::FETCH_ASSOC), 'seat_no'));
    }
}