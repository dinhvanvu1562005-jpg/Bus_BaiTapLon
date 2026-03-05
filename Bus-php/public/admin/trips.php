<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../_base.php';

require_role(['admin','dispatcher']);

require_once __DIR__ . '/../../app/services/TripService.php';
require_once __DIR__ . '/../../app/services/RouteService.php';
require_once __DIR__ . '/../../app/services/BusService.php';
require_once __DIR__ . '/_layout_top.php';

$tripSvc  = new TripService();
$routeSvc = new RouteService();
$busSvc   = new BusService();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $rs = $tripSvc->create($_POST);
    if (!$rs['ok']) $error = $rs['message'];
    header("Location: " . BASE_URL . "/admin/trips.php");
    exit;
  }

  if ($action === 'status') {
    $tripSvc->updateStatus($_POST);
    header("Location: " . BASE_URL . "/admin/trips.php");
    exit;
  }
}

if (isset($_GET['delete'])) {
  $tripSvc->delete((int)$_GET['delete']);
  header("Location: " . BASE_URL . "/admin/trips.php");
  exit;
}

$trips  = $tripSvc->list();
$routes = $routeSvc->list();
$buses  = $busSvc->list();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Quản lý chuyến / lịch chạy</h5>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
<?php endif; ?>

<div class="card p-3 mb-3">
  <form method="post" class="row g-2">
    <input type="hidden" name="action" value="create">

    <div class="col-md-4">
      <label class="form-label">Tuyến</label>
      <select class="form-select" name="route_id" required>
        <option value="">-- Chọn tuyến --</option>
        <?php foreach ($routes as $r): ?>
          <option value="<?= (int)$r['id'] ?>">
            <?= htmlspecialchars($r['from_city']) ?> → <?= htmlspecialchars($r['to_city']) ?> (<?= number_format((int)$r['base_price']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">Xe</label>
      <select class="form-select" name="bus_id" required>
        <option value="">-- Chọn xe --</option>
        <?php foreach ($buses as $b): ?>
          <option value="<?= (int)$b['id'] ?>">
            <?= htmlspecialchars($b['plate_no']) ?> - <?= htmlspecialchars($b['bus_type']) ?> (<?= (int)$b['seat_count'] ?> ghế)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Giờ xuất bến</label>
      <input class="form-control" type="datetime-local" name="depart_at" required>
    </div>

    <div class="col-md-1 d-flex align-items-end">
      <button class="btn btn-primary w-100">Tạo</button>
    </div>
  </form>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tuyến</th>
          <th>Xe</th>
          <th>Giờ chạy</th>
          <th>Trạng thái</th>
          <th class="text-end">Hành động</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($trips as $t): ?>
        <tr>
          <td><?= (int)$t['id'] ?></td>
          <td><?= htmlspecialchars($t['from_city']) ?> → <?= htmlspecialchars($t['to_city']) ?></td>
          <td><?= htmlspecialchars($t['plate_no']) ?> (<?= (int)$t['seat_count'] ?> ghế)</td>
          <td><?= htmlspecialchars($t['depart_at']) ?></td>
          <td>
            <form method="post" class="d-inline">
              <input type="hidden" name="action" value="status">
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php
                  $st = $t['status'];
                  $opts = ['scheduled'=>'Scheduled','departed'=>'Departed','done'=>'Done','canceled'=>'Canceled'];
                  foreach ($opts as $k=>$v) {
                    $sel = ($st===$k) ? 'selected' : '';
                    echo "<option value=\"$k\" $sel>$v</option>";
                  }
                ?>
              </select>
            </form>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-danger"
               href="<?=BASE_URL?>/admin/trips.php?delete=<?=(int)$t['id']?>"
               onclick="return confirm('Xóa chuyến này?')">
              Xóa
            </a>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (count($trips) === 0): ?>
        <tr><td colspan="6" class="text-muted">Chưa có chuyến.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>