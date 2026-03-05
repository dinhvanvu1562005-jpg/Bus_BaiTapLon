<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../_base.php';

require_role(['admin']);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/_layout_top.php';

$pdo = Database::conn();

// filter ngày
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');

$fromDT = $from . " 00:00:00";
$toDT   = $to . " 23:59:59";

// 1) Tổng vé + doanh thu theo ngày
$st = $pdo->prepare("
  SELECT DATE(tk.created_at) AS d,
         COUNT(*) AS tickets,
         SUM(tk.price) AS revenue
  FROM tickets tk
  WHERE tk.status='booked'
    AND tk.created_at BETWEEN ? AND ?
  GROUP BY DATE(tk.created_at)
  ORDER BY d ASC
");
$st->execute([$fromDT, $toDT]);
$daily = $st->fetchAll();

// 2) Tổng theo chuyến
$st2 = $pdo->prepare("
  SELECT t.id AS trip_id, t.depart_at,
         r.from_city, r.to_city,
         b.plate_no,
         COUNT(tk.id) AS tickets,
         SUM(tk.price) AS revenue
  FROM trips t
  JOIN routes r ON r.id=t.route_id
  JOIN buses b ON b.id=t.bus_id
  LEFT JOIN tickets tk ON tk.trip_id=t.id
       AND tk.status='booked'
       AND tk.created_at BETWEEN ? AND ?
  WHERE t.depart_at BETWEEN ? AND ?
  GROUP BY t.id
  ORDER BY t.depart_at ASC
");
$st2->execute([$fromDT, $toDT, $fromDT, $toDT]);
$byTrip = $st2->fetchAll();

// 3) Top tuyến bán chạy
$st3 = $pdo->prepare("
  SELECT r.from_city, r.to_city,
         COUNT(tk.id) AS tickets,
         SUM(tk.price) AS revenue
  FROM tickets tk
  JOIN trips t ON t.id=tk.trip_id
  JOIN routes r ON r.id=t.route_id
  WHERE tk.status='booked'
    AND tk.created_at BETWEEN ? AND ?
  GROUP BY r.id
  ORDER BY tickets DESC
  LIMIT 10
");
$st3->execute([$fromDT, $toDT]);
$topRoutes = $st3->fetchAll();

// tổng cộng
$totalTickets = 0;
$totalRevenue = 0;
foreach ($daily as $row) {
  $totalTickets += (int)$row['tickets'];
  $totalRevenue += (int)$row['revenue'];
}
?>

<h5 class="mb-3">Báo cáo thống kê</h5>

<div class="card p-3 mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label">Từ ngày</label>
      <input class="form-control" type="date" name="from" value="<?=htmlspecialchars($from)?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Đến ngày</label>
      <input class="form-control" type="date" name="to" value="<?=htmlspecialchars($to)?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Lọc</button>
    </div>
  </form>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card p-3">
      <div class="text-muted">Tổng vé đã bán</div>
      <div class="fs-3 fw-bold"><?=number_format($totalTickets)?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3">
      <div class="text-muted">Doanh thu</div>
      <div class="fs-3 fw-bold"><?=number_format($totalRevenue)?> VNĐ</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3">
      <div class="text-muted">Khoảng thời gian</div>
      <div class="fw-semibold"><?=htmlspecialchars($from)?> → <?=htmlspecialchars($to)?></div>
    </div>
  </div>
</div>

<div class="card p-3 mb-3">
  <div class="fw-semibold mb-2">1) Vé & doanh thu theo ngày</div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead>
        <tr>
          <th>Ngày</th>
          <th>Số vé</th>
          <th>Doanh thu</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($daily as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['d'])?></td>
          <td><?=number_format((int)$r['tickets'])?></td>
          <td><?=number_format((int)$r['revenue'])?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (count($daily)===0): ?>
        <tr><td colspan="3" class="text-muted">Chưa có dữ liệu trong khoảng thời gian này.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card p-3 mb-3">
  <div class="fw-semibold mb-2">2) Tổng hợp theo chuyến</div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead>
        <tr>
          <th>Chuyến</th>
          <th>Giờ chạy</th>
          <th>Xe</th>
          <th>Số vé</th>
          <th>Doanh thu</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($byTrip as $r): ?>
        <tr>
          <td>#<?= (int)$r['trip_id'] ?> | <?=htmlspecialchars($r['from_city'])?> → <?=htmlspecialchars($r['to_city'])?></td>
          <td><?=htmlspecialchars($r['depart_at'])?></td>
          <td><?=htmlspecialchars($r['plate_no'])?></td>
          <td><?=number_format((int)$r['tickets'])?></td>
          <td><?=number_format((int)$r['revenue'])?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (count($byTrip)===0): ?>
        <tr><td colspan="5" class="text-muted">Chưa có chuyến trong khoảng thời gian này.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card p-3">
  <div class="fw-semibold mb-2">3) Top tuyến bán chạy</div>
  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead>
        <tr>
          <th>Tuyến</th>
          <th>Số vé</th>
          <th>Doanh thu</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($topRoutes as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['from_city'])?> → <?=htmlspecialchars($r['to_city'])?></td>
          <td><?=number_format((int)$r['tickets'])?></td>
          <td><?=number_format((int)$r['revenue'])?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (count($topRoutes)===0): ?>
        <tr><td colspan="3" class="text-muted">Chưa có dữ liệu.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>