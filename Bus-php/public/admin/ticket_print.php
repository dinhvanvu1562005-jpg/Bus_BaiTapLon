<?php
require_once __DIR__ . '/../../app/middleware/auth.php';
require_once __DIR__ . '/../_base.php';

require_role(['admin','seller']);

require_once __DIR__ . '/../../app/services/TicketService.php';

$svc = new TicketService();
$id = (int)($_GET['id'] ?? 0);

$ticket = $svc->getTicketDetail($id);
if (!$ticket) {
  http_response_code(404);
  echo "Không tìm thấy vé.";
  exit;
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>In vé <?=htmlspecialchars($ticket['ticket_code'])?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print { .no-print { display:none !important; } }
    .ticket-box{max-width:520px;margin:30px auto;border:1px dashed #94a3b8;border-radius:14px;padding:18px}
    .kv{display:flex;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding:8px 0}
    .kv:last-child{border-bottom:none}
  </style>
</head>
<body class="bg-light">

<div class="ticket-box bg-white">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <div class="fw-bold">VÉ XE KHÁCH</div>
      <div class="text-muted small">Bus System</div>
    </div>
    <div class="text-end">
      <div class="fw-bold"><?=htmlspecialchars($ticket['ticket_code'])?></div>
      <div class="small text-muted"><?=htmlspecialchars($ticket['created_at'])?></div>
    </div>
  </div>

  <div class="kv"><span>Tuyến</span><b><?=htmlspecialchars($ticket['from_city'])?> → <?=htmlspecialchars($ticket['to_city'])?></b></div>
  <div class="kv"><span>Giờ chạy</span><b><?=htmlspecialchars($ticket['depart_at'])?></b></div>
  <div class="kv"><span>Xe</span><b><?=htmlspecialchars($ticket['plate_no'])?> (<?=htmlspecialchars($ticket['bus_type'])?>)</b></div>
  <div class="kv"><span>Ghế</span><b><?= (int)$ticket['seat_no'] ?></b></div>

  <hr>

  <div class="kv"><span>Khách</span><b><?=htmlspecialchars($ticket['full_name'])?></b></div>
  <div class="kv"><span>SĐT</span><b><?=htmlspecialchars($ticket['phone'])?></b></div>
  <div class="kv"><span>Giá</span><b><?=number_format((int)$ticket['price'])?> VNĐ</b></div>
  <div class="kv"><span>Trạng thái</span><b><?=htmlspecialchars($ticket['status'])?></b></div>

  <div class="no-print mt-3 d-flex gap-2">
    <button class="btn btn-primary" onclick="window.print()">In</button>
    <button class="btn btn-outline-secondary" onclick="window.close()">Đóng</button>
  </div>
</div>

</body>
</html>