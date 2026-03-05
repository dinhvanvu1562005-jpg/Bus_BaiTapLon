<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../_base.php';

require_role(['admin','dispatcher']); // dashboard cho admin + điều hành

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/_layout_top.php';

$pdo = Database::conn();

/**
 * GHI CHÚ:
 * - Nếu bảng tickets của bạn có cột status (vd: 'booked','canceled') thì thêm điều kiện status != 'canceled'
 * - Nếu không có cột status thì bỏ điều kiện đó đi.
 */

// 1) Tổng chuyến hôm nay (theo depart_at)
$st = $pdo->query("
  SELECT COUNT(*) AS c
  FROM trips
  WHERE DATE(depart_at) = CURDATE()
");
$todayTrips = (int)($st->fetch()['c'] ?? 0);

// 2) Tổng vé đã bán (tính tất cả vé)
$st = $pdo->query("
  SELECT COUNT(*) AS c
  FROM tickets
  -- WHERE status != 'canceled'   -- bật nếu có cột status
");
$ticketsSold = (int)($st->fetch()['c'] ?? 0);

// 3) Doanh thu (sum price)
$st = $pdo->query("
  SELECT COALESCE(SUM(price),0) AS s
  FROM tickets
  -- WHERE status != 'canceled'   -- bật nếu có cột status
");
$revenue = (int)($st->fetch()['s'] ?? 0);

// 4) Danh sách chuyến sắp tới + tính còn trống
// booked_count = số vé của trip đó
$upcoming = $pdo->query("
  SELECT
    t.id,
    t.depart_at,
    t.status,
    r.from_city,
    r.to_city,
    b.seat_count,
    COALESCE(x.booked_count, 0) AS booked_count
  FROM trips t
  JOIN routes r ON r.id = t.route_id
  JOIN buses  b ON b.id = t.bus_id
  LEFT JOIN (
    SELECT trip_id, COUNT(*) AS booked_count
    FROM tickets
    -- WHERE status != 'canceled'     -- bật nếu có cột status
    GROUP BY trip_id
  ) x ON x.trip_id = t.id
  WHERE t.depart_at >= NOW()
  ORDER BY t.depart_at ASC
  LIMIT 10
")->fetchAll();
?>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card p-3 kpi">
      <div class="label">Tổng chuyến hôm nay</div>
      <div class="value"><?= (int)$todayTrips ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 kpi">
      <div class="label">Tổng vé đã bán</div>
      <div class="value"><?= (int)$ticketsSold ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 kpi">
      <div class="label">Doanh thu</div>
      <div class="value"><?= number_format((int)$revenue) ?></div>
    </div>
  </div>
</div>

<div class="card p-3">
  <div class="fw-semibold mb-2">Chuyến sắp tới</div>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tuyến</th>
          <th>Giờ chạy</th>
          <th>Tổng ghế</th>
          <th>Đã bán</th>
          <th>Còn trống</th>
          <th class="text-end"></th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($upcoming)): ?>
        <tr>
          <td colspan="7" class="text-muted">Chưa có chuyến sắp tới.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($upcoming as $t): 
          $seatCount = (int)$t['seat_count'];
          $booked    = (int)$t['booked_count'];
          $remain    = max(0, $seatCount - $booked);
        ?>
          <tr>
            <td>#<?= (int)$t['id'] ?></td>
            <td><?= htmlspecialchars($t['from_city']) ?> → <?= htmlspecialchars($t['to_city']) ?></td>
            <td><?= htmlspecialchars($t['depart_at']) ?></td>
            <td><?= $seatCount ?></td>
            <td><?= $booked ?></td>
            <td><b><?= $remain ?></b></td>
            <td class="text-end">
              <a class="btn btn-sm btn-primary"
                 href="<?= BASE_URL ?>/admin/ticketing.php?trip_id=<?= (int)$t['id'] ?>">
                 Chọn chuyến
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>