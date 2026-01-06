<?php
// Database configuration
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'earthmover';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add acceptance column (correcting spelling to 'acceptance')
// Using VARCHAR to allow 'ACCEPTED', 'DECLINED', 'PENDING'
$sql = "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS acceptance VARCHAR(50) DEFAULT 'PENDING' AFTER status";

if ($conn->query($sql) === TRUE) {
    echo "Column 'acceptance' added/checked successfully.\n";
} else {
    echo "Error adding 'acceptance': " . $conn->error . "\n";
}

$conn->close();
?>
