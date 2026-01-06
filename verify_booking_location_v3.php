<?php
// Configuration
$baseUrl = 'http://127.0.0.1/Earth_mover/api';
$testLocation = "Test Location - Verified " . time();

// Local DB Config
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'earthmover';

// 0. Ensure a user exists (Register/Create User)
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$userName = "Test User " . time();
$userPhone = "999" . rand(1000000, 9999999);
$userId = 0;

// Check if any user exists
$result = $conn->query("SELECT user_id FROM users LIMIT 1");
if ($result->num_rows > 0) {
    $userId = $result->fetch_assoc()['user_id'];
    echo "Using existing User ID: $userId\n";
} else {
    // Create one
    $conn->query("INSERT INTO users (name, phone, password) VALUES ('$userName', '$userPhone', 'password')");
    $userId = $conn->insert_id;
    echo "Created new User ID: $userId\n";
}

// Check operator and machine
$operatorId = 0;
$machineId = 0;

$resOp = $conn->query("SELECT operator_id FROM operators LIMIT 1");
if ($resOp->num_rows > 0) {
    $operatorId = $resOp->fetch_assoc()['operator_id'];
} else {
    // Insert dummy operator
    $conn->query("INSERT INTO operators (name, phone, password, status) VALUES ('Test Op', '8888888888', 'pass', 'available')");
    $operatorId = $conn->insert_id;
}

$resMac = $conn->query("SELECT machine_id FROM machines LIMIT 1");
if ($resMac->num_rows > 0) {
    $machineId = $resMac->fetch_assoc()['machine_id'];
} else {
     // Insert dummy machine
     // Check if category exists
     $catRes = $conn->query("SELECT category_id FROM categories LIMIT 1");
     $catId = 1;
     if ($catRes->num_rows > 0) {
         $catId = $catRes->fetch_assoc()['category_id'];
     } else {
         $conn->query("INSERT INTO categories (category_name) VALUES ('Default Cat')");
         $catId = $conn->insert_id;
     }

    $conn->query("INSERT INTO machines (name, category_id, operator_id) VALUES ('Test Machine', $catId, $operatorId)");
    $machineId = $conn->insert_id;
}
$conn->close();

echo "Using User: $userId, Op: $operatorId, Machine: $machineId\n";

// 1. Create Booking via API
$createUrl = $baseUrl . '/booking/create_booking.php';
$postData = json_encode([
    'user_id' => $userId,
    'operator_id' => $operatorId,
    'machine_id' => $machineId,
    'duration' => '2 Hours',
    'total_amount' => 5000,
    'location' => $testLocation
]);

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => $postData,
        'ignore_errors' => true 
    ]
];

echo "1. Creating Booking at $createUrl...\n";
echo "   Location: $testLocation\n";
$context  = stream_context_create($options);
$response = file_get_contents($createUrl, false, $context);
$createResult = json_decode($response, true);

if (!$createResult || !isset($createResult['success']) || !$createResult['success']) {
    echo "   FAILED to create booking.\n";
    echo "   Response: " . substr($response, 0, 200) . "...\n";
    exit(1);
}

echo "   SUCCESS. Booking ID: " . $createResult['data']['booking_id'] . "\n\n";

// 2. Verify with Get User Bookings
$getUrl = $baseUrl . '/user/get_user_bookings.php?user_id=' . $userId;
echo "2. Fetching Bookings from $getUrl...\n";

$getResponse = file_get_contents($getUrl);
$getResult = json_decode($getResponse, true);

if (!$getResult || !isset($getResult['success']) || !$getResult['success']) {
    echo "   FAILED to fetch bookings.\n";
    echo "   Response: " . $getResponse . "\n";
    exit(1);
}

$found = false;
$foundLocation = "";

foreach ($getResult['data'] as $booking) {
    if ($booking['booking_id'] == $createResult['data']['booking_id']) {
        $found = true;
        $foundLocation = $booking['location'];
        break;
    }
}

if ($found) {
    echo "   Found created booking ID " . $createResult['data']['booking_id'] . ".\n";
    echo "   Expected Location: '$testLocation'\n";
    echo "   Actual Location:   '$foundLocation'\n";
    
    if ($foundLocation === $testLocation) {
        echo "   MATCH! Location persistence is WORKING.\n";
    } else {
        echo "   MISMATCH! Location is NOT saving or retrieving correctly.\n";
    }
} else {
    echo "   Created booking NOT found in the list.\n";
}
?>
