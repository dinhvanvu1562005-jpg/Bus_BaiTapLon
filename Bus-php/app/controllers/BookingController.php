<?php
require_once __DIR__ . '/../services/BookingService.php';

class BookingController
{
    private BookingService $bookingService;

    public function __construct()
    {
        $this->bookingService = new BookingService();
    }

    public function seatSelect(): void
    {
        $tripId = (int)($_GET['trip_id'] ?? 0);
        $trip = $this->bookingService->getTripDetail($tripId);

        require __DIR__ . '/../views/home/seat_select.php';
    }

    public function checkout(): void
    {
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->bookingService->createBooking($_POST);
        }

        require __DIR__ . '/../views/home/checkout.php';
    }
}