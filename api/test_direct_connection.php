<?php
/**
 * Direct Connection Test - Same as database.php
 * Test this to see if connection works when included
 */

header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "earthmover";

// Create connection (exactly like database.php)
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'error' => $conn->connect_error,
        'message' => 'Connection failed'
    ]);
} else {
    $conn->set_charset("utf8mb4");
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM operators");
    if ($result) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'message' => 'Connection works!',
            'operators_count' => $row['count']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $conn->error,
            'message' => 'Query failed'
        ]);
    }
    $conn->close();
}
?>

