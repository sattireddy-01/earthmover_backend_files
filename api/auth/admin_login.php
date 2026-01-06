<?php
/**
 * Admin Login API
 * Location: C:\xampp\htdocs\Earth_mover\api\auth\admin_login.php
 * 
 * COPY THIS FILE TO: C:\xampp\htdocs\Earth_mover\api\auth\admin_login.php
 * (Replace your existing file)
 */

// Start output buffering
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

// Include database config
// Path: From api/auth/ to config/ = ../../config/database.php
$db_path = __DIR__ . '/../../config/database.php';

if (!file_exists($db_path)) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database config file not found at: ' . $db_path . '. Please create C:\\xampp\\htdocs\\Earth_mover\\config\\database.php'
    ]);
    exit;
}

// CRITICAL FIX: Capture the return value from database.php
// Changed from: require_once $db_path;
// To: $conn = require_once $db_path;
$conn = require_once $db_path;

// Check if connection was established
if (!isset($conn) || $conn === null) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: $conn variable not set. Make sure database.php returns $conn.'
    ]);
    exit;
}

// Verify connection is still active
if ($conn->connect_error) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $conn->connect_error
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

// Get email and password
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';
$role = isset($input['role']) ? trim($input['role']) : 'admin';

// Validation
if (empty($email) || empty($password)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Check admin credentials
$stmt = $conn->prepare("SELECT admin_id, name, email, password FROM admins WHERE email = ?");
if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    ob_end_clean();
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Invalid email or password'
    ]);
    exit;
}

$admin = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $admin['password'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Invalid email or password'
    ]);
    exit;
}

// Login successful
ob_end_clean();
http_response_code(200);
echo json_encode([
    'success' => true,
    'ok' => true,  // Added for compatibility
    'message' => 'Login successful',
    'data' => [
        'user_id' => (int)$admin['admin_id'],
        'name' => $admin['name'],
        'phone' => $admin['email'], // LoginData expects 'phone' field
        'email' => $admin['email']  // Extra field for reference
    ]
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>

