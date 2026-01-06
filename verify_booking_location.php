<?php
// Configuration
$baseUrl = 'http://127.0.0.1/Earth_mover/api';
$userId = 14; 
$machineId = 9; 
$operatorId = 50; 
$testLocation = "Test Location - Generated " . time();

// 1. Create Booking
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
        'ignore_errors' => true // Fetch content even on failure status
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
