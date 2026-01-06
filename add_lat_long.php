<?php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; 
$DB_NAME = 'earthmover';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add latitude column
$sql1 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS latitude DOUBLE DEFAULT NULL";
if ($conn->query($sql1) === TRUE) {
    echo "Column 'latitude' added successfully or already exists.<br>";
} else {
    echo "Error adding column 'latitude': " . $conn->error . "<br>";
}

// Add longitude column
$sql2 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS longitude DOUBLE DEFAULT NULL";
if ($conn->query($sql2) === TRUE) {
    echo "Column 'longitude' added successfully or already exists.<br>";
} else {
    echo "Error adding column 'longitude': " . $conn->error . "<br>";
}

$conn->close();
?>
