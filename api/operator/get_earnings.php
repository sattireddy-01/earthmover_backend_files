<?php
/**
 * Get Operator Earnings (Bookings History) API Endpoint
 * File: api/operator/get_earnings.php
 */

// Set headers
error_reporting(0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include database connection
// Try different paths depending on where the file is relative to 'config'
$dbPath1 = __DIR__ . '/../../config/database.php';
$dbPath2 = __DIR__ . '/../../database.php';

if (file_exists($dbPath1)) {
    require_once $dbPath1;
} elseif (file_exists($dbPath2)) {
    require_once $dbPath2;
} else {
    echo json_encode(['success' => false, 'message' => 'Database configuration not found']);
    exit;
}

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check parameters
if (!isset($_GET['operator_id'])) {
    echo json_encode(['success' => false, 'message' => 'Operator ID is required']);
    exit;
}

$operator_id = $_GET['operator_id'];

// Prepare SQL query using prepared statement for security
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
        WHERE b.operator_id = ?
        ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $operator_id);
    $stmt->execute();
    $result = $stmt->get_result();

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
            'message' => 'Earnings data retrieved successfully',
            'data' => $bookings // The app expects 'data' or 'dataList' depending on Gson adapter, but ApiResponse usually uses 'data' for list generic
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to retrieve data: ' . $stmt->error
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to prepare query: ' . $conn->error
    ]);
}

$conn->close();
?>
