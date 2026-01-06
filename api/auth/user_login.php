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

// Accept either phone or email as identifier
// Android sends it in 'phone' field, but it could be email
$identifier = '';
if (isset($data['phone']) && !empty(trim($data['phone']))) {
    $identifier = trim($data['phone']);
} elseif (isset($data['email']) && !empty(trim($data['email']))) {
    $identifier = trim($data['email']);
}

$password = isset($data['password']) ? trim($data['password']) : '';
$role = isset($data['role']) ? trim($data['role']) : 'user';

// Validation
if ($identifier === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Phone/Email and password are required'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Determine if identifier is email or phone
$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

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

// Select database
$mysqli->select_db($db_name);

// Determine table name based on role
if ($role === 'admin') {
    $table_name = 'admins';
} elseif ($role === 'operator') {
    $table_name = 'operators';
} else {
    $table_name = 'users';
}

// Get user/operator/admin by email or phone
// Handle different primary key names (user_id for users, operator_id for operators, admin_id for admins)
if ($role === 'admin') {
    // For admins, use email only (admins don't have phone)
    $stmt = $mysqli->prepare("SELECT admin_id as user_id, name, email, password FROM $table_name WHERE email = ? LIMIT 1");
} elseif ($role === 'operator') {
    // For operators, use operator_id
    if ($isEmail) {
        $stmt = $mysqli->prepare("SELECT operator_id as user_id, name, phone, email, password FROM $table_name WHERE email = ? LIMIT 1");
    } else {
        $stmt = $mysqli->prepare("SELECT operator_id as user_id, name, phone, email, password FROM $table_name WHERE phone = ? LIMIT 1");
    }
} else {
    // For users, use user_id
    if ($isEmail) {
        $stmt = $mysqli->prepare("SELECT user_id, name, phone, email, password FROM $table_name WHERE email = ? LIMIT 1");
    } else {
        $stmt = $mysqli->prepare("SELECT user_id, name, phone, email, password FROM $table_name WHERE phone = ? LIMIT 1");
    }
}

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database query error'], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('s', $identifier);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    // Check if email exists but is NULL
    if ($isEmail) {
        // Try to find user by phone to see if email just needs to be added
        $checkStmt = $mysqli->prepare("SELECT user_id, name, phone, email FROM $table_name WHERE email IS NULL OR email = '' LIMIT 5");
        if ($checkStmt) {
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $usersWithoutEmail = [];
            while ($row = $checkResult->fetch_assoc()) {
                $usersWithoutEmail[] = $row['phone'] . ' (' . $row['name'] . ')';
            }
            $checkStmt->close();
            
            if (!empty($usersWithoutEmail)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Email not found. Please add email to your account or login with phone number.',
                    'hint' => 'Your email is not registered. Add it to your account or use phone: ' . implode(', ', $usersWithoutEmail)
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password'], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number or password'], JSON_UNESCAPED_UNICODE);
    }
    $mysqli->close();
    exit;
}

// Verify password
if (empty($user['password'])) {
    // If password column is empty, check if it's a new user without password set
    echo json_encode(['success' => false, 'message' => 'Password not set. Please reset your password.'], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Verify password using password_verify (for bcrypt hashed passwords)
if (!password_verify($password, $user['password'])) {
    $errorMsg = $isEmail ? 'Invalid email or password' : 'Invalid phone number or password';
    echo json_encode(['success' => false, 'message' => $errorMsg], JSON_UNESCAPED_UNICODE);
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
    ]
], JSON_UNESCAPED_UNICODE);
exit;
?>

