<?php
require_once __DIR__ . '/../repositories/RouteRepository.php';

class RouteService {
  private RouteRepository $repo;

  public function __construct() {
    $this->repo = new RouteRepository();
  }

  public function list(): array {
    return $this->repo->all();
  }

  public function create(array $data): array {
    $from = trim($data['from_city'] ?? '');
    $to   = trim($data['to_city'] ?? '');
    $time = trim($data['depart_time'] ?? '');
    $price= (int)($data['base_price'] ?? 0);

    if ($from === '' || $to === '') {
      return ['ok'=>false, 'message'=>'Vui lòng nhập điểm đi và điểm đến'];
    }
    if ($from === $to) {
      return ['ok'=>false, 'message'=>'Điểm đi và điểm đến không được giống nhau'];
    }
    if ($price < 0) {
      return ['ok'=>false, 'message'=>'Giá vé không hợp lệ'];
    }

    $departTime = ($time !== '') ? $time : null;

    try {
      $this->repo->create($from, $to, $departTime, $price);
      return ['ok'=>true];
    } catch (Throwable $e) {
      return ['ok'=>false, 'message'=>'Không thể tạo tuyến (có thể trùng tuyến).'];
    }
  }

  public function delete(int $id): array {
    if ($id <= 0) return ['ok'=>false, 'message'=>'ID không hợp lệ'];
    $this->repo->delete($id);
    return ['ok'=>true];
  }
}