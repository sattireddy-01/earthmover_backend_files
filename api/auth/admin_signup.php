<?php
// Suppress any warnings/errors that might output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Set headers first, before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get and validate required data
$name     = isset($data['name']) ? trim($data['name']) : '';
$email    = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

// Validation
if ($name === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => 'Name and email are required'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($password === '') {
    echo json_encode(['success' => false, 'message' => 'Password is required'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli->select_db($db_name);

// Check if email already exists
$stmt = $mysqli->prepare('SELECT admin_id FROM admins WHERE email = ?');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database query error'], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email address already registered'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $mysqli->close();
    exit;
}
$stmt->close();

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert the new admin
$stmt = $mysqli->prepare('INSERT INTO admins (name, email, password) VALUES (?, ?, ?)');

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('sss', $name, $email, $hashedPassword);

if ($stmt->execute()) {
    $adminId = $mysqli->insert_id;
    $stmt->close();
    $mysqli->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin registered successfully',
        'data' => [
            'admin_id' => (int)$adminId
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to register admin: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $mysqli->close();
}
exit;
?>




































