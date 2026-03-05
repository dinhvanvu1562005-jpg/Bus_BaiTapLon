<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../repositories/TicketRepository.php';
require_once __DIR__ . '/../repositories/CustomerRepository.php';

class TicketService {
  private TicketRepository $ticketRepo;
  private CustomerRepository $customerRepo;

  public function __construct() {
    $this->ticketRepo = new TicketRepository();
    $this->customerRepo = new CustomerRepository();
  }

  /** Ghế đã bán theo chuyến */
  public function getBookedSeats(int $tripId): array {
    return $this->ticketRepo->seatsBooked($tripId);
  }

  /** ✅ Danh sách hành khách theo chuyến */
  public function passengersByTrip(int $tripId): array {
    if ($tripId <= 0) return [];
    return $this->ticketRepo->listByTrip($tripId);
  }

  /** ✅ Lấy chi tiết vé để in */
  public function getTicketDetail(int $ticketId): ?array {
    if ($ticketId <= 0) return null;
    return $this->ticketRepo->findDetail($ticketId);
  }

  /** ✅ Hủy vé (booked -> canceled) */
  public function cancelTicket(int $ticketId): array {
    if ($ticketId <= 0) return ['ok' => false, 'message' => 'Ticket không hợp lệ'];

    try {
      $this->ticketRepo->cancel($ticketId);
      return ['ok' => true];
    } catch (Throwable $e) {
      return ['ok' => false, 'message' => 'Không thể hủy vé.'];
    }
  }

  /** BR5: mã vé duy nhất */
  private function generateTicketCode(): string {
    return 'T' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
  }

  /** Bán vé / Đặt vé */
  public function book(array $data): array {
    $tripId = (int)($data['trip_id'] ?? 0);
    $seatNo = (int)($data['seat_no'] ?? 0);
    $name   = trim($data['full_name'] ?? '');
    $phone  = trim($data['phone'] ?? '');

    if ($tripId<=0 || $seatNo<=0 || $name==='' || $phone==='') {
      return ['ok'=>false, 'message'=>'Vui lòng nhập đủ thông tin và chọn ghế'];
    }

    $pdo = Database::conn();

    // BR2: không bán khi chuyến đã khởi hành / không còn scheduled
    $st = $pdo->prepare("SELECT depart_at, status FROM trips WHERE id=?");
    $st->execute([$tripId]);
    $trip = $st->fetch();
    if (!$trip) return ['ok'=>false, 'message'=>'Chuyến không tồn tại'];

    if (strtotime($trip['depart_at']) <= time() || $trip['status'] !== 'scheduled') {
      return ['ok'=>false, 'message'=>'Không thể bán vé: chuyến đã khởi hành/không còn mở bán'];
    }

    try {
      $pdo->beginTransaction();

      $customerId = $this->customerRepo->findOrCreate($name, $phone);

      // lấy giá theo tuyến
      $st2 = $pdo->prepare("
        SELECT r.base_price
        FROM trips t JOIN routes r ON r.id=t.route_id
        WHERE t.id=?
      ");
      $st2->execute([$tripId]);
      $price = (int)($st2->fetch()['base_price'] ?? 0);

      $code = $this->generateTicketCode();

      // BR1 được đảm bảo bởi UNIQUE(trip_id, seat_no)
      $this->ticketRepo->create($code, $tripId, $customerId, $seatNo, $price);

      $pdo->commit();
      return ['ok'=>true, 'ticket_code'=>$code, 'price'=>$price];

    } catch (Throwable $e) {
      $pdo->rollBack();
      // nếu trùng ghế sẽ rơi vào đây
      return ['ok'=>false, 'message'=>'Ghế này đã được đặt/bán. Vui lòng chọn ghế khác.'];
    }
  }
}