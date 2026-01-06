<?php
/**
 * Create Booking API
 * Saves a new booking request to the database
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

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit();
}

$userId = isset($data['user_id']) ? intval($data['user_id']) : 0;
$operatorId = isset($data['operator_id']) ? intval($data['operator_id']) : null;
$machineId = isset($data['machine_id']) ? intval($data['machine_id']) : 0;
$hours = isset($data['duration']) ? intval(preg_replace('/[^0-9]/', '', $data['duration'])) : 0;
$amount = isset($data['total_amount']) ? floatval($data['total_amount']) : 0.0;
$status = isset($data['status']) ? $data['status'] : 'PENDING';
$location = isset($data['location']) ? $data['location'] : 'Not specified';

if ($userId <= 0 || $machineId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'user_id and machine_id are required']);
    exit();
}

try {
    $query = "INSERT INTO bookings (user_id, operator_id, machine_id, hours, amount, status, payment_status, location) 
              VALUES (?, ?, ?, ?, ?, ?, 'UNPAID', ?)";
    
    $stmt = $conn->prepare($query);
    // iiiidss -> user(i), op(i), mach(i), hours(i), amount(d), status(s), location(s)
    $stmt->bind_param("iiiidss", $userId, $operatorId, $machineId, $hours, $amount, $status, $location);
    
    if ($stmt->execute()) {
        $bookingId = $stmt->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Booking created successfully',
            'booking_id' => $bookingId
        ]);
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