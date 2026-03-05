<?php
require_once __DIR__ . '/../_base.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

require_login();

$current = basename($_SERVER['PHP_SELF']); // dashboard.php, buses.php...

function active($file, $current) {
  return $file === $current ? 'active' : '';
}

$role = $_SESSION['role'] ?? '';
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bus System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">

  <aside class="sidebar p-3">
    <div class="fw-bold mb-3 text-white">Bus System</div>

    <nav class="nav flex-column gap-1">

      <!-- Tổng quan: cả 3 role đều được vào -->
      <a class="nav-link text-white <?= active('dashboard.php',$current) ?>"
         href="<?= BASE_URL ?>/admin/dashboard.php">Tổng quan</a>

      <!-- ADMIN: quản lý xe -->
      <?php if ($role === 'admin'): ?>
        <a class="nav-link text-white <?= active('buses.php',$current) ?>"
           href="<?= BASE_URL ?>/admin/buses.php">Quản lý xe</a>
      <?php endif; ?>

      <!-- ADMIN + DISPATCHER: quản lý tuyến/chuyến -->
      <?php if ($role === 'admin' || $role === 'dispatcher'): ?>
        <a class="nav-link text-white <?= active('routes.php',$current) ?>"
           href="<?= BASE_URL ?>/admin/routes.php">Quản lý tuyến</a>

        <a class="nav-link text-white <?= active('trips.php',$current) ?>"
           href="<?= BASE_URL ?>/admin/trips.php">Quản lý chuyến</a>
      <?php endif; ?>

      <!-- ADMIN + SELLER: bán vé + hành khách -->
      <?php if ($role === 'admin' || $role === 'seller'): ?>
        <a class="nav-link text-white <?= active('ticketing.php',$current) ?>"
           href="<?= BASE_URL ?>/admin/ticketing.php">Bán vé / Đặt vé</a>

        <a class="nav-link text-white <?= active('passengers.php',$current) ?>"
           href="<?= BASE_URL ?>/admin/passengers.php">Danh sách hành khách</a>
      <?php endif; ?>

      <!-- ADMIN: báo cáo -->
      <?php if ($role === 'admin'): ?>
        <a class="nav-link text-white <?= active('reports.php',$current) ?>"
           href="<?= BASE_URL ?>/admin/reports.php">Báo cáo thống kê</a>
      <?php endif; ?>

      <a class="nav-link text-white" href="<?= BASE_URL ?>/logout.php">Đăng xuất</a>
    </nav>
  </aside>

  <main class="flex-grow-1">
    <header class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
      <div class="fw-semibold">Hệ thống quản lý xe khách</div>
      <div class="text-muted small">
        Xin chào, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?> (<?= htmlspecialchars($role ?: '-') ?>)
      </div>
    </header>

    <div class="p-4">