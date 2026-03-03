<?php
require_once __DIR__ . '/../repositories/UserRepository.php';

class AuthService {
  private UserRepository $repo;

  public function __construct() {
    $this->repo = new UserRepository();
  }

  public function login(string $username, string $password): array {
    $user = $this->repo->findByUsername($username);
    if (!$user) {
      return ["ok" => false, "message" => "Sai tài khoản hoặc mật khẩu"];
    }

    if (!password_verify($password, $user["password_hash"])) {
      return ["ok" => false, "message" => "Sai tài khoản hoặc mật khẩu"];
    }

    return ["ok" => true, "user" => $user];
  }
}