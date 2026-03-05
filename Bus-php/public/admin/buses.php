<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../_base.php';

require_role(['admin']);

require_once __DIR__ . '/../../app/services/BusService.php';
require_once __DIR__ . '/_layout_top.php';

$svc = new BusService();

// HANDLE POST (create/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'create') {
    $svc->create($_POST);
  }

  if ($action === 'update') {
    $svc->update($_POST);
  }

  header("Location: " . BASE_URL . "/admin/buses.php");
  exit;
}

// HANDLE delete
if (isset($_GET['delete'])) {
  $svc->delete((int)$_GET['delete']);
  header("Location: " . BASE_URL . "/admin/buses.php");
  exit;
}

$buses = $svc->list();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Quản lý xe</h5>
</div>

<div class="card p-3 mb-3">
  <form method="post" class="row g-2">
    <input type="hidden" name="action" value="create">

    <div class="col-md-3">
      <input class="form-control" name="plate_no" placeholder="Biển số (VD: 79A-12345)" required>
    </div>
    <div class="col-md-3">
      <input class="form-control" name="bus_type" placeholder="Loại xe (Giường nằm/Ghế ngồi)" required>
    </div>
    <div class="col-md-2">
      <input class="form-control" name="seat_count" type="number" min="1" placeholder="Số ghế" required>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Thêm xe</button>
    </div>
  </form>
</div>

<div class="card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Biển số</th>
          <th>Loại xe</th>
          <th>Số ghế</th>
          <th>Trạng thái</th>
          <th class="text-end">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($buses as $b): ?>
          <tr>
            <td><?= (int)$b['id'] ?></td>
            <td><?= htmlspecialchars($b['plate_no']) ?></td>
            <td><?= htmlspecialchars($b['bus_type']) ?></td>
            <td><?= (int)$b['seat_count'] ?></td>
            <td>
              <?= ((int)$b['is_active'] === 1)
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Off</span>' ?>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-danger"
                 href="<?=BASE_URL?>/admin/buses.php?delete=<?=(int)$b['id']?>"
                 onclick="return confirm('Xóa xe này?')">
                 Xóa
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (count($buses) === 0): ?>
          <tr><td colspan="6" class="text-muted">Chưa có xe.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>