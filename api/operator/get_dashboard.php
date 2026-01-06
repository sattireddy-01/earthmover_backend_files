<?php
/**
 * Get Operator Dashboard API
 * Returns operator dashboard data including profile image
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\get_dashboard.php
 */

// Suppress any warnings/errors that might output before JSON
error_reporting(0);
ini_set('display_errors', 0);

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only GET method allowed'], JSON_UNESCAPED_UNICODE);
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
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$conn = require_once $db_path;

if (!isset($conn) || $conn === null || $conn->connect_error) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . ($conn->connect_error ?? 'Connection not available')
    ], JSON_UNESCAPED_UNICODE);
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
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$operator_id_int = (int)$operator_id;

// Get operator dashboard data - include profile_image column
$stmt = $conn->prepare("SELECT 
    operator_id,
    name,
    phone,
    email,
    address,
    license_no,
    rc_number,
    machine_model,
    machine_year,
    approve_status,
    availability,
    profile_image,
    created_at
FROM operators 
WHERE operator_id = ?");

if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database prepare error: ' . $conn->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("i", $operator_id_int);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    ob_end_clean();
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Operator not found'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();
$conn->close();

$operator = [
    'operator_id' => (string)$row['operator_id'],
    'name' => $row['name'] ?? '',
    'phone' => $row['phone'] ?? '',
    'email' => $row['email'] ?? '',
    'address' => $row['address'] ?? '',
    'license_number' => $row['license_no'] ?? '', // Map license_no to license_number
    'rc_number' => $row['rc_number'] ?? '',
    'machine_model' => $row['machine_model'] ?? '',
    'machine_year' => $row['machine_year'] ?? 0,
    'profile_image' => $row['profile_image'] ?? '', // Include profile image path
    'status' => $row['availability'] ?? 'OFFLINE', // Map availability to status
    'approve_status' => $row['approve_status'] ?? 'PENDING',
    'experience_years' => 0, // Not in database
    'total_bookings' => 0, // Not in database
    'rating' => 0.0, // Not in database
    'machines' => $row['machine_model'] ?? '', // Use machine_model as machines
    'license_expiry' => '' // Not in database
];

ob_end_clean();
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Operator dashboard data retrieved successfully',
    'data' => $operator
], JSON_UNESCAPED_UNICODE);
exit;






















