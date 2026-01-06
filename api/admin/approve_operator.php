<?php
/**
 * PHP backend file for approve_operator.php
 * Updates approve_status and approval_pending columns
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\admin\approve_operator.php
 */

// Start output buffering to prevent any accidental output
ob_start();

// Set headers first
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Include database config - use relative path from this file's location
$db_path = __DIR__ . '/../../config/database.php';

if (!file_exists($db_path)) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Database config file not found at: ' . $db_path
    ]);
    exit;
}

// Include the file and capture return value
$conn = require_once $db_path;

// Check if connection was established
if (!isset($conn) || $conn === null) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Database connection error: $conn variable not set after including database.php'
    ]);
    exit;
}

if ($conn === null) {
    ob_end_clean();
    http_response_code(500);
    
    // Try to create connection directly to get actual error
    $test_conn = new mysqli("localhost", "root", "", "earthmover");
    $actual_error = $test_conn->connect_error;
    $test_conn->close();
    
    $error_msg = "Database connection failed";
    if ($actual_error) {
        $error_msg .= ": " . $actual_error;
    } else {
        $error_msg .= ". Connection test works but database.php returned null. Check PHP error logs at: C:\\xampp\\php\\logs\\php_error_log";
    }
    
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Database connection error: ' . $error_msg
    ]);
    exit;
}

// Double check connection is still valid
if ($conn->connect_error) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Database connection error: ' . $conn->connect_error
    ]);
    exit;
}

// Get JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Log for debugging (remove in production)
error_log("Approve Operator Request: " . $rawInput);

// Check if JSON was parsed correctly
if (json_last_error() !== JSON_ERROR_NONE) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg()
    ]);
    exit;
}

// Validate operator_id
if (!isset($input['operator_id']) || empty($input['operator_id'])) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'operator_id is required'
    ]);
    exit;
}

$operator_id = $input['operator_id'];

// Validate operator_id is numeric
if (!is_numeric($operator_id)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'operator_id must be numeric'
    ]);
    exit;
}

// Check if operator exists
$check_stmt = $conn->prepare("SELECT operator_id FROM operators WHERE operator_id = ?");
if (!$check_stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$check_stmt->bind_param("i", $operator_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    ob_end_clean();
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Operator not found with ID: ' . $operator_id
    ]);
    exit;
}
$check_stmt->close();

// Update operator status
$stmt = $conn->prepare("UPDATE operators SET approve_status = 'APPROVED', approval_pending = 0 WHERE operator_id = ?");

if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $operator_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        ob_end_clean();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'ok' => true,
            'message' => 'Operator approved successfully',
            'operator_id' => $operator_id,
            'approve_status' => 'APPROVED',
            'approval_pending' => 0
        ]);
    } else {
        ob_end_clean();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'ok' => true,
            'message' => 'Operator was already approved',
            'operator_id' => $operator_id
        ]);
    }
} else {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Failed to update operator: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
