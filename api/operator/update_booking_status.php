<?php
/**
 * Update Booking Status API
 * Allows operators to Accept, Decline, Start, or Complete a booking.
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\update_booking_status.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../database.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

$bookingId = isset($data['booking_id']) ? intval($data['booking_id']) : 0;
// operator_id might be passed, or we verify session. Assuming passed param.
$operatorId = isset($data['operator_id']) ? intval($data['operator_id']) : 0;
$status = isset($data['status']) ? strtoupper(trim($data['status'])) : '';

if ($bookingId <= 0 || empty($status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'booking_id and status are required']);
    exit();
}

// Logic: Map App Status -> DB Status
// App might send: ACCEPTED, DECLINED, START_WORK (-> IN_PROGRESS), COMPLETE_WORK (-> COMPLETED)
$dbStatus = $status;
$shouldReleaseOperator = false;

if ($status === 'DECLINED') {
    // If declined, we set it back to PENDING and remove operator so others can see it
    $dbStatus = 'PENDING';
    $shouldReleaseOperator = true;
} else if ($status === 'START_WORK') {
    $dbStatus = 'IN_PROGRESS';
} else if ($status === 'COMPLETE_WORK') {
    $dbStatus = 'COMPLETED';
}

// Check allowed DB enums
$allowedDbStatuses = ['PENDING', 'ACCEPTED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];
if (!in_array($dbStatus, $allowedDbStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Invalid status: $status (mapped to $dbStatus)"]);
    exit();
}

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    if ($shouldReleaseOperator) {
        $query = "UPDATE bookings SET status = ?, operator_id = NULL WHERE booking_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $dbStatus, $bookingId);
    } else {
        // Normal update
        if ($dbStatus === 'ACCEPTED') {
             // Ensure operator_id is set
             $query = "UPDATE bookings SET status = ?, operator_id = ? WHERE booking_id = ?";
             $stmt = $conn->prepare($query);
             $stmt->bind_param("sii", $dbStatus, $operatorId, $bookingId);
        } else {
             $query = "UPDATE bookings SET status = ? WHERE booking_id = ?";
             $stmt = $conn->prepare($query);
             $stmt->bind_param("si", $dbStatus, $bookingId);
        }
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
    } else {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
