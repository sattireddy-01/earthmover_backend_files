

<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

$bookingId = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : 0;

if ($bookingId <= 0) {
    send_response(false, 'booking_id is required', null, 400);
}

try {
    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE booking_id = ?');
    $stmt->execute([$bookingId]);
    $row = $stmt->fetch();

    if (!$row) {
        send_response(false, 'Booking not found', null, 404);
    }

    send_response(true, 'Booking status fetched', $row);
} catch (Exception $e) {
    send_response(false, 'Error fetching booking status', ['error' => $e->getMessage()], 500);
}