<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../utils/auth_middleware.php';

// Simple admin auth using X-Admin-Id header
$adminId = require_admin();

try {
    $reports = [];

    // Bookings by status
    $stmt = $pdo->query("
        SELECT status, COUNT(*) AS count
        FROM bookings
        GROUP BY status
    ");
    $reports['bookings_by_status'] = $stmt->fetchAll();

    // Top operators by earnings
    $stmt = $pdo->query("
        SELECT 
            o.operator_id,
            o.name,
            SUM(oe.amount) AS total_earned
        FROM operator_earnings oe
        JOIN operators o ON oe.operator_id = o.operator_id
        GROUP BY o.operator_id, o.name
        ORDER BY total_earned DESC
        LIMIT 10
    ");
    $reports['top_operators'] = $stmt->fetchAll();

    send_response(true, 'Reports data', $reports);
} catch (Exception $e) {
    send_response(false, 'Error fetching reports', ['error' => $e->getMessage()], 500);
}


