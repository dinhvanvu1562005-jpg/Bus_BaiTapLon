<?php
class Database {
  private static ?PDO $conn = null;

  public static function conn(): PDO {
    if (self::$conn !== null) return self::$conn;

    $host = "localhost";
    $db   = "bus_system";
    $user = "root";
    $pass = ""; // nếu bạn có mật khẩu MySQL thì điền vào đây

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    self::$conn = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return self::$conn;
  }
}