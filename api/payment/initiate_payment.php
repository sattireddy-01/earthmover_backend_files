<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

require_method('POST');
$data = get_json_input();

$bookingId     = isset($data['booking_id']) ? (int) $data['booking_id'] : 0;
$paymentMethod = isset($data['payment_method']) ? strtoupper(trim($data['payment_method'])) : '';

if ($bookingId <= 0 || !in_array($paymentMethod, ['UPI', 'CARD', 'WALLET'], true)) {
    send_response(false, 'booking_id and valid payment_method (UPI, CARD, WALLET) are required', null, 400);
}

try {
    // Get booking amount
    $stmt = $pdo->prepare('SELECT amount FROM bookings WHERE booking_id = ?');
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        send_response(false, 'Booking not found', null, 404);
    }

    $amount = $booking['amount'];

    // In a real system, integrate with payment gateway here.
    // For now, we assume SUCCESS.
    $stmt = $pdo->prepare('
        INSERT INTO payments (booking_id, payment_method, amount, payment_status)
        VALUES (?, ?, ?, "SUCCESS")
    ');
    $stmt->execute([$bookingId, $paymentMethod, $amount]);

    // Mark booking as PAID
    $stmt = $pdo->prepare('UPDATE bookings SET payment_status = "PAID" WHERE booking_id = ?');
    $stmt->execute([$bookingId]);

    send_response(true, 'Payment recorded as SUCCESS', [
        'booking_id' => $bookingId,
        'amount'     => (float) $amount,
        'status'     => 'SUCCESS',
    ]);
} catch (Exception $e) {
    send_response(false, 'Error initiating payment', ['error' => $e->getMessage()], 500);
}


