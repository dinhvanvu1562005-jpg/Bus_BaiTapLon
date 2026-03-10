<?php
// ✅ để session dùng được cho /public và /public/admin
if (session_status() === PHP_SESSION_NONE) {
  $cookiePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
  session_set_cookie_params(['path' => $cookiePath]);
  session_start();
}

require_once __DIR__ . '/_base.php';
require_once __DIR__ . '/../app/services/AuthService.php';

$error = "";

// ✅ Nếu đã đăng nhập -> vào cổng chung admin/index.php
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
  header("Location: " . BASE_URL . "/admin/index.php");
  exit;
}

// open modal
$open = $_GET['open'] ?? ''; // login | register

// ✅ Xử lý đăng nhập NGAY TẠI INDEX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['__form'] ?? '') === 'login') {
  $username = trim($_POST['username'] ?? "");
  $password = $_POST['password'] ?? "";

  $auth = new AuthService();
  $result = $auth->login($username, $password);

  if (!empty($result['ok'])) {
    $user = $result['user'];

    $_SESSION["user_id"]  = $user["id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"]     = $user["role"];

    header("Location: " . BASE_URL . "/admin/index.php");
    exit;
  } else {
    $error = $result["message"] ?? "Sai tài khoản hoặc mật khẩu.";
    $open = 'login';
  }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bus System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">

  <style>
    /* ===== Hero kiểu RedBus ===== */
    .hero {
      min-height: 100vh;
      position: relative;
      color: #fff;
      background:
        linear-gradient(180deg, rgba(0,0,0,.55), rgba(0,0,0,.35)),
        url("https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=1800&q=80");
      background-size: cover;
      background-position: center;
      transition: background-position .15s ease-out;
      isolation: isolate;
      overflow: hidden;
    }

    .hero-nav {
      height: 64px;
      background: rgba(220, 38, 38, .92);
      backdrop-filter: blur(6px);
    }

    .brand {
      font-weight: 800;
      letter-spacing: .2px;
    }

    .search-card {
      background: rgba(255,255,255,.98);
      border-radius: 18px;
      box-shadow: 0 20px 60px rgba(0,0,0,.25);
      overflow: hidden;
    }

    .search-card .form-control,
    .search-card .form-select {
      height: 54px;
      border-radius: 12px;
    }

    .btn-search {
      height: 54px;
      border-radius: 12px;
      font-weight: 700;
      background: #dc2626;
      border: 0;
      position: relative;
      overflow: hidden;
    }

    .btn-search:hover {
      background:#b91c1c;
    }

    /* ===== Modal auth ===== */
    .auth-modal .modal-content{
      border:0;
      border-radius: 16px;
      overflow:hidden;
      box-shadow: 0 30px 80px rgba(2,6,23,.25);
    }

    .auth-left{
      background: linear-gradient(180deg,#ef4444,#b91c1c);
      color:#fff;
      padding: 28px;
      min-height: 420px;
      position: relative;
    }

    .auth-left:after{
      content:"";
      position:absolute;
      inset:-40px -60px auto auto;
      width:220px;
      height:220px;
      background: rgba(255,255,255,.14);
      border-radius: 40px;
      transform: rotate(18deg);
    }

    .auth-right{
      padding: 26px;
      background:#fff;
    }

    .auth-title{
      font-weight: 900;
      letter-spacing:.2px;
    }

    .auth-sub{
      color:#64748b;
      font-size: 13px;
    }

    .auth-right .form-control{
      height: 48px;
      border-radius: 12px;
    }

    .auth-btn{
      height: 48px;
      border-radius: 12px;
      font-weight: 800;
      background:#2563eb;
      border:0;
    }

    .auth-btn:hover{
      background:#1d4ed8;
    }

    .auth-link{
      color:#2563eb;
      text-decoration:none;
      font-weight:600;
    }

    .auth-link:hover{
      text-decoration:underline;
    }

    /* ===== Animation + Pro ===== */
    html { scroll-behavior: smooth; }
    body { overflow-x: hidden; }

    .hero::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(900px 500px at 20% 20%, rgba(255,255,255,.12), transparent 60%),
        radial-gradient(700px 420px at 80% 30%, rgba(255,255,255,.10), transparent 65%);
      mix-blend-mode: overlay;
      pointer-events:none;
      opacity:.85;
      animation: floatGlow 8s ease-in-out infinite;
      z-index: 0;
    }

    @keyframes floatGlow{
      0%,100%{ transform: translate3d(0,0,0) scale(1); }
      50%{ transform: translate3d(0,-10px,0) scale(1.02); }
    }

    .hero .container,
    .hero-nav {
      position: relative;
      z-index: 1;
    }

    .hero h1,
    .hero .text-white-75,
    .search-card{
      opacity: 0;
      transform: translateY(16px);
      transition: opacity .7s ease, transform .7s ease;
      will-change: opacity, transform;
    }

    .hero.is-ready h1{
      transition-delay: .05s;
      opacity:1;
      transform: translateY(0);
    }

    .hero.is-ready .text-white-75{
      transition-delay: .15s;
      opacity:1;
      transform: translateY(0);
    }

    .hero.is-ready .search-card{
      transition-delay: .25s;
      opacity:1;
      transform: translateY(0);
    }

    .hero-nav{
      transform: translateY(-10px);
      opacity: 0;
      transition: transform .6s ease, opacity .6s ease;
    }

    .hero.is-ready .hero-nav{
      transform: translateY(0);
      opacity: 1;
    }

    .search-card{
      transform-origin: center;
      transition: transform .25s ease, box-shadow .25s ease, opacity .7s ease;
    }

    .search-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 26px 80px rgba(0,0,0,.32);
    }

    .search-card .form-control:focus{
      box-shadow: 0 0 0 .25rem rgba(37,99,235,.18);
      border-color: rgba(37,99,235,.45);
    }

    .top-loader{
      position: fixed;
      left:0;
      top:0;
      height: 3px;
      width: 0%;
      background: linear-gradient(90deg, #60a5fa, #ef4444);
      z-index: 9999;
      transition: width .25s ease;
    }
  </style>
</head>

<body>
  <section class="hero d-flex flex-column">
    <!-- Top nav -->
    <div class="hero-nav d-flex align-items-center">
      <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">🚌 Bus System</div>
        <div class="d-flex gap-2">
          <a class="btn btn-light btn-sm fw-semibold" href="<?= BASE_URL ?>/index.php?open=login">Đăng nhập</a>
          <a class="btn btn-outline-light btn-sm fw-semibold" href="<?= BASE_URL ?>/index.php?open=register">Đăng ký</a>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="container flex-grow-1 d-flex align-items-center">
      <div class="w-100">
        <h1 class="fw-bold display-6 mb-2">Tìm vé xe & quản lý chuyến nhanh gọn</h1>
        <div class="text-white-75 mb-4">Tra cứu tuyến – lịch chạy – đặt vé</div>

        <div class="search-card p-3 p-md-4">
          <!-- ✅ ĐÃ SỬA FORM TÌM CHUYẾN -->
          <form class="row g-3 align-items-center" method="get" action="<?= BASE_URL ?>/search.php">
            <div class="col-md-4">
              <label class="form-label mb-1 text-muted">FROM</label>
              <input class="form-control" name="from_city" placeholder="Ví dụ: HCM" required>
            </div>
            <div class="col-md-4">
              <label class="form-label mb-1 text-muted">TO</label>
              <input class="form-control" name="to_city" placeholder="Ví dụ: Nha Trang" required>
            </div>
            <div class="col-md-2">
              <label class="form-label mb-1 text-muted">DATE</label>
              <input class="form-control" type="date" name="depart_date">
            </div>
            <div class="col-md-2 d-grid">
              <label class="form-label mb-1 text-muted">&nbsp;</label>
              <button type="submit" class="btn btn-search text-white">
                SEARCH BUSES
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- AUTH MODAL -->
  <div class="modal fade auth-modal" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="row g-0">
          <div class="col-md-5 auth-left d-none d-md-block">
            <div class="fw-bold mb-2">Bus System</div>
            <div class="opacity-75 small">SIGN IN / SIGN UP</div>
            <div class="mt-4 fw-bold fs-4">Quản lý xe khách</div>
            <div class="opacity-75 mt-2">Đặt vé • Quản lý chuyến • Báo cáo</div>
          </div>

          <div class="col-md-7 auth-right">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="auth-title fs-4" id="authTitle">Đăng nhập</div>
                <div class="auth-sub" id="authSub">Nhập tài khoản để vào hệ thống</div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger py-2 mt-3 mb-0">
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <!-- LOGIN FORM -->
            <form id="loginForm" class="mt-3" method="post" action="<?= BASE_URL ?>/index.php?open=login" autocomplete="off">
              <input type="hidden" name="__form" value="login">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input class="form-control" name="username" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" name="password" type="password" required>
              </div>
              <button class="btn auth-btn text-white w-100" type="submit">Đăng nhập</button>
              <div class="d-flex justify-content-between mt-3 small">
                <a class="auth-link" href="<?= BASE_URL ?>/index.php?open=register">Tạo tài khoản</a>
                <a class="text-muted text-decoration-none" href="<?= BASE_URL ?>/index.php">Trang chủ</a>
              </div>
            </form>

            <!-- REGISTER FORM -->
            <form id="registerForm" class="mt-3 d-none" method="post" action="<?= BASE_URL ?>/register.php" autocomplete="off">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Username</label>
                  <input class="form-control" name="username" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Password</label>
                  <input class="form-control" name="password" type="password" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Họ tên</label>
                  <input class="form-control" name="full_name" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">SĐT</label>
                  <input class="form-control" name="phone" required>
                </div>
              </div>
              <button class="btn auth-btn text-white w-100 mt-3" type="submit">Đăng ký</button>
              <div class="d-flex justify-content-between mt-3 small">
                <a class="auth-link" href="<?= BASE_URL ?>/index.php?open=login">Đã có tài khoản</a>
                <a class="text-muted text-decoration-none" href="<?= BASE_URL ?>/index.php">Trang chủ</a>
              </div>
              <div class="text-muted small mt-2">* Đăng ký mặc định role = seller.</div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // reveal khi page load
    window.addEventListener('DOMContentLoaded', () => {
      document.querySelector('.hero')?.classList.add('is-ready');
    });

    // parallax theo chuột
    (function(){
      const hero = document.querySelector('.hero');
      if(!hero) return;

      let raf = null;
      window.addEventListener('mousemove', (e) => {
        if (raf) return;
        raf = requestAnimationFrame(() => {
          const x = (e.clientX / window.innerWidth - 0.5) * 10;
          const y = (e.clientY / window.innerHeight - 0.5) * 10;
          hero.style.backgroundPosition = `calc(50% + ${x}px) calc(50% + ${y}px)`;
          raf = null;
        });
      }, { passive: true });
    })();

    // parallax theo scroll
    window.addEventListener('scroll', () => {
      const hero = document.querySelector('.hero');
      if(!hero) return;
      const y = window.scrollY * 0.15;
      hero.style.backgroundPosition = `50% calc(50% + ${y}px)`;
    }, { passive: true });

    // loader bar khi click open modal
    (function(){
      const loader = document.createElement('div');
      loader.className = 'top-loader';
      document.body.appendChild(loader);

      document.addEventListener('click', (e) => {
        const el = e.target.closest('a,button');
        if(!el) return;

        const href = el.getAttribute('href') || '';
        const isNav = href.includes('?open=login') || href.includes('?open=register');

        if(isNav){
          loader.style.width = '35%';
          setTimeout(() => loader.style.width = '70%', 120);
          setTimeout(() => loader.style.width = '100%', 250);
          setTimeout(() => loader.style.width = '0%', 520);
        }
      });
    })();

    // auto focus username khi mở modal
    (function(){
      const modalEl = document.getElementById('authModal');
      if(!modalEl) return;

      modalEl.addEventListener('shown.bs.modal', () => {
        const u = modalEl.querySelector('#loginForm input[name="username"]');
        u?.focus();
      });
    })();

    // open modal login/register
    const open = <?= json_encode($open) ?>;
    const modalEl = document.getElementById('authModal');

    function showRegister(){
      document.getElementById('authTitle').textContent = 'Đăng ký';
      document.getElementById('authSub').textContent = 'Tạo tài khoản để sử dụng hệ thống';
      document.getElementById('loginForm').classList.add('d-none');
      document.getElementById('registerForm').classList.remove('d-none');
    }

    function showLogin(){
      document.getElementById('authTitle').textContent = 'Đăng nhập';
      document.getElementById('authSub').textContent = 'Nhập tài khoản để vào hệ thống';
      document.getElementById('registerForm').classList.add('d-none');
      document.getElementById('loginForm').classList.remove('d-none');
    }

    if (open === 'login' || open === 'register') {
      const m = new bootstrap.Modal(modalEl);
      if (open === 'register') {
        showRegister();
      } else {
        showLogin();
      }
      m.show();
    }
  </script>
</body>
</html>