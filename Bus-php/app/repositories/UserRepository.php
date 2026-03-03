<?php
require_once __DIR__ . '/../config/database.php';

class UserRepository {
  public function findByUsername(string $username): ?array {
    $pdo = Database::conn();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    return $user ?: null;
  }
}