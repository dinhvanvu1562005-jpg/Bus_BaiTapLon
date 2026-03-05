<?php
require_once __DIR__ . '/../config/database.php';

class CustomerRepository {

  public function findOrCreate(string $name, string $phone): int {
    $pdo = Database::conn();

    $st = $pdo->prepare("SELECT id FROM customers WHERE phone=? LIMIT 1");
    $st->execute([$phone]);
    $row = $st->fetch();
    if ($row) return (int)$row['id'];

    $st2 = $pdo->prepare("INSERT INTO customers(full_name, phone) VALUES (?, ?)");
    $st2->execute([$name, $phone]);
    return (int)$pdo->lastInsertId();
  }
}
