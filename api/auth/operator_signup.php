<?php
// Suppress any warnings/errors that might output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

// Set headers first, before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data: ' . json_last_error_msg()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get and validate required data
$name        = isset($data['name']) ? trim($data['name']) : '';
$phone       = isset($data['phone']) ? trim($data['phone']) : '';
$email       = isset($data['email']) ? trim($data['email']) : '';
$address     = isset($data['address']) ? trim($data['address']) : '';
$licenseNo   = isset($data['license_no']) ? trim($data['license_no']) : '';
$rcNumber    = isset($data['rc_number']) ? trim($data['rc_number']) : '';
$password    = isset($data['password']) ? trim($data['password']) : '';

// Validation
if ($name === '' || $phone === '' || $email === '') {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name, phone and email are required'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($password === '') {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password is required'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($password) < 6) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate email format (now mandatory)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'earthmover';

$mysqli = new mysqli($db_host, $db_user, $db_pass);

if ($mysqli->connect_errno) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli->select_db($db_name);

// Check if phone already exists
$stmt = $mysqli->prepare('SELECT operator_id FROM operators WHERE phone = ?');
if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database query error: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('s', $phone);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $stmt->close();
    $mysqli->close();
    ob_end_clean();
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Phone number already registered'], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->close();

// Check if email already exists (now mandatory)
$stmt = $mysqli->prepare('SELECT operator_id FROM operators WHERE email = ?');
if ($stmt) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        ob_end_clean();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email address already registered'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt->close();
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert the new operator
// Build query based on provided fields (email is now mandatory)
$fields = ['name', 'phone', 'email', 'password'];
$values = [$name, $phone, $email, $hashedPassword];
$placeholders = ['?', '?', '?', '?'];

if (!empty($address)) {
    $fields[] = 'address';
    $values[] = $address;
    $placeholders[] = '?';
}

if (!empty($licenseNo)) {
    $fields[] = 'license_no';
    $values[] = $licenseNo;
    $placeholders[] = '?';
}

if (!empty($rcNumber)) {
    $fields[] = 'rc_number';
    $values[] = $rcNumber;
    $placeholders[] = '?';
}

$fieldsStr = implode(', ', $fields);
$placeholdersStr = implode(', ', $placeholders);

$sql = "INSERT INTO operators ($fieldsStr) VALUES ($placeholdersStr)";
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Bind parameters dynamically
$types = str_repeat('s', count($values));
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    $operatorId = $mysqli->insert_id;
    $stmt->close();
    $mysqli->close();
    
    // Clean output buffer before sending JSON
    ob_end_clean();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Operator registered successfully',
        'data' => [
            'operator_id' => (int)$operatorId,
            'user_id' => (int)$operatorId  // Also include user_id for compatibility
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
} else {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to register operator: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $mysqli->close();
    exit;
}
?>
