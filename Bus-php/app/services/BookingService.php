<?php
require_once __DIR__ . '/../repositories/PublicTripRepository.php';
require_once __DIR__ . '/../repositories/BookingRepository.php';

class BookingService
{
    private PublicTripRepository $tripRepo;
    private BookingRepository $bookingRepo;

    public function __construct()
    {
        $this->tripRepo = new PublicTripRepository();
        $this->bookingRepo = new BookingRepository();
    }

    public function searchTrips(string $fromCity, string $toCity, ?string $date = null): array
    {
        return $this->tripRepo->searchTrips($fromCity, $toCity, $date);
    }

    public function getTripDetail(int $tripId): ?array
    {
        $trip = $this->tripRepo->findTripById($tripId);
        if (!$trip) {
            return null;
        }

        $trip['booked_seats'] = $this->tripRepo->getBookedSeats($tripId);
        return $trip;
    }

    public function createBooking(array $input): array
    {
        $tripId        = (int)($input['trip_id'] ?? 0);
        $customerName  = trim($input['customer_name'] ?? '');
        $customerPhone = trim($input['customer_phone'] ?? '');
        $customerEmail = trim($input['customer_email'] ?? '');
        $seatNo        = (int)($input['seat_no'] ?? 0);
        $paymentMethod = trim($input['payment_method'] ?? 'cash');

        if ($tripId <= 0 || $seatNo <= 0 || $customerName === '' || $customerPhone === '') {
            return [
                'ok' => false,
                'message' => 'Vui lòng nhập đủ thông tin đặt vé.'
            ];
        }

        $trip = $this->tripRepo->findTripById($tripId);
        if (!$trip) {
            return [
                'ok' => false,
                'message' => 'Chuyến xe không tồn tại.'
            ];
        }

        $seatCount = (int)$trip['seat_count'];
        if ($seatNo < 1 || $seatNo > $seatCount) {
            return [
                'ok' => false,
                'message' => 'Số ghế không hợp lệ.'
            ];
        }

        $bookedSeats = $this->tripRepo->getBookedSeats($tripId);
        if (in_array($seatNo, $bookedSeats, true)) {
            return [
                'ok' => false,
                'message' => 'Ghế này đã được đặt/bán.'
            ];
        }

        $price = (int)$trip['base_price'];
        $bookingCode = 'BK' . date('YmdHis') . rand(10, 99);
        $ticketCode  = 'TK' . date('YmdHis') . rand(10, 99);

        $pdo = $this->bookingRepo->getPdo();

        try {
            $pdo->beginTransaction();

            $bookingId = $this->bookingRepo->createBooking([
                'booking_code'   => $bookingCode,
                'customer_name'  => $customerName,
                'customer_phone' => $customerPhone,
                'customer_email' => $customerEmail,
                'trip_id'        => $tripId,
                'total_amount'   => $price,
                'payment_status' => 'pending',
                'booking_status' => 'confirmed',
            ]);

            $this->bookingRepo->addBookingSeat($bookingId, $seatNo, $price);

            $this->bookingRepo->createPayment(
                $bookingId,
                $paymentMethod,
                $price,
                $paymentMethod === 'cash' ? 'pending' : 'paid'
            );

            $this->bookingRepo->createTicket([
                'trip_id'        => $tripId,
                'seat_no'        => $seatNo,
                'customer_name'  => $customerName,
                'customer_phone' => $customerPhone,
                'price'          => $price,
                'ticket_code'    => $ticketCode,
                'sold_by'        => null,
            ]);

            $pdo->commit();

            return [
                'ok' => true,
                'booking_code' => $bookingCode,
                'ticket_code' => $ticketCode,
                'price' => $price,
            ];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return [
                'ok' => false,
                'message' => 'Đặt vé thất bại: ' . $e->getMessage()
            ];
        }
    }

    public function getBookingByCode(string $bookingCode): ?array
    {
        $booking = $this->bookingRepo->findBookingByCode($bookingCode);
        if (!$booking) {
            return null;
        }

        $booking['seats'] = $this->bookingRepo->getBookingSeats((int)$booking['id']);
        return $booking;
    }
}