<?php
/**
 * Get Live Bookings API Endpoint
 * File: C:\xampp\htdocs\Earth_mover\api\admin\get_live_bookings.php
 */

// Set headers
error_reporting(0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include database connection
$dbPath = __DIR__ . '/../../config/database.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    // Try alternative path structure
    $dbPath = __DIR__ . '/../../database.php';
    if (file_exists($dbPath)) {
        require_once $dbPath;
    } else {
        echo json_encode(['success' => false, 'message' => 'Database configuration not found']);
        exit;
    }
}

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Prepare SQL query
// Fetch bookings with status 'Pending' or 'In Progress'
// Join with operators and machines table to get names
$sql = "SELECT 
            b.booking_id,
            b.user_id,
            b.operator_id,
            b.machine_id,
            b.created_at as booking_date,
            b.hours as total_hours,
            b.amount as total_amount,
            b.status,
            b.location,
            u.name as user_name,
            u.phone as user_phone,
            o.name as operator_name,
            o.phone as operator_phone,
            m.name as machine_model,
            m.type as machine_type,
            m.image as machine_image
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.user_id
        LEFT JOIN operators o ON b.operator_id = o.operator_id
        LEFT JOIN machines m ON b.machine_id = m.machine_id
        ORDER BY b.created_at DESC";

$result = $conn->query($sql);

if ($result) {
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        // Ensure numeric types are converted properly if needed
        $row['total_hours'] = (int)$row['total_hours'];
        $row['total_amount'] = (float)$row['total_amount'];
        $bookings[] = $row;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Bookings retrieved successfully',
        'data' => $bookings
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve bookings: ' . $conn->error
    ]);
}

$conn->close();
?>
