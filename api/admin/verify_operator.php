<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../utils/auth_middleware.php';

require_method('POST');

// Simple admin auth using X-Admin-Id header
$adminId = require_admin();

$data = get_json_input();

$operatorId = isset($data['operator_id']) ? (int) $data['operator_id'] : 0;
$isVerified = isset($data['is_verified']) ? (int) $data['is_verified'] : 0;

if ($operatorId <= 0) {
    send_response(false, 'operator_id is required', null, 400);
}

// Normalize value to 0 or 1
$isVerified = $isVerified ? 1 : 0;

try {
    // Check if operator exists
    $checkStmt = $pdo->prepare('SELECT operator_id, is_verified FROM operators WHERE operator_id = ?');
    $checkStmt->execute([$operatorId]);
    $existing = $checkStmt->fetch();

    if (!$existing) {
        send_response(false, 'Operator not found', null, 404);
    }

    $stmt = $pdo->prepare('UPDATE operators SET is_verified = ? WHERE operator_id = ?');
    $stmt->execute([$isVerified, $operatorId]);

    send_response(true, 'Operator verification updated', [
        'operator_id'    => $operatorId,
        'old_is_verified'=> (int)$existing['is_verified'],
        'new_is_verified'=> $isVerified
    ]);
} catch (Exception $e) {
    send_response(false, 'Error updating operator', ['error' => $e->getMessage()], 500);
}


