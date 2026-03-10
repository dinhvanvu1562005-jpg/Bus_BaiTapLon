<?php require_once __DIR__ . '/../../../public/_base.php'; ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Chọn ghế và đặt vé</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .seat-grid{
      display:grid;
      grid-template-columns:repeat(4,60px);
      gap:12px;
    }
    .seat{
      width:60px;
      height:60px;
      border-radius:12px;
      border:1px solid #cbd5e1;
      display:flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      font-weight:700;
      background:#fff;
    }
    .seat.booked{
      background:#e2e8f0;
      color:#94a3b8;
      cursor:not-allowed;
    }
    .seat.selected{
      outline:3px solid #2563eb;
      background:#eff6ff;
    }
  </style>
</head>
<body class="bg-light">
<div class="container py-4">
  <h3 class="mb-3">Chọn ghế và đặt vé</h3>

  <?php if (!$trip): ?>
    <div class="alert alert-danger">Không tìm thấy chuyến xe.</div>
  <?php else: ?>
    <div class="row g-4">
      <div class="col-md-5">
        <div class="card p-3">
          <div><b>Tuyến:</b> <?= htmlspecialchars($trip['from_city']) ?> → <?= htmlspecialchars($trip['to_city']) ?></div>
          <div><b>Giờ chạy:</b> <?= htmlspecialchars($trip['depart_at']) ?></div>
          <div><b>Giá vé:</b> <?= number_format((int)$trip['base_price']) ?> VNĐ</div>
          <hr>
          <div class="mb-2"><b>Sơ đồ ghế</b></div>
          <div id="seatGrid" class="seat-grid"></div>
        </div>
      </div>

      <div class="col-md-7">
        <div class="card p-3">
          <form method="post" action="<?= BASE_URL ?>/checkout.php" id="bookingForm">
            <input type="hidden" name="trip_id" value="<?= (int)$trip['id'] ?>">
            <input type="hidden" name="seat_no" id="seatNo">

            <div class="mb-3">
              <label class="form-label">Họ tên khách</label>
              <input class="form-control" name="customer_name" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Số điện thoại</label>
              <input class="form-control" name="customer_phone" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input class="form-control" name="customer_email">
            </div>

            <div class="mb-3">
              <label class="form-label">Phương thức thanh toán</label>
              <select class="form-select" name="payment_method">
                <option value="cash">Tiền mặt</option>
                <option value="banking">Chuyển khoản</option>
                <option value="momo">MoMo (demo)</option>
                <option value="vnpay">VNPay (demo)</option>
              </select>
            </div>

            <button class="btn btn-primary w-100">Xác nhận đặt vé</button>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
  const seatCount = <?= (int)($trip['seat_count'] ?? 0) ?>;
  const bookedSeats = <?= json_encode(array_values($trip['booked_seats'] ?? [])) ?>.map(Number);

  const grid = document.getElementById('seatGrid');
  const seatNoInput = document.getElementById('seatNo');
  let selected = null;

  function renderSeats() {
    if (!grid) return;

    for (let i = 1; i <= seatCount; i++) {
      const div = document.createElement('div');
      div.className = 'seat';
      div.textContent = i;

      if (bookedSeats.includes(i)) {
        div.classList.add('booked');
      } else {
        div.addEventListener('click', () => {
          document.querySelectorAll('.seat.selected').forEach(el => el.classList.remove('selected'));
          div.classList.add('selected');
          selected = i;
          seatNoInput.value = i;
        });
      }

      grid.appendChild(div);
    }
  }

  document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
    if (!seatNoInput.value) {
      e.preventDefault();
      alert('Bạn chưa chọn ghế.');
    }
  });

  renderSeats();
</script>
</body>
</html>