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

$phone        = isset($data['phone']) ? trim($data['phone']) : '';
$email        = isset($data['email']) ? trim($data['email']) : '';
$role         = isset($data['role']) ? trim($data['role']) : 'user';
$otp          = isset($data['otp']) ? trim($data['otp']) : '';
$newPassword  = isset($data['new_password']) ? $data['new_password'] : '';

// For admin, use email; for others, use phone
if ($role === 'admin') {
    if ($email === '') {
        $email = $phone; // Fallback: use phone field if email not provided (for compatibility)
    }
    $identifier = $email;
} else {
    $identifier = $phone;
}

// Validation
if ($identifier === '' || $otp === '' || $newPassword === '') {
    $errorMsg = ($role === 'admin') ? 'Email, OTP and new password are required' : 'Phone, OTP and new password are required';
    echo json_encode(['success' => false, 'message' => $errorMsg], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Database connection - UPDATE THESE VALUES IF DIFFERENT
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'earthmover'; // Change this to your actual database name

// First, connect without selecting database to check if it exists
$mysqli = new mysqli($db_host, $db_user, $db_pass);

if ($mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if database exists
$result = $mysqli->query("SHOW DATABASES LIKE '$db_name'");
if ($result->num_rows == 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database not found. Please run setup_database.sql in phpMyAdmin'
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Now select the database
$mysqli->select_db($db_name);

// ============================
// OTP VALIDATION (REQUIRED)
// ============================
// For admin, phone field in password_resets contains email; for others, it contains phone/email identifier
$otpPhone = ($role === 'admin') ? $identifier : $phone;

// Ensure password_resets.phone can hold full email/phone
@$mysqli->query("ALTER TABLE `password_resets` MODIFY `phone` VARCHAR(255) NOT NULL");

// Check for matching OTP record (identifier + role + otp)
$stmt = $mysqli->prepare('SELECT id, expires_at FROM password_resets WHERE phone = ? AND role = ? AND otp = ? LIMIT 1');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database query error'], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('sss', $otpPhone, $role, $otp);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid OTP',
        'debug_note' => 'No matching OTP found for this identifier/role. Make sure you use the latest OTP from email and the same email/phone as when requesting it.',
        'identifier' => $otpPhone,
        'role' => $role
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Check if OTP expired
if (strtotime($row['expires_at']) < time()) {
    // Delete expired OTP
    $stmt = $mysqli->prepare('DELETE FROM password_resets WHERE phone = ? AND role = ?');
    $stmt->bind_param('ss', $otpPhone, $role);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.'], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// Update password in the appropriate table
// Determine if identifier is email or phone
$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

if ($role === 'admin') {
    // For admins, use email instead of phone
    $table_name = 'admins';
    $stmt = $mysqli->prepare("UPDATE $table_name SET password = ? WHERE email = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database update error'], JSON_UNESCAPED_UNICODE);
        $mysqli->close();
        exit;
    }
    $stmt->bind_param('ss', $hashedPassword, $identifier);
} elseif ($role === 'operator') {
    $table_name = 'operators';
    if ($isEmail) {
        $stmt = $mysqli->prepare("UPDATE $table_name SET password = ? WHERE email = ? LIMIT 1");
    } else {
        $stmt = $mysqli->prepare("UPDATE $table_name SET password = ? WHERE phone = ? LIMIT 1");
    }
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database update error'], JSON_UNESCAPED_UNICODE);
        $mysqli->close();
        exit;
    }
    $stmt->bind_param('ss', $hashedPassword, $identifier);
} else {
    // For users, check if identifier is email or phone
    $table_name = 'users';
    if ($isEmail) {
        $stmt = $mysqli->prepare("UPDATE $table_name SET password = ? WHERE email = ? LIMIT 1");
    } else {
        $stmt = $mysqli->prepare("UPDATE $table_name SET password = ? WHERE phone = ? LIMIT 1");
    }
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database update error'], JSON_UNESCAPED_UNICODE);
        $mysqli->close();
        exit;
    }
    $stmt->bind_param('ss', $hashedPassword, $identifier);
}
$updateSuccess = $stmt->execute();
$affectedRows = $mysqli->affected_rows;
$stmt->close();

if ($updateSuccess && $affectedRows > 0) {
    // Delete the used OTP record
    $stmt = $mysqli->prepare('DELETE FROM password_resets WHERE phone = ? AND role = ?');
    $stmt->bind_param('ss', $otpPhone, $role);
    $stmt->execute();
    $stmt->close();
    
    $mysqli->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Password changed successfully'
    ], JSON_UNESCAPED_UNICODE);
    exit;
} else {
    $mysqli->close();
    echo json_encode(['success' => false, 'message' => 'Failed to update password'], JSON_UNESCAPED_UNICODE);
    exit;
}

