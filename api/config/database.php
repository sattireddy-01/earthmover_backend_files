<?php
/**
 * Database Configuration File
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\config\database.php
 */

// Suppress any warnings/errors that might output
error_reporting(0);
ini_set('display_errors', 0);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "earthmover";

// Initialize $conn to null first
$conn = null;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    // Connection failed
    $conn = null;
} else {
    // Connection successful - set charset
    $conn->set_charset("utf8mb4");
}

// Ensure $conn is always set (even if null)
if (!isset($conn)) {
    $conn = null;
}

// Return the connection object (required for require_once)
return $conn;