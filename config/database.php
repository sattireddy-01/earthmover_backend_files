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

// Create connection using mysqli
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Set charset to utf8
$conn->set_charset("utf8");

return $conn;

