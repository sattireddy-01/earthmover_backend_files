

<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../utils/auth_middleware.php';

$userId = require_user(); // from X-User-Id header

try {
    $sql = "
        SELECT 
            b.booking_id,
            b.machine_id,
            m.model_name,
            m.price_per_hour,
            b.hours,
            b.amount,
            b.status,
            b.payment_status,
            b.created_at
        FROM bookings b
        JOIN machines m ON b.machine_id = m.machine_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    // Execute the prepared statement with user ID
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    send_response(true, 'Service history fetched', $rows);
} catch (Exception $e) {
    send_response(false, 'Error fetching service history', ['error' => $e->getMessage()], 500);
}