<?php
/**
 * Get Pending Operators API
 * Returns operators with approval_pending = 1 or approve_status = 'PENDING'
 * Location: C:\xampp\htdocs\Earth_mover\api\admin\get_pending_operators.php
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Include database config
$db_path = __DIR__ . '/../../config/database.php';
if (!file_exists($db_path)) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database config file not found'
    ]);
    exit;
}

$conn = require_once $db_path;

if (!isset($conn) || $conn === null || $conn->connect_error) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . ($conn->connect_error ?? 'Connection not available')
    ]);
    exit;
}

// Get pending operators (approval_pending = 1 OR approve_status = 'PENDING')
// Only query columns that exist in the operators table
$query = "SELECT 
    operator_id,
    name,
    phone,
    email,
    address,
    license_no,
    rc_number,
    approve_status,
    approval_pending,
    availability,
    created_at
FROM operators 
WHERE (approval_pending = 1 OR approve_status = 'PENDING' OR approve_status IS NULL)
ORDER BY operator_id DESC";

$result = $conn->query($query);

if (!$result) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$operators = [];
while ($row = $result->fetch_assoc()) {
    $operators[] = [
        'operator_id' => (string)$row['operator_id'],
        'name' => $row['name'] ?? '',
        'full_name' => $row['name'] ?? '', // Use name as full_name
        'date_of_birth' => '', // Not in database
        'address' => $row['address'] ?? '',
        'phone' => $row['phone'] ?? '',
        'email' => $row['email'] ?? '',
        'license_number' => $row['license_no'] ?? '', // Map license_no to license_number
        'license_expiry' => '', // Not in database
        'machine_type' => '', // Not in database
        'total_hours' => 0, // Not in database
        'status' => $row['approve_status'] ?? 'PENDING', // Use approve_status as status
        'approve_status' => $row['approve_status'] ?? 'PENDING',
        'approval_pending' => (int)($row['approval_pending'] ?? 1),
        'profile_image' => '', // Not in database
        'rc_number' => $row['rc_number'] ?? '',
        'availability' => $row['availability'] ?? 'OFFLINE'
    ];
}

ob_end_clean();
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => count($operators) > 0 ? 'Pending operators retrieved successfully' : 'No pending operators',
    'data_list' => $operators  // Use data_list for array of operators
], JSON_UNESCAPED_UNICODE);

$conn->close();

