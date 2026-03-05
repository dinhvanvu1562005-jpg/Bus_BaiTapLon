<?php
session_start();
require_once __DIR__ . '/../_base.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/index.php?open=login");
  exit;
}

$role = $_SESSION['role'];

if ($role === 'admin') {
  header("Location: " . BASE_URL . "/admin/dashboard.php"); exit;
}
if ($role === 'dispatcher') {
  header("Location: " . BASE_URL . "/admin/trips.php"); exit;
}
if ($role === 'seller') {
  header("Location: " . BASE_URL . "/admin/ticketing.php"); exit;
}

header("Location: " . BASE_URL . "/logout.php");
exit;