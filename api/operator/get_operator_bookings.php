<?php
/**
 * Get Operator Bookings API
 * Returns all bookings (pending and completed) for a specific operator
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\get_operator_bookings.php
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

$operatorId = isset($_GET['operator_id']) ? intval($_GET['operator_id']) : 0;

if ($operatorId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'operator_id is required']);
    exit();
}

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    $query = "
        SELECT 
            b.*,
            u.name as user_name,
            u.phone as user_phone,
            u.address as user_address,
            u.location as user_location,
            
            m.image as machine_image,
            m.equipment_type,
            m.machine_model,
            c.category_name as machine_type
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.user_id
        LEFT JOIN machines m ON b.machine_id = m.machine_id
        LEFT JOIN categories c ON m.category_id = c.category_id
        WHERE b.operator_id = ?
        ORDER BY b.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $operatorId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = [
            'booking_id' => (string)$row['booking_id'],
            'user_id' => (string)$row['user_id'],
            'user_name' => $row['user_name'],
            'user_phone' => $row['user_phone'],
            'user_address' => $row['user_address'],
            'user_location' => $row['user_location'],
            'machine_id' => (string)$row['machine_id'],
            'machine_type' => $row['machine_type'] ? $row['machine_type'] : ($row['equipment_type'] ? $row['equipment_type'] : 'Machine'),
            'machine_model' => $row['machine_model'] ? $row['machine_model'] : ($row['machine_model_name'] ? $row['machine_model_name'] : ''),
            'amount' => (float)$row['amount'],
            'hours' => (int)$row['hours'],
            'status' => $row['status'],
            'payment_status' => $row['payment_status'],
            'created_at' => $row['created_at'],
            'booking_date' => date('Y-m-d', strtotime($row['created_at'])),
            'start_time' => date('H:i', strtotime($row['created_at'])),
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
