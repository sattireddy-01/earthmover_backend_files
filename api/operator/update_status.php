<?php
/**
 * Update Operator Availability Status API
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\update_status.php
 */

// Suppress any warnings/errors that might output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Set headers first, before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed'], JSON_UNESCAPED_UNICODE);
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

// Get JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate required fields
$operator_id = isset($input['operator_id']) ? trim($input['operator_id']) : '';
$status = isset($input['status']) ? trim($input['status']) : '';

// Convert status to uppercase to match database enum (ONLINE/OFFLINE)
$status = strtoupper($status);

if (empty($operator_id) || !is_numeric($operator_id)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'operator_id is required and must be numeric'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate status - must be ONLINE or OFFLINE
if ($status !== 'ONLINE' && $status !== 'OFFLINE') {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'status must be ONLINE or OFFLINE'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if operator exists
$check_stmt = $conn->prepare("SELECT operator_id FROM operators WHERE operator_id = ?");
if (!$check_stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ], JSON_UNESCAPED_UNICODE);
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
        'message' => 'Operator not found with ID: ' . $operator_id
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$check_stmt->close();

// Update availability status
$stmt = $conn->prepare("UPDATE operators SET availability = ? WHERE operator_id = ?");

if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database prepare error: ' . $conn->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$operator_id_int = (int)$operator_id;
$stmt->bind_param("si", $status, $operator_id_int);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'ok' => true,
        'message' => 'Availability status updated successfully',
        'availability' => $status
    ], JSON_UNESCAPED_UNICODE);
    exit;
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Failed to update availability: ' . $error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}






















