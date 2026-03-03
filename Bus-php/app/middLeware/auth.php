<?php
// middleware kiểm tra đăng nhập & phân quyền

session_start();

// load BASE_URL để redirect đúng thư mục project
require_once __DIR__ . '/../../public/_base.php';


/**
 * Kiểm tra đã đăng nhập chưa
 */
function require_login() {

  if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
  }
}


/**
 * Kiểm tra quyền user
 * ví dụ: require_role(['admin'])
 */
function require_role(array $roles) {

  require_login();

  $currentRole = $_SESSION['role'] ?? '';

  if (!in_array($currentRole, $roles, true)) {
    http_response_code(403);
    echo "<h3>403 - Bạn không có quyền truy cập</h3>";
    exit;
  }
}