<?php
// app/middleware/auth.php

// ✅ đảm bảo session dùng chung cho /public và /public/admin
if (session_status() === PHP_SESSION_NONE) {
  $cookiePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
  session_set_cookie_params(['path' => $cookiePath]);
  session_start();
}

// Luôn include _base.php từ public (đường dẫn tuyệt đối)
require_once dirname(__DIR__, 2) . '/public/_base.php';

function require_login(): void {
  if (empty($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php?open=login");
    exit;
  }
}

function require_role(array $roles): void {
  require_login();

  $currentRole = $_SESSION['role'] ?? '';
  if (!in_array($currentRole, $roles, true)) {
    http_response_code(403);
    echo "<h3 style='font-family:Arial'>403 - Bạn không có quyền truy cập</h3>";
    echo "<div style='font-family:Arial;color:#555'>Role hiện tại: <b>" . htmlspecialchars($currentRole) . "</b></div>";
    echo "<div style='font-family:Arial;color:#555'>Cho phép: <b>" . htmlspecialchars(implode(', ', $roles)) . "</b></div>";
    exit;
  }
}