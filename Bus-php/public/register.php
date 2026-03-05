<?php
session_start();

require_once __DIR__ . '/_base.php';
require_once __DIR__ . '/../app/config/database.php';

$error = "";
$success = "";

// giữ lại input khi lỗi
$form = [
  'username'  => '',
  'full_name' => '',
  'phone'     => '',
  'role'      => 'seller', // mặc định
];

function is_valid_username(string $u): bool {
  // 4-20 ký tự: chữ/số/._-
  return (bool)preg_match('/^[a-zA-Z0-9._-]{4,20}$/', $u);
}
function is_valid_phone(string $p): bool {
  // đơn giản: 9-11 số
  return (bool)preg_match('/^\d{9,11}$/', $p);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form['username']  = trim($_POST['username'] ?? "");
  $password          = $_POST['password'] ?? "";
  $form['full_name'] = trim($_POST['full_name'] ?? "");
  $form['phone']     = trim($_POST['phone'] ?? "");
  $form['role']      = $_POST['role'] ?? 'seller';

  // ✅ chặn tự đăng ký admin/dispatcher (để đúng thực tế + an toàn)
  $allowedRoles = ['seller', 'customer'];
  if (!in_array($form['role'], $allowedRoles, true)) {
    $form['role'] = 'seller';
  }

  if ($form['username'] === "" || $password === "" || $form['full_name'] === "" || $form['phone'] === "") {
    $error = "Vui lòng nhập đủ thông tin.";
  } elseif (!is_valid_username($form['username'])) {
    $error = "Username không hợp lệ (4–20 ký tự, chỉ gồm chữ/số và . _ -).";
  } elseif (strlen($password) < 6) {
    $error = "Mật khẩu phải từ 6 ký tự trở lên.";
  } elseif (!is_valid_phone($form['phone'])) {
    $error = "Số điện thoại không hợp lệ (chỉ nhập 9–11 chữ số).";
  } else {
    try {
      $pdo = Database::conn();

      // check trùng username
      $st = $pdo->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
      $st->execute([$form['username']]);
      if ($st->fetch()) {
        $error = "Username đã tồn tại. Vui lòng chọn username khác.";
      } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $st2 = $pdo->prepare("
          INSERT INTO users(username, password_hash, role, full_name, phone, is_active)
          VALUES(?,?,?,?,?,1)
        ");
        $st2->execute([$form['username'], $hash, $form['role'], $form['full_name'], $form['phone']]);

        $success = "Đăng ký thành công! Bạn có thể đăng nhập.";
        // reset form cho đẹp
        $form = ['username'=>'','full_name'=>'','phone'=>'','role'=>'seller'];
      }
    } catch (Throwable $e) {
      $error = "Lỗi hệ thống. Kiểm tra DB / bảng users.";
    }
  }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng ký - Bus System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">

  <style>
    .auth-wrap{min-height:100vh;display:flex;align-items:center;background:linear-gradient(180deg,#f5f7fb 0%, #eef2ff 100%)}
    .auth-card{border:0;border-radius:16px;box-shadow:0 14px 40px rgba(15,23,42,.08)}
    .auth-brand{font-weight:800;letter-spacing:.2px}
    .auth-sub{color:#64748b}
    .pw-toggle{cursor:pointer;user-select:none}
    .hint{color:#64748b;font-size:12px}
  </style>
</head>

<body>
<div class="auth-wrap">
  <div class="container" style="max-width: 560px;">
    <div class="card auth-card">
      <div class="card-body p-4">

        <div class="text-center mb-3">
          <div class="auth-brand fs-4">Bus System</div>
          <div class="auth-sub small">Tạo tài khoản</div>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success py-2 mb-3">
            <?= htmlspecialchars($success) ?>
            <div class="mt-2">
              <a class="btn btn-success btn-sm" href="<?= BASE_URL ?>/login.php">Đi tới đăng nhập</a>
            </div>
          </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Username</label>
              <input class="form-control" name="username" value="<?= htmlspecialchars($form['username']) ?>" required>
              <div class="hint mt-1">4–20 ký tự, chỉ gồm chữ/số và . _ -</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Mật khẩu</label>
              <div class="input-group">
                <input class="form-control" id="password" name="password" type="password" required>
                <span class="input-group-text pw-toggle" id="togglePw" title="Hiện/ẩn mật khẩu">👁</span>
              </div>
              <div class="hint mt-1">Tối thiểu 6 ký tự</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Họ tên</label>
              <input class="form-control" name="full_name" value="<?= htmlspecialchars($form['full_name']) ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">SĐT</label>
              <input class="form-control" name="phone" value="<?= htmlspecialchars($form['phone']) ?>" required>
              <div class="hint mt-1">Chỉ nhập số (9–11 chữ số)</div>
            </div>

            <div class="col-12">
              <label class="form-label">Loại tài khoản</label>
              <select class="form-select" name="role">
                <option value="seller" <?= $form['role']==='seller'?'selected':'' ?>>Nhân viên bán vé (Seller)</option>
                <option value="customer" <?= $form['role']==='customer'?'selected':'' ?>>Khách hàng (Customer)</option>
              </select>
              <div class="hint mt-1">
                Admin/Điều hành sẽ do quản trị tạo trong hệ thống (không tự đăng ký).
              </div>
            </div>
          </div>

          <button class="btn btn-primary w-100 mt-3">Đăng ký</button>

          <div class="d-flex justify-content-between mt-3 small">
            <a class="text-decoration-none" href="<?= BASE_URL ?>/login.php">Đã có tài khoản? Đăng nhập</a>
            <!-- ✅ Fix: về trang chủ đúng -->
            <a class="text-decoration-none" href="<?= BASE_URL ?>/index.php">Về trang chủ</a>
          </div>
        </form>
      </div>
    </div>

    <div class="text-center text-muted small mt-3">
      © <?= date('Y') ?> Bus System
    </div>
  </div>
</div>

<script>
  const toggle = document.getElementById('togglePw');
  const pw = document.getElementById('password');
  if (toggle && pw) {
    toggle.addEventListener('click', () => {
      pw.type = (pw.type === 'password') ? 'text' : 'password';
    });
  }
</script>
</body>
</html>