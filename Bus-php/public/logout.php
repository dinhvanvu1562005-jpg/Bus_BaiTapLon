<?php
session_start();
require_once __DIR__ . '/_base.php';

/**
 * ✅ Logout sạch:
 * - Xoá session data
 * - Xoá cookie session
 * - Huỷ session
 */
$_SESSION = [];

if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"] ?? '/',
    $params["domain"] ?? '',
    $params["secure"] ?? false,
    $params["httponly"] ?? true
  );
}

session_destroy();

/**
 * ✅ Chống cache để bấm Back không quay lại trang admin
 */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/**
 * ✅ Redirect thẳng về trang chủ
 */
header("Location: " . BASE_URL . "/index.php");
exit;