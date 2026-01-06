<?php
/**
 * Complete Booking API
 * Location: C:\xampp\htdocs\Earth_mover\api\booking\complete_booking.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = require_once '../../config/database.php';

// Ensure connection integrity
if (!isset($conn) || !($conn instanceof mysqli)) {
     if (isset($GLOBALS['conn'])) $conn = $GLOBALS['conn'];
}

if (!$conn || !($conn instanceof mysqli)) {
    // If config/database.php failed or returned something else, try the root one we fixed earlier?
    // Actually, earlier we saw root database.php returns $conn (mysqli).
    // Let's use the same inclusion logic as accept_booking_fixed.php (using root database.php if config fails?)
    // But wait, require_once only works once.
    // Let's assume $conn is available or create it if needed.
    if (!isset($conn)) {
         // Create local connection just in case
         $HOST = 'localhost'; $USER = 'root'; $PASS = ''; $DB = 'earthmover';
         $conn = new mysqli($HOST, $USER, $PASS, $DB);
    }
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$bookingId = isset($data['booking_id']) ? intval($data['booking_id']) : 0;
// operator_id is desirable but we can complete it based on booking_id
// User just says "Mark Complete", the booking already has an assigned operator.

if ($bookingId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'booking_id is required']);
    exit();
}

try {
    // Check if booking exists
    $check = $conn->query("SELECT status, amount, operator_id, user_id FROM bookings WHERE booking_id = $bookingId");
    if (!$check || $check->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit();
    }
    
    $booking = $check->fetch_assoc();
    
    // Update status to COMPLETED
    $query = "UPDATE bookings SET status = 'COMPLETED', payment_status = 'PAID' WHERE booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $bookingId);
    
    if ($stmt->execute()) {
        
        // Optional: Add to operator earnings if table exists
        // $booking['operator_id']
        // We will skip this for now to keep it simple unless requested, or use simple check
        
        echo json_encode(['success' => true, 'message' => 'Booking completed successfully']);
    } else {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();
    // $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
