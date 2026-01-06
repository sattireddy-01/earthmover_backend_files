<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Correct include path for XAMPP structure
// deployed to api/user/, needs to go to config/database.php
include_once '../../config/database.php';

// Check connection
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if (isset($_GET['user_id'])) {
    $user_id = $conn->real_escape_string($_GET['user_id']);

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
