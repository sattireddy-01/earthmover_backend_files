<?php
/**
 * Test Admin Login Endpoint
 * Access: http://localhost/Earth_mover/api/test_admin_login.php
 */

header('Content-Type: application/json');

// Test 1: Check if admin_login.php exists
$admin_login_path = __DIR__ . '/auth/admin_login.php';
$file_exists = file_exists($admin_login_path);

// Test 2: Check database connection
require_once __DIR__ . '/config/database.php';

$result = [
    'admin_login_file_exists' => $file_exists,
    'admin_login_path' => $admin_login_path,
    'database_conn_set' => isset($conn),
    'database_conn_null' => isset($conn) ? ($conn === null) : 'N/A',
    'database_conn_error' => isset($conn) && $conn !== null ? ($conn->connect_error ?: 'NO ERROR') : 'N/A'
];

// Test 3: Check if admins table exists
if (isset($conn) && $conn !== null && !$conn->connect_error) {
    $table_check = $conn->query("SHOW TABLES LIKE 'admins'");
    $result['admins_table_exists'] = $table_check->num_rows > 0;
    
    if ($table_check->num_rows > 0) {
        $count_result = $conn->query("SELECT COUNT(*) as count FROM admins");
        if ($count_result) {
            $row = $count_result->fetch_assoc();
            $result['admins_count'] = (int)$row['count'];
        }
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>

