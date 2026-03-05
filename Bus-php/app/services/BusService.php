<?php
require_once __DIR__ . '/../repositories/BusRepository.php';

class BusService {
  private BusRepository $repo;

  public function __construct() {
    $this->repo = new BusRepository();
  }

  public function list(): array {
    return $this->repo->all();
  }

  public function create(array $data): array {
    $plate = trim($data['plate_no'] ?? '');
    $type  = trim($data['bus_type'] ?? '');
    $seat  = (int)($data['seat_count'] ?? 0);

    if ($plate === '' || $type === '' || $seat <= 0) {
      return ['ok'=>false, 'message'=>'Vui lòng nhập đủ biển số, loại xe, số ghế hợp lệ'];
    }

    try {
      $this->repo->create($plate, $type, $seat);
      return ['ok'=>true];
    } catch (Throwable $e) {
      return ['ok'=>false, 'message'=>'Không thể tạo xe (có thể trùng biển số).'];
    }
  }

  public function update(array $data): array {
    $id    = (int)($data['id'] ?? 0);
    $plate = trim($data['plate_no'] ?? '');
    $type  = trim($data['bus_type'] ?? '');
    $seat  = (int)($data['seat_count'] ?? 0);
    $active= (int)($data['is_active'] ?? 1);

    if ($id<=0 || $plate==='' || $type==='' || $seat<=0) {
      return ['ok'=>false, 'message'=>'Dữ liệu không hợp lệ'];
    }

    try {
      $this->repo->update($id, $plate, $type, $seat, $active);
      return ['ok'=>true];
    } catch (Throwable $e) {
      return ['ok'=>false, 'message'=>'Không thể cập nhật (có thể trùng biển số).'];
    }
  }

  public function delete(int $id): array {
    if ($id<=0) return ['ok'=>false, 'message'=>'ID không hợp lệ'];
    $this->repo->delete($id);
    return ['ok'=>true];
  }
}