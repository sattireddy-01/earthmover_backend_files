<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../utils/auth_middleware.php';

// Simple admin auth using X-Admin-Id header
$adminId = require_admin();

try {
    $stats = [];

    $stats['total_users'] = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stats['total_operators'] = (int) $pdo->query('SELECT COUNT(*) FROM operators')->fetchColumn();
    $stats['total_bookings'] = (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
    $stats['pending_bookings'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'PENDING'")->fetchColumn();
    $stats['completed_bookings'] = (int) $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'COMPLETED'")->fetchColumn();
    $stats['total_revenue'] = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_status = 'SUCCESS'")->fetchColumn();

    send_response(true, 'Dashboard stats', $stats);
} catch (Exception $e) {
    send_response(false, 'Error fetching dashboard stats', ['error' => $e->getMessage()], 500);
}


