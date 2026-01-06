<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if ($userId <= 0) {
    send_response(false, 'user_id is required', null, 400);
}

try {
    $stmt = $pdo->prepare('
        SELECT 
            p.payment_id,
            p.booking_id,
            p.payment_method,
            p.amount,
            p.payment_status,
            p.payment_date
        FROM payments p
        JOIN bookings b ON p.booking_id = b.booking_id
        WHERE b.user_id = ?
        ORDER BY p.payment_date DESC
    ');
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    send_response(true, 'Payment history fetched', $rows);
} catch (Exception $e) {
    send_response(false, 'Error fetching payment history', ['error' => $e->getMessage()], 500);
}


