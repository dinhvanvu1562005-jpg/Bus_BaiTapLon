<?php
require_once __DIR__ . '/../config/database.php';

class TicketRepository {

  public function seatsBooked(int $tripId): array {
    $pdo = Database::conn();
    $st = $pdo->prepare("SELECT seat_no FROM tickets WHERE trip_id=? AND status='booked'");
    $st->execute([$tripId]);
    return array_map(fn($r)=>(int)$r['seat_no'], $st->fetchAll());
  }

  public function create(string $code, int $tripId, int $customerId, int $seatNo, int $price): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("
      INSERT INTO tickets(ticket_code, trip_id, customer_id, seat_no, price)
      VALUES (?,?,?,?,?)
    ");
    $st->execute([$code, $tripId, $customerId, $seatNo, $price]);
  }

  /** Danh sách hành khách theo chuyến */
  public function listByTrip(int $tripId): array {
    $pdo = Database::conn();
    $st = $pdo->prepare("
      SELECT tk.id AS ticket_id, tk.ticket_code, tk.seat_no, tk.price, tk.status, tk.created_at,
             c.full_name, c.phone
      FROM tickets tk
      JOIN customers c ON c.id = tk.customer_id
      WHERE tk.trip_id = ?
      ORDER BY tk.seat_no ASC
    ");
    $st->execute([$tripId]);
    return $st->fetchAll();
  }

  /** Hủy vé */
  public function cancel(int $ticketId): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("UPDATE tickets SET status='canceled' WHERE id=?");
    $st->execute([$ticketId]);
  }

  /** Lấy thông tin vé để in */
  public function findDetail(int $ticketId): ?array {
    $pdo = Database::conn();
    $st = $pdo->prepare("
      SELECT tk.*, c.full_name, c.phone,
             t.depart_at, t.status AS trip_status,
             r.from_city, r.to_city, r.base_price,
             b.plate_no, b.bus_type, b.seat_count
      FROM tickets tk
      JOIN customers c ON c.id=tk.customer_id
      JOIN trips t ON t.id=tk.trip_id
      JOIN routes r ON r.id=t.route_id
      JOIN buses b ON b.id=t.bus_id
      WHERE tk.id=?
      LIMIT 1
    ");
    $st->execute([$ticketId]);
    $row = $st->fetch();
    return $row ?: null;
  }
}