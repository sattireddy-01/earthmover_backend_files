<?php
// Suppress any warnings/errors that might output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Set the content type to JSON for the response
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// --- 1. Database Connection ---
$servername = "localhost";
$username = "root";
$password = ""; // Your XAMPP MySQL password is blank by default
$dbname = "earthmover"; // Your database name

// Use PDO for the connection, as in your original script
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}


// --- 2. Custom Response Function ---
function send_response($success, $message, $data = null, $status_code = 200) {
    http_response_code($status_code);
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}


// --- 3. Main Logic ---

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Only POST method is accepted.', null, 405);
}

// Get JSON input from the app
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data === null) {
    send_response(false, 'Invalid JSON received.', null, 400);
}

// Get and validate required data
$name     = isset($data['name']) ? trim($data['name']) : '';
$phone    = isset($data['phone']) ? trim($data['phone']) : '';
$address  = isset($data['address']) ? trim($data['address']) : '';
$email    = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if ($name === '' || $phone === '') {
    send_response(false, 'Name and phone are required', null, 400);
}

if ($password === '') {
    send_response(false, 'Password is required', null, 400);
}

if (strlen($password) < 6) {
    send_response(false, 'Password must be at least 6 characters', null, 400);
}

// Validate email format if provided
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_response(false, 'Please enter a valid email address', null, 400);
}

// --- 4. Database Interaction ---
try {
    // Check if phone already exists
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE phone = ?');
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        send_response(false, 'Phone number already registered', null, 409);
    }

    // Check if email already exists (if provided)
    if (!empty($email)) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            send_response(false, 'Email address already registered', null, 409);
        }
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert the new user (include email if provided)
    if (!empty($email)) {
        $stmt = $pdo->prepare('INSERT INTO users (name, phone, email, address, password) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$name, $phone, $email, $address, $hashedPassword]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (name, phone, address, password) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $phone, $address, $hashedPassword]);
    }

    $userId = $pdo->lastInsertId();

    // Send success response
    send_response(true, 'User registered successfully', [
        'user_id' => (int) $userId,
    ]);
} catch (Exception $e) {
    // Handle any other database errors
    send_response(false, 'Error creating user', ['error' => $e->getMessage()], 500);
}

?>