<?php
/**
 * Test Database Connection
 * 
 * Access this file in browser to test database connection:
 * http://localhost/Earth_mover/api/test_database_connection.php
 */

// Start output buffering
ob_start();

header('Content-Type: application/json; charset=utf-8');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "earthmover";

$result = [
    'success' => false,
    'message' => '',
    'details' => []
];

try {
    // Test 1: Connect to MySQL server
    $result['details']['step1'] = 'Testing MySQL server connection...';
    $temp_conn = new mysqli($servername, $username, $password);
    
    if ($temp_conn->connect_error) {
        throw new Exception("MySQL server connection failed: " . $temp_conn->connect_error);
    }
    $result['details']['step1'] = 'MySQL server connection: SUCCESS';
    
    // Test 2: Check if database exists
    $result['details']['step2'] = 'Checking if database exists...';
    $db_check = $temp_conn->query("SHOW DATABASES LIKE '$dbname'");
    if ($db_check->num_rows == 0) {
        $temp_conn->close();
        throw new Exception("Database '$dbname' does not exist. Please create it in phpMyAdmin.");
    }
    $result['details']['step2'] = "Database '$dbname' exists: SUCCESS";
    $temp_conn->close();
    
    // Test 3: Connect to database
    $result['details']['step3'] = 'Connecting to database...';
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    $result['details']['step3'] = 'Database connection: SUCCESS';
    
    // Test 4: Check if operators table exists
    $result['details']['step4'] = 'Checking operators table...';
    $table_check = $conn->query("SHOW TABLES LIKE 'operators'");
    if ($table_check->num_rows == 0) {
        throw new Exception("Table 'operators' does not exist. Please run the SQL migration.");
    }
    $result['details']['step4'] = 'Operators table exists: SUCCESS';
    
    // Test 5: Check if required columns exist
    $result['details']['step5'] = 'Checking required columns...';
    $columns_check = $conn->query("SHOW COLUMNS FROM operators LIKE 'approve_status'");
    if ($columns_check->num_rows == 0) {
        throw new Exception("Column 'approve_status' does not exist. Please run migrate_operators_table.sql");
    }
    $columns_check2 = $conn->query("SHOW COLUMNS FROM operators LIKE 'approval_pending'");
    if ($columns_check2->num_rows == 0) {
        throw new Exception("Column 'approval_pending' does not exist. Please run migrate_operators_table.sql");
    }
    $result['details']['step5'] = 'Required columns exist: SUCCESS';
    
    // Test 6: Test a simple query
    $result['details']['step6'] = 'Testing query...';
    $test_query = $conn->query("SELECT COUNT(*) as count FROM operators");
    if ($test_query) {
        $row = $test_query->fetch_assoc();
        $result['details']['step6'] = "Query successful. Found {$row['count']} operators in database.";
    } else {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $conn->close();
    
    $result['success'] = true;
    $result['message'] = 'All database tests passed!';
    
} catch (Exception $e) {
    $result['success'] = false;
    $result['message'] = $e->getMessage();
    $result['error'] = $e->getMessage();
}

ob_end_clean();
echo json_encode($result, JSON_PRETTY_PRINT);
?>

