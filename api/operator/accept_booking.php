<?php
/**
 * Accept Booking API
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\accept_booking.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database config which gives us $conn
$conn = require_once '../../database.php';

// If require_once returned true (already included), we might not have $conn captured.
// But $conn variable should be available in scope if file was included.
if (!isset($conn) || $conn === true) {
    // try to find it in global scope or re-include via require if needed, 
    // but usually in these simple PHP apps, simple require works.
    if (isset($GLOBALS['conn'])) {
        $conn = $GLOBALS['conn'];
    } else {
        // Fallback to explicit path if needed, or error out
        // Let's assume standard include behavior: variables leak to scope.
        // If $conn is null, we can't proceed.
        // Actually, 'database.php' has 'return $conn;' at the end.
        // So $conn = require_once... works if it's the first time.
        // If it's the second time, it returns true.
        // Safest is to just use $conn if it exists.
    }
}

if (!$conn || !($conn instanceof mysqli)) {
    // One last try: maybe it's named differently or we need to connect manually using credentials from file?
    // But file didn't export credentials. 
    // Let's assume $conn is available.
    if (!isset($conn)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed (variable not set)']);
        exit();
    }
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

$operatorId = isset($data['operator_id']) ? intval($data['operator_id']) : 0;
$bookingId  = isset($data['booking_id']) ? intval($data['booking_id']) : 0;

if ($bookingId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'booking_id is required']);
    exit();
}

try {
    // Update with new 'acceptance' column
    if ($operatorId > 0) {
        $query = "UPDATE bookings SET status = 'ACCEPTED', acceptance = 'ACCEPTED', operator_id = ? WHERE booking_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $operatorId, $bookingId);
    } else {
         http_response_code(400);
         echo json_encode(['success' => false, 'message' => 'operator_id required to accept']);
         exit();
    }
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Booking accepted successfully']);
        } else {
             // Check status
            $check = $conn->query("SELECT status, operator_id FROM bookings WHERE booking_id = $bookingId");
            if ($check && $row = $check->fetch_assoc()) {
                if ($row['status'] === 'ACCEPTED') {
                    // Update acceptance column if it was missing (idempotency)
                    $conn->query("UPDATE bookings SET acceptance = 'ACCEPTED' WHERE booking_id = $bookingId");
                    echo json_encode(['success' => true, 'message' => 'Booking already accepted']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to accept (Status mismatch)']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Booking not found']);
            }
        }
    } else {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();
    // Do not close $conn if it's shared? Usually PHP closes at end of script.
    // $conn->close(); 
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
