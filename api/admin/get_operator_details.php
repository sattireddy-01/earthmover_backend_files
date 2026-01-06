<?php
/**
 * Get Operator Details API
 * Returns detailed information about a specific operator
 * Location: C:\xampp\htdocs\Earth_mover\api\admin\get_operator_details.php
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

// Get operator_id from query parameter
$operator_id = isset($_GET['operator_id']) ? trim($_GET['operator_id']) : '';

if (empty($operator_id) || !is_numeric($operator_id)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'operator_id is required and must be numeric'
    ]);
    exit;
}

// Get operator details - only query columns that exist in the operators table
$stmt = $conn->prepare("SELECT 
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
WHERE operator_id = ?");

if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $operator_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    ob_end_clean();
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Operator not found'
    ]);
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();

$operator = [
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

ob_end_clean();
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Operator details retrieved successfully',
    'data' => $operator  // Use data for single operator object
], JSON_UNESCAPED_UNICODE);

$conn->close();

