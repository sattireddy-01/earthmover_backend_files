<?php
/**
 * Get User Bookings API
 * Returns all bookings for a specific user
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\user\get_user_bookings.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; 
$DB_NAME = 'earthmover';

$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'user_id is required']);
    exit();
}

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset("utf8mb4");
    
    $query = "
        SELECT 
            b.*,
            m.model_name as machine_model_name,
            m.image as machine_image,
            m.equipment_type,
            m.machine_model,
            c.category_name as machine_type,
            o.name as operator_name,
            o.phone as operator_phone
        FROM bookings b
        LEFT JOIN machines m ON b.machine_id = m.machine_id
        LEFT JOIN categories c ON m.category_id = c.category_id
        LEFT JOIN operators o ON b.operator_id = o.operator_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = [
            'booking_id' => (string)$row['booking_id'],
            'user_id' => (string)$row['user_id'],
            'operator_id' => $row['operator_id'] ? (string)$row['operator_id'] : null,
            'machine_id' => (string)$row['machine_id'],
            'machine_type' => $row['machine_type'] ? $row['machine_type'] : ($row['equipment_type'] ? $row['equipment_type'] : 'Machine'),
            'machine_model' => $row['machine_model'] ? $row['machine_model'] : ($row['machine_model_name'] ? $row['machine_model_name'] : ''),
            'booking_date' => date('Y-m-d', strtotime($row['created_at'])),
            'start_time' => date('H:i', strtotime($row['created_at'])),
            'location' => $row['location'] ? $row['location'] : 'Not specified',
            'duration' => $row['hours'] . ' hour' . ($row['hours'] > 1 ? 's' : ''),
            'total_hours' => (int)$row['hours'],
            'total_amount' => (float)$row['amount'],
            'status' => $row['status'],
            'payment_status' => $row['payment_status'],
            'operator_name' => $row['operator_name'],
            'operator_phone' => $row['operator_phone'],
            'machine_image' => $row['machine_image']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $bookings]);
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
