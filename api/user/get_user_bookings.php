<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off HTML error display

// Robust database include (handles both dev and prod/xampp file structures)
$dbPath1 = __DIR__ . '/../../config/database.php'; // XAMPP standard
$dbPath2 = __DIR__ . '/../../database.php';        // Project root fallback

if (file_exists($dbPath1)) {
    include_once $dbPath1;
} elseif (file_exists($dbPath2)) {
    include_once $dbPath2;
} else {
    // Fatal error if neither found
    echo json_encode(['success' => false, 'message' => 'Database configuration not found']);
    exit;
}

// Check connection
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (isset($_GET['user_id'])) {
    $user_id = $conn->real_escape_string($_GET['user_id']);
    
    // DEBUG: Log the request to a file
    $log_entry = date('Y-m-d H:i:s') . " - Request for user_id: " . $user_id . "\n";
    file_put_contents('debug_log.txt', $log_entry, FILE_APPEND);

    $query = "SELECT 
                b.booking_id, 
                b.user_id, 
                b.operator_id, 
                b.machine_id, 
                b.status, 
                b.acceptance,
                b.created_at as booking_date, 
                b.amount as total_amount, 
                b.hours as total_hours, 
                b.location,
                u.name as user_name, 
                u.phone as user_phone, 
                o.name as operator_name, 
                o.phone as operator_phone, 
                m.model_name as machine_model, 
                m.machine_type, 
                m.image as machine_image 
              FROM bookings b
              LEFT JOIN users u ON b.user_id = u.user_id
              LEFT JOIN operators o ON b.operator_id = o.operator_id
              LEFT JOIN machines m ON b.machine_id = m.machine_id
              WHERE b.user_id = '$user_id'
              ORDER BY b.created_at DESC";

    $result = $conn->query($query);

    if ($result === false) {
        // Query Failed! Return the specific SQL error properly
        echo json_encode([
            "success" => false, 
            "message" => "Query Failed: " . $conn->error
        ]);
        exit;
    }

    $bookings_arr = array();
    $bookings_arr["success"] = false;
    $bookings_arr["data"] = array();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $booking_item = array(
                "booking_id" => $row['booking_id'],
                "user_id" => $row['user_id'],
                "operator_id" => $row['operator_id'],
                "machine_id" => $row['machine_id'],
                "status" => $row['status'],
                "acceptance" => $row['acceptance'],
                "booking_date" => $row['booking_date'],
                "total_amount" => $row['total_amount'],
                "total_hours" => $row['total_hours'],
                "location" => $row['location'],
                "user_name" => $row['user_name'],
                "user_phone" => $row['user_phone'],
                "operator_name" => $row['operator_name'] ? $row['operator_name'] : "Pending",
                "operator_phone" => $row['operator_phone'],
                "machine_model" => $row['machine_model'],
                "machine_type" => $row['machine_type'],
                "machine_image" => $row['machine_image']
            );

            array_push($bookings_arr["data"], $booking_item);
        }
        $bookings_arr["success"] = true;
        $bookings_arr["message"] = "Bookings found.";
    } else {
        $bookings_arr["success"] = true; // Still success, just empty list
        $bookings_arr["message"] = "No bookings found.";
    }
} else {
    $bookings_arr = array();
    $bookings_arr["success"] = false;
    $bookings_arr["message"] = "User ID is missing.";
}

echo json_encode($bookings_arr);
?>
