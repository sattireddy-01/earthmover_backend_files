<?php
// Debug version - shows detailed error messages
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method', 'debug' => 'Method: ' . $_SERVER['REQUEST_METHOD']], JSON_UNESCAPED_UNICODE);
    exit;
}

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data', 'debug' => 'JSON Error: ' . json_last_error_msg(), 'raw' => substr($raw, 0, 200)], JSON_UNESCAPED_UNICODE);
    exit;
}

$phone = isset($data['phone']) ? trim($data['phone']) : '';
$role  = isset($data['role']) ? trim($data['role']) : 'user';

if ($phone === '') {
    echo json_encode(['success' => false, 'message' => 'Phone number is required', 'debug' => 'Received data: ' . print_r($data, true)], JSON_UNESCAPED_UNICODE);
    exit;
}

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'earthmover'; // Change this to your actual database name

// First, connect without selecting database to check if it exists
$mysqli = new mysqli($db_host, $db_user, $db_pass);

if ($mysqli->connect_errno) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed',
        'debug' => 'Error: ' . $mysqli->connect_error . ' | Host: ' . $db_host . ' | User: ' . $db_user
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if database exists
$result = $mysqli->query("SHOW DATABASES LIKE '$db_name'");
if ($result->num_rows == 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database does not exist',
        'debug' => "Database '$db_name' not found. Please run setup_database.sql in phpMyAdmin to create it.",
        'instructions' => '1. Open phpMyAdmin (http://localhost/phpmyadmin) | 2. Click SQL tab | 3. Run setup_database.sql file'
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Now select the database
$mysqli->select_db($db_name);

// Check if user/operator exists with this phone number
$table_name = ($role === 'operator') ? 'operators' : 'users';
// Use SELECT 1 instead of SELECT id to work with any table structure
$stmt = $mysqli->prepare("SELECT 1 FROM $table_name WHERE phone = ? LIMIT 1");

if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database query error',
        'debug' => 'Error: ' . $mysqli->error . ' | Table: ' . $table_name
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('s', $phone);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'No account found for this phone number',
        'debug' => 'Phone: ' . $phone . ' | Role: ' . $role . ' | Table: ' . $table_name
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $mysqli->close();
    exit;
}
$stmt->close();

// Generate 6-digit OTP
$otp = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', time() + 5 * 60);

// Check if password_resets table exists, if not create it
$tableCheck = $mysqli->query("SHOW TABLES LIKE 'password_resets'");
if ($tableCheck->num_rows == 0) {
    $createTable = "CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `phone` VARCHAR(20) NOT NULL,
        `role` VARCHAR(20) NOT NULL,
        `otp` VARCHAR(10) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_phone_role` (`phone`, `role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$mysqli->query($createTable)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to create password_resets table',
            'debug' => 'Error: ' . $mysqli->error
        ], JSON_UNESCAPED_UNICODE);
        $mysqli->close();
        exit;
    }
}

// Delete any existing OTP
$stmt = $mysqli->prepare('DELETE FROM password_resets WHERE phone = ? AND role = ?');
if ($stmt) {
    $stmt->bind_param('ss', $phone, $role);
    $stmt->execute();
    $stmt->close();
}

// Insert new OTP
$stmt = $mysqli->prepare('INSERT INTO password_resets (phone, role, otp, expires_at) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'debug' => 'Error: ' . $mysqli->error
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('ssss', $phone, $role, $otp, $expiresAt);
if ($stmt->execute()) {
    $logMessage = date('Y-m-d H:i:s') . " - Phone: $phone, Role: $role, OTP: $otp\n";
    @file_put_contents(__DIR__ . '/otp_log.txt', $logMessage, FILE_APPEND);
    
    $stmt->close();
    $mysqli->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'OTP sent to your registered mobile number',
        'debug' => 'OTP generated successfully (check otp_log.txt)'
    ], JSON_UNESCAPED_UNICODE);
    exit;
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to generate OTP',
        'debug' => 'Error: ' . $mysqli->error
    ], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $mysqli->close();
    exit;
}
?>

