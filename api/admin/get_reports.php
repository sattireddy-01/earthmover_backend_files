<?php
/**
 * Get Reports Data API
 * Returns reports/statistics data for admin dashboard
 * Location: C:\xampp\htdocs\Earth_mover\api\admin\get_reports.php
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Include database config
require_once __DIR__ . '/../../config/database.php';

if (!isset($conn) || $conn === null || $conn->connect_error) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . ($conn->connect_error ?? 'Connection not available')
    ]);
    exit;
}

// Initialize reports data
$reports = [
    'total_operators' => 0,
    'pending_operators' => 0,
    'approved_operators' => 0,
    'total_bookings' => 0,
    'completed_bookings' => 0,
    'total_revenue' => 0.0,
    'total_machines' => 0
];

// Get total operators
$result = $conn->query("SELECT COUNT(*) as count FROM operators");
if ($result) {
    $row = $result->fetch_assoc();
    $reports['total_operators'] = (int)$row['count'];
}

// Get pending operators
$result = $conn->query("SELECT COUNT(*) as count FROM operators WHERE approval_pending = 1 OR approve_status = 'PENDING' OR approve_status IS NULL");
if ($result) {
    $row = $result->fetch_assoc();
    $reports['pending_operators'] = (int)$row['count'];
}

// Get approved operators
$result = $conn->query("SELECT COUNT(*) as count FROM operators WHERE approve_status = 'APPROVED'");
if ($result) {
    $row = $result->fetch_assoc();
    $reports['approved_operators'] = (int)$row['count'];
}

// Get total bookings (if bookings table exists)
$table_check = $conn->query("SHOW TABLES LIKE 'bookings'");
if ($table_check->num_rows > 0) {
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings");
    if ($result) {
        $row = $result->fetch_assoc();
        $reports['total_bookings'] = (int)$row['count'];
    }
    
    // Get completed bookings
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'");
    if ($result) {
        $row = $result->fetch_assoc();
        $reports['completed_bookings'] = (int)$row['count'];
    }
    
    // Get total revenue
    $result = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE status = 'completed'");
    if ($result) {
        $row = $result->fetch_assoc();
        $reports['total_revenue'] = (float)($row['total'] ?? 0.0);
    }
}

// Get total machines (if machines table exists)
$table_check = $conn->query("SHOW TABLES LIKE 'machines'");
if ($table_check->num_rows > 0) {
    $result = $conn->query("SELECT COUNT(*) as count FROM machines");
    if ($result) {
        $row = $result->fetch_assoc();
        $reports['total_machines'] = (int)$row['count'];
    }
}

ob_end_clean();
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Reports data retrieved successfully',
    'data' => [$reports]
], JSON_UNESCAPED_UNICODE);

$conn->close();

