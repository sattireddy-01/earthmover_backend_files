<?php
/**
 * Test if database.php works when included
 * Access: http://localhost/Earth_mover/api/test_database_include.php
 */

header('Content-Type: application/json');
ob_start();

// Include database.php
require_once __DIR__ . '/config/database.php';

$result = [
    'success' => false,
    'message' => '',
    'conn_set' => isset($conn) ? 'YES' : 'NO',
    'conn_null' => ($conn === null) ? 'YES' : 'NO',
    'conn_error' => isset($conn) && $conn !== null ? ($conn->connect_error ?: 'NO ERROR') : 'N/A'
];

if (isset($conn) && $conn !== null && !$conn->connect_error) {
    // Test query
    $test_query = $conn->query("SELECT COUNT(*) as count FROM operators");
    if ($test_query) {
        $row = $test_query->fetch_assoc();
        $result['success'] = true;
        $result['message'] = 'Database connection works!';
        $result['operators_count'] = $row['count'];
    } else {
        $result['message'] = 'Query failed: ' . $conn->error;
    }
} else {
    $result['message'] = 'Connection failed or is null';
    if (isset($conn) && $conn !== null) {
        $result['message'] .= ': ' . $conn->connect_error;
    }
}

ob_end_clean();
echo json_encode($result, JSON_PRETTY_PRINT);
?>

