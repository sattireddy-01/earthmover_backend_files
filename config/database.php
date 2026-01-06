<?php
/**
 * Database Connection File
 * Place this file at: C:\xampp\htdocs\Earth_mover\config\database.php
 */

// Database configuration
$host = 'localhost';
$dbname = 'earthmover';
$username = 'root';
$password = '';

// Create connection using mysqli with exception handling
mysqli_report(MYSQLI_REPORT_OFF); // Disable auto-exceptions for cleaner handling

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}

// Set charset to utf8
$conn->set_charset("utf8");

return $conn;

