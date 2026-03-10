<?php require_once __DIR__ . '/../../../public/_base.php'; ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Chi tiết chuyến xe</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-3">Chi tiết chuyến xe</h3>

  <?php if (!$trip): ?>
    <div class="alert alert-danger">Không tìm thấy chuyến xe.</div>
  <?php else: ?>
    <?php $remain = max(0, (int)$trip['seat_count'] - (int)$trip['booked_count']); ?>

    <div class="card p-4">
      <div class="row g-3">
        <div class="col-md-6">
          <div><b>Tuyến:</b> <?= htmlspecialchars($trip['from_city']) ?> → <?= htmlspecialchars($trip['to_city']) ?></div>
          <div><b>Giờ chạy:</b> <?= htmlspecialchars($trip['depart_at']) ?></div>
          <div><b>Trạng thái:</b> <?= htmlspecialchars($trip['status']) ?></div>
        </div>
        <div class="col-md-6">
          <div><b>Biển số:</b> <?= htmlspecialchars($trip['plate_no']) ?></div>
          <div><b>Loại xe:</b> <?= htmlspecialchars($trip['bus_type']) ?></div>
          <div><b>Số ghế:</b> <?= (int)$trip['seat_count'] ?></div>
          <div><b>Còn trống:</b> <?= $remain ?></div>
          <div><b>Giá vé:</b> <?= number_format((int)$trip['base_price']) ?> VNĐ</div>
        </div>
      </div>

      <div class="mt-4">
        <a class="btn btn-primary" href="<?= BASE_URL ?>/seat_select.php?trip_id=<?= (int)$trip['id'] ?>">
          Chọn ghế và đặt vé
        </a>
        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/search.php">Quay lại kết quả</a>
      </div>
    </div>
  <?php endif; ?>
</div>
</body>
</html>