<?php
/**
 * Simple test to verify database.php inclusion works
 * Access: http://localhost/Earth_mover/api/test_include_simple.php
 */

header('Content-Type: application/json');

// Test 1: Direct connection
$direct_conn = new mysqli("localhost", "root", "", "earthmover");
$direct_works = !$direct_conn->connect_error;
$direct_error = $direct_conn->connect_error;
$direct_conn->close();

// Test 2: Include database.php
$db_path = __DIR__ . '/config/database.php';
$file_exists = file_exists($db_path);

$include_works = false;
$conn_set = false;
$conn_null = false;
$conn_error = '';

if ($file_exists) {
    require_once $db_path;
    $conn_set = isset($conn);
    if ($conn_set) {
        $conn_null = ($conn === null);
        if (!$conn_null) {
            $conn_error = $conn->connect_error ?: 'NO ERROR';
            $include_works = true;
        }
    }
}

echo json_encode([
    'test1_direct_connection' => [
        'works' => $direct_works,
        'error' => $direct_error ?: 'NO ERROR'
    ],
    'test2_include_database' => [
        'file_exists' => $file_exists,
        'file_path' => $db_path,
        'conn_set' => $conn_set,
        'conn_null' => $conn_null,
        'conn_error' => $conn_error,
        'works' => $include_works
    ],
    'current_dir' => __DIR__
], JSON_PRETTY_PRINT);
?>

