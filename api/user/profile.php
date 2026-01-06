

<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../utils/auth_middleware.php';

$userId = require_user(); // from X-User-Id header

try {
    $stmt = $pdo->prepare('SELECT user_id, name, phone, address, created_at FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        send_response(false, 'User not found', null, 404);
    }

    send_response(true, 'User profile fetched', $user);
} catch (Exception $e) {
    send_response(false, 'Error fetching profile', ['error' => $e->getMessage()], 500);
}