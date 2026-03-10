<?php
require_once __DIR__ . '/../config/database.php';

class BookingRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conn();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function createBooking(array $data): int
    {
        $sql = "
            INSERT INTO bookings (
                booking_code,
                customer_name,
                customer_phone,
                customer_email,
                trip_id,
                total_amount,
                payment_status,
                booking_status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([
            $data['booking_code'],
            $data['customer_name'],
            $data['customer_phone'],
            $data['customer_email'],
            $data['trip_id'],
            $data['total_amount'],
            $data['payment_status'],
            $data['booking_status'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function addBookingSeat(int $bookingId, int $seatNo, int $price): void
    {
        $sql = "
            INSERT INTO booking_seats (booking_id, seat_no, price)
            VALUES (?, ?, ?)
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([$bookingId, $seatNo, $price]);
    }

    public function createPayment(int $bookingId, string $method, int $amount, string $status = 'pending'): void
    {
        $sql = "
            INSERT INTO payments (booking_id, payment_method, amount, payment_status)
            VALUES (?, ?, ?, ?)
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([$bookingId, $method, $amount, $status]);
    }

    public function createTicket(array $data): void
    {
        $sql = "
            INSERT INTO tickets (
                trip_id,
                seat_no,
                customer_name,
                customer_phone,
                price,
                ticket_code,
                sold_by
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([
            $data['trip_id'],
            $data['seat_no'],
            $data['customer_name'],
            $data['customer_phone'],
            $data['price'],
            $data['ticket_code'],
            $data['sold_by'],
        ]);
    }

    public function findBookingByCode(string $bookingCode): ?array
    {
        $sql = "
            SELECT *
            FROM bookings
            WHERE booking_code = ?
            LIMIT 1
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([$bookingCode]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getBookingSeats(int $bookingId): array
    {
        $sql = "
            SELECT seat_no, price
            FROM booking_seats
            WHERE booking_id = ?
            ORDER BY seat_no ASC
        ";

        $st = $this->pdo->prepare($sql);
        $st->execute([$bookingId]);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}