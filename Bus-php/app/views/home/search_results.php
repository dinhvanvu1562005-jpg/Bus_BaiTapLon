<?php require_once __DIR__ . '/../../../public/_base.php'; ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Kết quả tra cứu chuyến xe</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-3">Kết quả tra cứu chuyến xe</h3>

  <form class="row g-2 mb-4" method="get" action="<?= BASE_URL ?>/search.php">
    <div class="col-md-4">
      <input class="form-control" name="from_city" placeholder="Điểm đi" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-md-4">
      <input class="form-control" name="to_city" placeholder="Điểm đến" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-md-2">
      <input class="form-control" type="date" name="depart_date" value="<?= htmlspecialchars($date) ?>">
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-primary">Tìm chuyến</button>
    </div>
  </form>

  <div class="card p-3">
    <?php if (empty($trips)): ?>
      <div class="text-muted">Không tìm thấy chuyến phù hợp.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tuyến</th>
              <th>Giờ chạy</th>
              <th>Xe</th>
              <th>Loại xe</th>
              <th>Giá</th>
              <th>Còn trống</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($trips as $t): 
            $remain = max(0, (int)$t['seat_count'] - (int)$t['booked_count']);
          ?>
            <tr>
              <td>#<?= (int)$t['id'] ?></td>
              <td><?= htmlspecialchars($t['from_city']) ?> → <?= htmlspecialchars($t['to_city']) ?></td>
              <td><?= htmlspecialchars($t['depart_at']) ?></td>
              <td><?= htmlspecialchars($t['plate_no']) ?></td>
              <td><?= htmlspecialchars($t['bus_type']) ?></td>
              <td><?= number_format((int)$t['base_price']) ?> VNĐ</td>
              <td><?= $remain ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="<?= BASE_URL ?>/trip.php?trip_id=<?= (int)$t['id'] ?>">
                  Xem chi tiết
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="mt-3">
    <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary">Về trang chủ</a>
  </div>
</div>
</body>
</html>