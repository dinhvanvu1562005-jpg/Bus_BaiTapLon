<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../_base.php';

require_role(['admin','dispatcher']);

require_once __DIR__ . '/../../app/services/RouteService.php';
require_once __DIR__ . '/_layout_top.php';

$svc = new RouteService();
$error = "";

// Thêm tuyến
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rs = $svc->create($_POST);
  if (!$rs['ok']) $error = $rs['message'];
  header("Location: " . BASE_URL . "/admin/routes.php");
  exit;
}

// Xóa tuyến
if (isset($_GET['delete'])) {
  $svc->delete((int)$_GET['delete']);
  header("Location: " . BASE_URL . "/admin/routes.php");
  exit;
}

$routes = $svc->list();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Quản lý tuyến xe</h5>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
<?php endif; ?>

<div class="card p-3 mb-3">
  <form method="post" class="row g-2">
    <div class="col-md-3">
      <input class="form-control" name="from_city" placeholder="Điểm đi (VD: HCM)" required>
    </div>
    <div class="col-md-3">
      <input class="form-control" name="to_city" placeholder="Điểm đến (VD: Nha Trang)" required>
    </div>
    <div class="col-md-2">
      <input class="form-control" name="depart_time" type="time">
    </div>
    <div class="col-md-2">
      <input class="form-control" name="base_price" type="number" min="0" placeholder="Giá vé" value="0" required>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Thêm tuyến</button>
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
          <th>Giờ chạy</th>
          <th>Giá vé</th>
          <th class="text-end">Hành động</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($routes as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= htmlspecialchars($r['from_city']) ?> → <?= htmlspecialchars($r['to_city']) ?></td>
          <td><?= htmlspecialchars($r['depart_time'] ?? '-') ?></td>
          <td><?= number_format((int)$r['base_price']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-danger"
               href="<?=BASE_URL?>/admin/routes.php?delete=<?=(int)$r['id']?>"
               onclick="return confirm('Xóa tuyến này?')">
              Xóa
            </a>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (count($routes) === 0): ?>
        <tr><td colspan="5" class="text-muted">Chưa có tuyến.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>