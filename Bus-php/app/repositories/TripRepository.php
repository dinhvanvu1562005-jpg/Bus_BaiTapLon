<?php
require_once __DIR__ . '/../config/database.php';

class TripRepository {

  public function all(): array {
    $pdo = Database::conn();
    $sql = "
      SELECT t.*,
             r.from_city, r.to_city, r.base_price,
             b.plate_no, b.bus_type, b.seat_count
      FROM trips t
      JOIN routes r ON r.id = t.route_id
      JOIN buses  b ON b.id = t.bus_id
      ORDER BY t.depart_at DESC
    ";
    return $pdo->query($sql)->fetchAll();
  }

  public function create(int $routeId, int $busId, string $departAt): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("INSERT INTO trips(route_id,bus_id,depart_at) VALUES (?,?,?)");
    $st->execute([$routeId, $busId, $departAt]);
  }

  public function updateStatus(int $id, string $status): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("UPDATE trips SET status=? WHERE id=?");
    $st->execute([$status, $id]);
  }

  public function delete(int $id): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("DELETE FROM trips WHERE id=?");
    $st->execute([$id]);
  }
}