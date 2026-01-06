

<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

require_method('POST');
$data = get_json_input();

$phone = isset($data['phone']) ? trim($data['phone']) : '';

if ($phone === '') {
    send_response(false, 'Phone is required', null, 400);
}

try {
    $stmt = $pdo->prepare('SELECT user_id, name, phone, address, created_at FROM users WHERE phone = ?');
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        send_response(false, 'User not found', null, 404);
    }

    // In real OTP flow, you would generate OTP and send SMS here.
    send_response(true, 'Login successful (no OTP used)', [
        'user' => $user,
    ]);
} catch (Exception $e) {
    send_response(false, 'Error during login', ['error' => $e->getMessage()], 500);
}