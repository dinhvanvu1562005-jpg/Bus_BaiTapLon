<?php
require_once __DIR__ . '/_base.php';

// chuyển về trang chủ + mở popup login
header("Location: " . BASE_URL . "/index.php?open=login");
exit;