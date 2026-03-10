<?php
require_once __DIR__ . '/../services/BookingService.php';

class HomeController
{
    private BookingService $bookingService;

    public function __construct()
    {
        $this->bookingService = new BookingService();
    }

    public function search(): void
    {
        $from = trim($_GET['from_city'] ?? '');
        $to   = trim($_GET['to_city'] ?? '');
        $date = trim($_GET['depart_date'] ?? '');

        $trips = [];

        if ($from !== '' && $to !== '') {
            $trips = $this->bookingService->searchTrips($from, $to, $date ?: null);
        }

        require __DIR__ . '/../views/home/search_results.php';
    }

    public function tripDetail(): void
    {
        $tripId = (int)($_GET['trip_id'] ?? 0);
        $trip = $this->bookingService->getTripDetail($tripId);

        require __DIR__ . '/../views/home/trip_detail.php';
    }
}