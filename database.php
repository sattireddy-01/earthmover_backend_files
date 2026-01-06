<?php
/**
 * Database Connection Configuration
 * Location: C:\xampp\htdocs\Earth_mover\config\database.php
 * 
 * COPY THIS FILE TO: C:\xampp\htdocs\Earth_mover\config\database.php
 */

// Database configuration
$host = 'localhost';
$dbname = 'earthmover';  // Change this if your database name is different
$username = 'root';       // Default XAMPP MySQL username
$password = '';           // Default XAMPP MySQL password (empty by default)

// Create MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    // Log error for debugging
    error_log("Database connection failed: " . $conn->connect_error);
    
    // Return JSON error if called via HTTP
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    } else {
        die("Database connection failed: " . $conn->connect_error . "\n");
    }
}

// Set charset to UTF-8 for proper character encoding
$conn->set_charset("utf8");

// IMPORTANT: Return the connection object
// This allows require_once to capture the return value
return $conn;
?>
