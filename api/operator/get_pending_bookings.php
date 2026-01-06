<?php
/**
 * Get Pending Bookings API
 * Returns all pending booking requests for a specific operator
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\get_pending_bookings.php
 * 
 * GET Parameters:
 * - operator_id: The ID of the operator
 * 
 * Example: GET /api/operator/get_pending_bookings.php?operator_id=43
 * 
 * Note: The bookings table only has: booking_id, user_id, operator_id, machine_id, hours, amount, status, payment_status, created_at
 * We'll join with machines and users tables to get additional details
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET.'
    ]);
    exit();
}

// Database configuration
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // Default XAMPP password is empty
$DB_NAME = 'earthmover';

// Get operator_id from query parameter
$operatorId = isset($_GET['operator_id']) ? intval($_GET['operator_id']) : 0;

if ($operatorId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'operator_id is required and must be a valid number'
    ]);
    exit();
}

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Query to get pending bookings for this operator
    // Join with users table to get user name
    // Join with machines table to get machine details
    // Join with categories table to get machine type
    $query = "
        SELECT 
            b.booking_id,
            b.user_id,
            b.operator_id,
            b.machine_id,
            b.hours,
            b.amount,
            b.status,
            b.payment_status,
            b.created_at,
            u.name as user_name,
            u.phone as user_phone,
            u.address as user_address,
            m.model_name as machine_model_name,
            m.image as machine_image,
            m.equipment_type,
            m.machine_model,
            c.category_name as machine_type
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.user_id
        LEFT JOIN machines m ON b.machine_id = m.machine_id
        LEFT JOIN categories c ON m.category_id = c.category_id
        WHERE b.operator_id = ? 
            AND b.status = 'PENDING'
        ORDER BY b.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $operatorId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    
    while ($row = $result->fetch_assoc()) {
        // Format hours for display (e.g., "2 hours" or "2 Hours 30 Min" if we had minutes)
        $hoursDisplay = $row['hours'] . ' hour' . ($row['hours'] > 1 ? 's' : '');
        
        $booking = [
            'booking_id' => (string)$row['booking_id'],
            'user_id' => (string)$row['user_id'],
            'operator_id' => (string)$row['operator_id'],
            'machine_id' => (string)$row['machine_id'],
            'machine_type' => $row['machine_type'] ? $row['machine_type'] : ($row['equipment_type'] ? $row['equipment_type'] : 'Machine'),
            'machine_model' => $row['machine_model'] ? $row['machine_model'] : ($row['machine_model_name'] ? $row['machine_model_name'] : ''),
            'booking_date' => date('Y-m-d', strtotime($row['created_at'])), // Use created_at as booking_date
            'start_time' => date('H:i', strtotime($row['created_at'])), // Use created_at time as start_time
            'location' => $row['user_address'] ? $row['user_address'] : 'Not specified',
            'duration' => $hoursDisplay,
            'total_hours' => (int)$row['hours'],
            'total_amount' => (float)$row['amount'],
            'status' => $row['status'],
            'user_name' => $row['user_name'],
            'user_phone' => $row['user_phone'],
            'machine_image' => $row['machine_image']
        ];
        $bookings[] = $booking;
    }
    
    $stmt->close();
    $conn->close();
    
    error_log("Found " . count($bookings) . " pending bookings for operator_id: $operatorId");
    
    // Return success response with bookings list
    echo json_encode([
        'success' => true,
        'message' => 'Pending bookings retrieved successfully',
        'data' => $bookings
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("EXCEPTION: Error fetching pending bookings: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
