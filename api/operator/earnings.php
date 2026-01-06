

<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

$operatorId = isset($_GET['operator_id']) ? (int) $_GET['operator_id'] : 0;

if ($operatorId <= 0) {
    send_response(false, 'operator_id is required', null, 400);
}

try {
    $stmt = $pdo->prepare('
        SELECT oe.earning_id, oe.amount, oe.created_at, b.booking_id
        FROM operator_earnings oe
        JOIN bookings b ON oe.booking_id = b.booking_id
        WHERE oe.operator_id = ?
        ORDER BY oe.created_at DESC
    ');
    $stmt->execute([$operatorId]);
    $rows = $stmt->fetchAll();

    send_response(true, 'Operator earnings fetched', $rows);
} catch (Exception $e) {
    send_response(false, 'Error fetching earnings', ['error' => $e->getMessage()], 500);
}