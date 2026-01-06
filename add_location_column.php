<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'earthmover');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column exists
$checkInternal = $conn->query("SHOW COLUMNS FROM users LIKE 'location'");
if ($checkInternal->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN location TEXT DEFAULT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'location' added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'location' already exists";
}

$conn->close();
?>
