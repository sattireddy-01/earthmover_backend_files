<?php
/**
 * Update Machine Pricing API
 * Updates the price_per_hour for a machine
 * Location: C:\xampp\htdocs\Earth_mover\api\admin\update_machine_pricing.php
 */

ob_start();
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
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
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

// Get JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg()
    ]);
    exit;
}

// Validate required fields
$machine_id = isset($input['machine_id']) ? (int)$input['machine_id'] : 0;
$price_per_hour = isset($input['price_per_hour']) ? (float)$input['price_per_hour'] : 0.0;

if ($machine_id <= 0) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'machine_id is required and must be greater than 0'
    ]);
    exit;
}

if ($price_per_hour < 0) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'price_per_hour must be greater than or equal to 0'
    ]);
    exit;
}

// Check if machine exists
$check_stmt = $conn->prepare("SELECT machine_id FROM machines WHERE machine_id = ?");
if (!$check_stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$check_stmt->bind_param("i", $machine_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    ob_end_clean();
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Machine not found with ID: ' . $machine_id
    ]);
    exit;
}
$check_stmt->close();

// Update machine pricing (machines table doesn't have last_updated column, so we only update price_per_hour)
$stmt = $conn->prepare("UPDATE machines SET price_per_hour = ? WHERE machine_id = ?");

if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("di", $price_per_hour, $machine_id);

if ($stmt->execute()) {
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'ok' => true,
        'message' => 'Machine pricing updated successfully',
        'machine_id' => $machine_id,
        'price_per_hour' => $price_per_hour
    ]);
} else {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Failed to update machine pricing: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

