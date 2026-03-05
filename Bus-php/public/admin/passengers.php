<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../_base.php';

require_role(['admin','seller']);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/services/TicketService.php';
require_once __DIR__ . '/_layout_top.php';

$pdo = Database::conn();
$svc = new TicketService();

$tripId = (int)($_GET['trip_id'] ?? 0);
$message = "";

// hủy vé
if (isset($_GET['cancel'])) {
  $ticketId = (int)$_GET['cancel'];
  $rs = $svc->cancelTicket($ticketId);
  $message = $rs['ok'] ? "Đã hủy vé!" : $rs['message'];
}

// danh sách chuyến để chọn
$trips = $pdo->query("
  SELECT t.id, t.depart_at, t.status,
         r.from_city, r.to_city,
         b.plate_no, b.seat_count
  FROM trips t
  JOIN routes r ON r.id=t.route_id
  JOIN buses b ON b.id=t.bus_id
  ORDER BY t.depart_at DESC
")->fetchAll();

$rows = ($tripId > 0) ? $svc->passengersByTrip($tripId) : [];
?>

<h5 class="mb-3">Danh sách hành khách theo chuyến</h5>

<?php if ($message): ?>
  <div class="alert alert-info"><?=htmlspecialchars($message)?></div>
<?php endif; ?>

<div class="card p-3 mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-10">
      <label class="form-label">Chọn chuyến</label>
      <select class="form-select" name="trip_id" onchange="this.form.submit()">
        <option value="0">-- Chọn chuyến --</option>
        <?php foreach ($trips as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= ($tripId===(int)$t['id'])?'selected':'' ?>>
            #<?= (int)$t['id'] ?> | <?=htmlspecialchars($t['from_city'])?> → <?=htmlspecialchars($t['to_city'])?>
            | <?=htmlspecialchars($t['depart_at'])?> | <?=htmlspecialchars($t['plate_no'])?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <a class="btn btn-outline-secondary w-100" href="<?=BASE_URL?>/admin/passengers.php">Reset</a>
    </div>
  </form>
</div>

<?php if ($tripId > 0): ?>
  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Ghế</th>
            <th>Khách</th>
            <th>SĐT</th>
            <th>Mã vé</th>
            <th>Trạng thái</th>
            <th class="text-end">Hành động</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><b><?= (int)$r['seat_no'] ?></b></td>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= htmlspecialchars($r['phone']) ?></td>
            <td><?= htmlspecialchars($r['ticket_code']) ?></td>
            <td>
              <?php if ($r['status'] === 'booked'): ?>
                <span class="badge text-bg-success">booked</span>
              <?php else: ?>
                <span class="badge text-bg-secondary">canceled</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary"
                 href="<?=BASE_URL?>/admin/ticket_print.php?id=<?=(int)$r['ticket_id']?>"
                 target="_blank">
                In vé
              </a>

              <?php if ($r['status'] === 'booked'): ?>
                <a class="btn btn-sm btn-outline-danger"
                   href="<?=BASE_URL?>/admin/passengers.php?trip_id=<?=$tripId?>&cancel=<?=(int)$r['ticket_id']?>"
                   onclick="return confirm('Hủy vé này?');">
                  Hủy vé
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (count($rows) === 0): ?>
          <tr><td colspan="6" class="text-muted">Chuyến này chưa có vé.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="text-muted">Hãy chọn 1 chuyến để xem danh sách hành khách.</div>
<?php endif; ?>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>