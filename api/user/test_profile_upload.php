<?php
/**
 * Test Profile Picture Upload
 * 
 * This script tests if the backend can receive profile picture data
 * 
 * Usage: POST to this file with JSON containing profile_picture field
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

echo json_encode([
    'success' => true,
    'message' => 'Test endpoint reached',
    'received_data' => [
        'has_profile_picture' => isset($data['profile_picture']),
        'profile_picture_length' => isset($data['profile_picture']) ? strlen($data['profile_picture']) : 0,
        'all_keys' => $data ? array_keys($data) : [],
        'raw_input_length' => strlen($input),
        'decoded_successfully' => $data !== null
    ],
    'debug' => [
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
        'input_preview' => substr($input, 0, 200)
    ]
]);

// Log to error log
error_log("=== TEST PROFILE UPLOAD ===");
error_log("Has profile_picture: " . (isset($data['profile_picture']) ? 'YES' : 'NO'));
if (isset($data['profile_picture'])) {
    error_log("Profile picture length: " . strlen($data['profile_picture']));
}
error_log("All keys: " . implode(', ', array_keys($data ?: [])));
?>




















