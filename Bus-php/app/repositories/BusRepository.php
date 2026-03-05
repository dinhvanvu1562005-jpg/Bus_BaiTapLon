<?php
require_once __DIR__ . '/../config/database.php';

class BusRepository {

  public function all(): array {
    $pdo = Database::conn();
    $st = $pdo->query("SELECT * FROM buses ORDER BY id DESC");
    return $st->fetchAll();
  }

  public function create(string $plateNo, string $busType, int $seatCount): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("INSERT INTO buses (plate_no, bus_type, seat_count) VALUES (?, ?, ?)");
    $st->execute([$plateNo, $busType, $seatCount]);
  }

  public function find(int $id): ?array {
    $pdo = Database::conn();
    $st = $pdo->prepare("SELECT * FROM buses WHERE id = ?");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public function update(int $id, string $plateNo, string $busType, int $seatCount, int $isActive): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("UPDATE buses SET plate_no=?, bus_type=?, seat_count=?, is_active=? WHERE id=?");
    $st->execute([$plateNo, $busType, $seatCount, $isActive, $id]);
  }

  public function delete(int $id): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("DELETE FROM buses WHERE id=?");
    $st->execute([$id]);
  }
}