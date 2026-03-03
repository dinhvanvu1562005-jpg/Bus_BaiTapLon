<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../_base.php';

require_login();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bus System</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- CSS project -->
  <link href="<?=BASE_URL?>/assets/style.css" rel="stylesheet">
</head>

<body>
<div class="d-flex">

  <!-- SIDEBAR -->
  <aside class="sidebar p-3">
    <div class="fw-bold mb-3 text-white">Bus System</div>

    <nav class="nav flex-column gap-1">
      <a class="nav-link text-white"
         href="<?=BASE_URL?>/admin/dashboard.php">
         Tổng quan
      </a>

      <a class="nav-link text-white"
         href="<?=BASE_URL?>/admin/ticketing.php">
         Bán vé / Đặt vé
      </a>

      <a class="nav-link text-white"
         href="<?=BASE_URL?>/admin/reports.php">
         Báo cáo thống kê
      </a>

      <a class="nav-link text-white"
         href="<?=BASE_URL?>/logout.php">
         Đăng xuất
      </a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="flex-grow-1">

    <!-- TOPBAR -->
    <header class="topbar px-4 py-3 d-flex justify-content-between align-items-center">
      <div class="fw-semibold">Hệ thống quản lý xe khách</div>

      <div class="text-muted small">
        Xin chào,
        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
      </div>
    </header>

    <div class="p-4">