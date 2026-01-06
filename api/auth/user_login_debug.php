<?php
// Debug version - shows detailed error messages
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$identifier = '';
if (isset($data['phone']) && !empty(trim($data['phone']))) {
    $identifier = trim($data['phone']);
} elseif (isset($data['email']) && !empty(trim($data['email']))) {
    $identifier = trim($data['email']);
}

$password = isset($data['password']) ? trim($data['password']) : '';
$role = isset($data['role']) ? trim($data['role']) : 'user';

// Determine if identifier is email or phone
$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

// Database connection
$mysqli = new mysqli('localhost', 'root', '', 'earthmover');

if ($mysqli->connect_errno) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed',
        'debug' => 'Error: ' . $mysqli->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$table_name = ($role === 'operator') ? 'operators' : 'users';

// Get user by email or phone
if ($isEmail) {
    $stmt = $mysqli->prepare("SELECT user_id, name, phone, email, password FROM $table_name WHERE email = ? LIMIT 1");
} else {
    $stmt = $mysqli->prepare("SELECT user_id, name, phone, email, password FROM $table_name WHERE phone = ? LIMIT 1");
}

if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database query error',
        'debug' => 'Error: ' . $mysqli->error . ' | Table: ' . $table_name . ' | IsEmail: ' . ($isEmail ? 'Yes' : 'No')
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('s', $identifier);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode([
        'success' => false, 
        'message' => $isEmail ? 'Invalid email or password' : 'Invalid phone number or password',
        'debug' => 'Identifier: ' . $identifier . ' | Type: ' . ($isEmail ? 'Email' : 'Phone') . ' | Not found in database'
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Verify password
if (empty($user['password'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Password not set. Please reset your password.',
        'debug' => 'User found but password is empty'
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Verify password
$passwordMatch = password_verify($password, $user['password']);
if (!$passwordMatch) {
    echo json_encode([
        'success' => false, 
        'message' => $isEmail ? 'Invalid email or password' : 'Invalid phone number or password',
        'debug' => 'Password does not match | User found: ' . $user['name'] . ' | Email: ' . ($user['email'] ?? 'NULL') . ' | Phone: ' . $user['phone']
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Login successful
$mysqli->close();

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'data' => [
        'user_id' => (int)$user['user_id'],
        'name' => $user['name'],
        'phone' => $user['phone']
    ],
    'debug' => 'Login successful for: ' . $user['name']
], JSON_UNESCAPED_UNICODE);
exit;
?>




































