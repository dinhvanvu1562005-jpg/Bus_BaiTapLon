<?php
require_once __DIR__ . '/../repositories/TripRepository.php';

class TripService {
  private TripRepository $repo;

  public function __construct() {
    $this->repo = new TripRepository();
  }

  public function list(): array {
    return $this->repo->all();
  }

  public function create(array $data): array {
    $routeId = (int)($data['route_id'] ?? 0);
    $busId   = (int)($data['bus_id'] ?? 0);
    $depart  = trim($data['depart_at'] ?? '');

    if ($routeId<=0 || $busId<=0 || $depart==='') {
      return ['ok'=>false, 'message'=>'Vui lòng chọn tuyến, xe và giờ chạy'];
    }

    // BR2: không cho tạo chuyến trong quá khứ
    if (strtotime($depart) < time()) {
      return ['ok'=>false, 'message'=>'Giờ chạy không được ở quá khứ'];
    }

    try {
      $this->repo->create($routeId, $busId, $depart);
      return ['ok'=>true];
    } catch (Throwable $e) {
      return ['ok'=>false, 'message'=>'Không thể tạo chuyến (kiểm tra dữ liệu).'];
    }
  }

  public function updateStatus(array $data): array {
    $id = (int)($data['id'] ?? 0);
    $status = $data['status'] ?? '';

    $allowed = ['scheduled','departed','done','canceled'];
    if ($id<=0 || !in_array($status, $allowed, true)) {
      return ['ok'=>false, 'message'=>'Dữ liệu không hợp lệ'];
    }

    $this->repo->updateStatus($id, $status);
    return ['ok'=>true];
  }

  public function delete(int $id): array {
    if ($id<=0) return ['ok'=>false, 'message'=>'ID không hợp lệ'];
    $this->repo->delete($id);
    return ['ok'=>true];
  }
}