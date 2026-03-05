<?php
require_once __DIR__ . '/../config/database.php';

class RouteRepository {

  public function all(): array {
    $pdo = Database::conn();
    return $pdo->query("SELECT * FROM routes ORDER BY id DESC")->fetchAll();
  }

  public function create(string $from, string $to, ?string $departTime, int $price): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("INSERT INTO routes(from_city,to_city,depart_time,base_price) VALUES (?,?,?,?)");
    $st->execute([$from, $to, $departTime, $price]);
  }

  public function delete(int $id): void {
    $pdo = Database::conn();
    $st = $pdo->prepare("DELETE FROM routes WHERE id=?");
    $st->execute([$id]);
  }
}