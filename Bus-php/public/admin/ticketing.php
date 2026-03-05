<?php
require_once __DIR__ . '/../_base.php';
require_once __DIR__ . '/../../app/middleware/auth.php';

require_login();
require_role(['admin','seller']);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/services/TicketService.php';
require_once __DIR__ . '/_layout_top.php';

$ticketSvc = new TicketService();
$pdo = Database::conn();

$tripId   = (int)($_GET['trip_id'] ?? 0);
$success  = "";
$error    = "";

/** Lấy danh sách chuyến */
$trips = $pdo->query("
  SELECT t.id, t.depart_at, t.status,
         r.from_city, r.to_city, r.base_price,
         b.plate_no, b.seat_count
  FROM trips t
  JOIN routes r ON r.id=t.route_id
  JOIN buses b  ON b.id=t.bus_id
  ORDER BY t.depart_at ASC
")->fetchAll();

/** Nếu đã chọn trip => load số ghế + ghế đã bán */
$seatCount   = 0;
$bookedSeats = [];

if ($tripId > 0) {
  $st = $pdo->prepare("
    SELECT b.seat_count
    FROM trips t
    JOIN buses b ON b.id=t.bus_id
    WHERE t.id=?
    LIMIT 1
  ");
  $st->execute([$tripId]);
  $seatCount = (int)($st->fetch()['seat_count'] ?? 0);

  $bookedSeats = $ticketSvc->getBookedSeats($tripId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rs = $ticketSvc->book($_POST);

  if ($rs['ok']) {
    $success = "Bán vé thành công! Mã vé: {$rs['ticket_code']} - Giá: " . number_format((int)$rs['price']);

    // reload lại trip/seat sau khi bán vé
    $tripId = (int)($_POST['trip_id'] ?? 0);

    if ($tripId > 0) {
      $st = $pdo->prepare("
        SELECT b.seat_count
        FROM trips t
        JOIN buses b ON b.id=t.bus_id
        WHERE t.id=?
        LIMIT 1
      ");
      $st->execute([$tripId]);
      $seatCount = (int)($st->fetch()['seat_count'] ?? 0);
    }

    $bookedSeats = $ticketSvc->getBookedSeats($tripId);
  } else {
    $error = $rs['message'] ?? "Có lỗi xảy ra.";
  }
}
?>

<style>
  .seat-grid{display:grid;grid-template-columns:repeat(4,56px);gap:10px}
  .seat{
    width:56px;height:56px;border-radius:12px;border:1px solid #cbd5e1;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;user-select:none;font-weight:700;background:#fff;
    transition:transform .08s ease, box-shadow .08s ease;
  }
  .seat:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(15,23,42,.08)}
  .seat.booked{background:#f1f5f9;color:#94a3b8;cursor:not-allowed;box-shadow:none}
  .seat.selected{outline:3px solid #2563eb}
  .legend span{display:inline-flex;align-items:center;gap:6px;margin-right:12px}
  .dot{width:14px;height:14px;border-radius:6px;border:1px solid #cbd5e1;display:inline-block;background:#fff}
  .dot.booked{background:#f1f5f9}
  .dot.selected{outline:3px solid #2563eb}
</style>

<h5 class="mb-3">Bán vé / Đặt vé</h5>

<?php if ($success): ?>
  <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <form method="get" class="row g-2 align-items-end">
        <div class="col-12">
          <label class="form-label">Chọn chuyến</label>
          <select class="form-select" name="trip_id" onchange="this.form.submit()">
            <option value="0">-- Chọn chuyến --</option>
            <?php foreach ($trips as $t): ?>
              <option value="<?= (int)$t['id'] ?>" <?= ($tripId===(int)$t['id'])?'selected':'' ?>>
                #<?= (int)$t['id'] ?> | <?= htmlspecialchars($t['from_city']) ?> → <?= htmlspecialchars($t['to_city']) ?>
                | <?= htmlspecialchars($t['depart_at']) ?> | <?= htmlspecialchars($t['plate_no']) ?> | <?= (int)$t['seat_count'] ?> ghế
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>

      <hr>

      <?php if ($tripId > 0): ?>
        <div class="legend mb-2">
          <span><i class="dot"></i> Trống</span>
          <span><i class="dot booked"></i> Đã bán</span>
          <span><i class="dot selected"></i> Đang chọn</span>
        </div>

        <div id="seatGrid" class="seat-grid mb-3"></div>

        <form method="post" id="ticketForm">
          <input type="hidden" name="trip_id" value="<?= (int)$tripId ?>">
          <input type="hidden" name="seat_no" id="seatNo">

          <div class="mb-2">
            <label class="form-label">Họ tên khách</label>
            <input class="form-control" name="full_name" required>
          </div>

          <div class="mb-3">
            <label class="form-label">SĐT</label>
            <input class="form-control" name="phone" required>
          </div>

          <button class="btn btn-primary w-100" type="submit">
            Xác nhận bán vé
          </button>
        </form>
      <?php else: ?>
        <div class="text-muted">Hãy chọn 1 chuyến để hiển thị sơ đồ ghế.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card p-3">
      <div class="fw-semibold mb-2">Gợi ý</div>
      <div class="text-muted">
        Đã có chọn ghế bằng JS + lưu vé vào DB (tickets). Tiếp theo: in vé, hủy vé, danh sách hành khách theo chuyến.
      </div>
    </div>
  </div>
</div>

<script>
  const seatCount = <?= (int)$seatCount ?>;

  // ép kiểu về number để includes(i) luôn đúng
  const bookedSeats = (<?= json_encode(array_values($bookedSeats)) ?> || []).map(n => Number(n));

  const seatGrid = document.getElementById('seatGrid');
  const seatNoInput = document.getElementById('seatNo');

  let selected = null;

  function renderSeats() {
    if (!seatGrid) return;

    seatGrid.innerHTML = '';
    for (let i = 1; i <= seatCount; i++) {
      const div = document.createElement('div');
      div.className = 'seat';
      div.textContent = i;

      if (bookedSeats.includes(i)) {
        div.classList.add('booked');
      } else {
        div.addEventListener('click', () => {
          if (selected) selected.classList.remove('selected');
          selected = div;
          div.classList.add('selected');
          seatNoInput.value = i;
        });
      }

      seatGrid.appendChild(div);
    }
  }

  // chặn submit nếu chưa chọn ghế
  const form = document.getElementById('ticketForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      if (!seatNoInput.value) {
        e.preventDefault();
        alert('Bạn chưa chọn ghế!');
      }
    });
  }

  renderSeats();
</script>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>