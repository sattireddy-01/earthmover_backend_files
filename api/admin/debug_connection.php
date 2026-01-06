<?php
/**
 * Debug Connection - Test database.php inclusion
 */

header('Content-Type: application/json');
ob_start();

// Test 1: Direct connection (like test file)
$result = ['tests' => []];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "earthmover";

// Test direct connection
$direct_conn = new mysqli($servername, $username, $password, $dbname);
if ($direct_conn->connect_error) {
    $result['tests']['direct'] = 'FAILED: ' . $direct_conn->connect_error;
} else {
    $result['tests']['direct'] = 'SUCCESS';
    $direct_conn->close();
}

// Test 2: Include database.php
$result['tests']['include_path'] = __DIR__ . '/../../config/database.php';
$result['tests']['file_exists'] = file_exists(__DIR__ . '/../../config/database.php') ? 'YES' : 'NO';

require_once __DIR__ . '/../../config/database.php';

if (isset($conn)) {
    if ($conn === null) {
        $result['tests']['included_conn'] = 'NULL';
    } else if ($conn->connect_error) {
        $result['tests']['included_conn'] = 'ERROR: ' . $conn->connect_error;
    } else {
        $result['tests']['included_conn'] = 'SUCCESS';
    }
} else {
    $result['tests']['included_conn'] = 'NOT SET';
}

ob_end_clean();
echo json_encode($result, JSON_PRETTY_PRINT);
?>

