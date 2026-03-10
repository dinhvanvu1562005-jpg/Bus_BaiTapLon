<?php require_once __DIR__ . '/../../../public/_base.php'; ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Kết quả đặt vé</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card p-4">
    <h3 class="mb-3">Kết quả đặt vé</h3>

    <?php if (!empty($result['ok'])): ?>
      <div class="alert alert-success">
        Đặt vé thành công.
      </div>

      <div class="mb-2"><b>Mã đặt chỗ:</b> <?= htmlspecialchars($result['booking_code']) ?></div>
      <div class="mb-2"><b>Mã vé:</b> <?= htmlspecialchars($result['ticket_code']) ?></div>
      <div class="mb-2"><b>Số tiền:</b> <?= number_format((int)$result['price']) ?> VNĐ</div>

      <div class="mt-3">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">Về trang chủ</a>
      </div>
    <?php else: ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($result['message'] ?? 'Đặt vé thất bại.') ?>
      </div>

      <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary">Quay lại</a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>