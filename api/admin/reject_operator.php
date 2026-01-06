<?php
/**
 * PHP backend file for reject_operator.php
 * Updates approve_status and approval_pending columns
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\admin\reject_operator.php
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

// Include database config
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
        $error_msg .= ". Connection test works but database.php returned null. Check PHP error logs.";
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
error_log("Reject Operator Request: " . $rawInput);

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

// Check if operator_id is provided
if (!isset($input['operator_id']) || empty($input['operator_id'])) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Operator ID is required'
    ]);
    exit;
}

$operatorId = trim($input['operator_id']);

// Validate operator_id is numeric
if (!is_numeric($operatorId)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Invalid operator ID format'
    ]);
    exit;
}

$operatorId = (int)$operatorId;

try {
    // Check if operator exists first
    $checkStmt = $conn->prepare("SELECT operator_id, approve_status FROM operators WHERE operator_id = ?");
    $checkStmt->bind_param("i", $operatorId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        $checkStmt->close();
        ob_end_clean();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'ok' => false,
            'message' => 'Operator not found with ID: ' . $operatorId
        ]);
        exit;
    }
    
    $checkStmt->close();
    
    // Update operator with new columns
    $stmt = $conn->prepare("
        UPDATE operators 
        SET approve_status = 'REJECTED', 
            approval_pending = 0 
        WHERE operator_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $operatorId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            ob_end_clean();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'ok' => true,
                'message' => 'Operator rejected successfully'
            ]);
        } else {
            // No rows affected - might already be rejected
            ob_end_clean();
            http_response_code(200);
            echo json_encode([
                'success' => false,
                'ok' => false,
                'message' => 'No changes made. Operator may already be rejected.'
            ]);
        }
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Reject Operator Error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

// Clean any output buffer and ensure only JSON is sent
ob_end_clean();
