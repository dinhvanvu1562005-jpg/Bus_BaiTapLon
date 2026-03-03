<?php include __DIR__.'/_layout_top.php'; ?>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card p-3">
      <div class="text-muted small">Tổng chuyến hôm nay</div>
      <div class="fs-2 fw-bold">3</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3">
      <div class="text-muted small">Tổng vé đã bán</div>
      <div class="fs-2 fw-bold">250</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3">
      <div class="text-muted small">Doanh thu</div>
      <div class="fs-2 fw-bold">12,500,000</div>
    </div>
  </div>
</div>

<div class="card p-3 mt-4">
  <h5 class="mb-3">Chuyến sắp tới</h5>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Tuyến</th>
          <th>Giờ</th>
          <th>Tổng ghế</th>
          <th>Còn trống</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>HCM - Nha Trang</td>
          <td>15:00</td>
          <td>24</td>
          <td><b>20</b></td>
          <td><a class="btn btn-sm btn-primary" href="/admin/ticketing.php">Chọn chuyến</a></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__.'/_layout_bottom.php'; ?>