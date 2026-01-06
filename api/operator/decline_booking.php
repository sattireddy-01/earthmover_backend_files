<?php
/**
 * Decline Booking API
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\decline_booking.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = require_once '../../database.php';

// Ensure connection integrity (simple check)
if (!isset($conn) || !($conn instanceof mysqli)) {
     if (isset($GLOBALS['conn'])) $conn = $GLOBALS['conn'];
}

$data = json_decode(file_get_contents("php://input"), true);

$bookingId  = isset($data['booking_id']) ? intval($data['booking_id']) : 0;

if ($bookingId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'booking_id is required']);
    exit();
}

try {
    // Logic: Decline means "I don't want it".
    $query = "UPDATE bookings SET status = 'PENDING', operator_id = NULL, acceptance = 'DECLINED' WHERE booking_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $bookingId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Booking declined (released) successfully']);
    } else {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
