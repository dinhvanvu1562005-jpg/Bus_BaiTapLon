<?php
session_start();

require_once __DIR__ . '/_base.php';
require_once __DIR__ . '/../app/services/AuthService.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $username = trim($_POST['username'] ?? "");
  $password = $_POST['password'] ?? "";

  // gọi tầng Service (đúng kiến trúc)
  $auth = new AuthService();
  $result = $auth->login($username, $password);

  if ($result["ok"]) {

    $user = $result["user"];

    // lưu session
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"] = $user["role"];

    // chuyển dashboard
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;

  } else {
    $error = $result["message"];
  }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container" style="max-width:420px;margin-top:100px;">
  <div class="card p-4 shadow-sm">

    <h4 class="mb-3 text-center">Đăng nhập hệ thống</h4>

    <!-- HIỂN THỊ LỖI -->
    <?php if ($error): ?>
      <div class="alert alert-danger py-2">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post">

      <div class="mb-2">
        <label class="form-label">Username</label>
        <input class="form-control" name="username" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input class="form-control" name="password" type="password" required>
      </div>

      <button class="btn btn-primary w-100">
        Đăng nhập
      </button>

    </form>

  </div>
</div>

</body>
</html>