

<?php

require_once __DIR__ . '/../../utils/response.php';

require_method('POST');
$data = get_json_input();

$phone = isset($data['phone']) ? trim($data['phone']) : '';
$otp   = isset($data['otp']) ? trim($data['otp']) : '';

if ($phone === '' || $otp === '') {
    send_response(false, 'Phone and OTP are required', null, 400);
}

// Dummy implementation: accept any OTP for now
send_response(true, 'OTP verified successfully (dummy implementation)');