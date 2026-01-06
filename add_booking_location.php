<?php
// Database configuration
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'earthmover';

// Create connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add location column if it doesn't exist
$sql = "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS location VARCHAR(255) DEFAULT 'Not specified' AFTER payment_status";

if ($conn->query($sql) === TRUE) {
    echo "Table 'bookings' updated successfully with 'location' column.";
} else {
    echo "Error updating table: " . $conn->error;
}

$conn->close();
?>
