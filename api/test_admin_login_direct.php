<?php
/**
 * Direct Test of Admin Login
 * Simulates the exact request from Android app
 */

header('Content-Type: application/json');
ob_start();

// Simulate the request
$_SERVER['REQUEST_METHOD'] = 'POST';

// Test data
$test_email = "sattireddysabbella7@gmail.com";
$test_password = "test123"; // Change this to actual password

// Include admin_login.php logic directly
require_once __DIR__ . '/config/database.php';

$result = [
    'step1_database_conn_set' => isset($conn),
    'step2_database_conn_null' => isset($conn) ? ($conn === null) : 'N/A',
    'step3_database_conn_error' => isset($conn) && $conn !== null ? ($conn->connect_error ?: 'NO ERROR') : 'N/A',
    'step4_admins_table_exists' => false,
    'step5_admin_found' => false,
    'step6_password_match' => false,
    'errors' => []
];

if (!isset($conn) || $conn === null) {
    $result['errors'][] = 'Database connection not set';
    ob_end_clean();
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

if ($conn->connect_error) {
    $result['errors'][] = 'Database connection error: ' . $conn->connect_error;
    ob_end_clean();
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Check if admins table exists
$table_check = $conn->query("SHOW TABLES LIKE 'admins'");
$result['step4_admins_table_exists'] = $table_check->num_rows > 0;

if (!$result['step4_admins_table_exists']) {
    $result['errors'][] = 'Admins table does not exist';
    ob_end_clean();
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Check if admin exists
$stmt = $conn->prepare("SELECT admin_id, name, email, password FROM admins WHERE email = ?");
if (!$stmt) {
    $result['errors'][] = 'Prepare failed: ' . $conn->error;
    ob_end_clean();
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$stmt->bind_param("s", $test_email);
$stmt->execute();
$query_result = $stmt->get_result();

if ($query_result->num_rows === 0) {
    $result['errors'][] = 'Admin not found with email: ' . $test_email;
    $stmt->close();
    ob_end_clean();
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$admin = $query_result->fetch_assoc();
$result['step5_admin_found'] = true;
$result['admin_id'] = $admin['admin_id'];
$result['admin_name'] = $admin['name'];
$result['admin_email'] = $admin['email'];
$result['password_hash'] = substr($admin['password'], 0, 20) . '...'; // Show first 20 chars

// Test password verification
if (password_verify($test_password, $admin['password'])) {
    $result['step6_password_match'] = true;
    $result['message'] = 'Password matches! Login would succeed.';
} else {
    $result['errors'][] = 'Password does not match';
    $result['message'] = 'Password verification failed. Check if password is correctly hashed.';
}

$stmt->close();
$conn->close();

ob_end_clean();
echo json_encode($result, JSON_PRETTY_PRINT);
?>

