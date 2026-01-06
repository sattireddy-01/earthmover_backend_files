

<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

require_method('POST');
$data = get_json_input();

$operatorId   = isset($data['operator_id']) ? (int) $data['operator_id'] : 0;
$availability = isset($data['availability']) ? strtoupper(trim($data['availability'])) : '';

if ($operatorId <= 0 || !in_array($availability, ['ONLINE', 'OFFLINE'], true)) {
    send_response(false, 'operator_id and availability (ONLINE/OFFLINE) are required', null, 400);
}

try {
    // First, ensure operator exists
    $checkStmt = $pdo->prepare('SELECT operator_id, availability FROM operators WHERE operator_id = ?');
    $checkStmt->execute([$operatorId]);
    $existing = $checkStmt->fetch();

    if (!$existing) {
        send_response(false, 'Operator not found', null, 404);
    }

    // Update availability (even if value is the same)
    $stmt = $pdo->prepare('UPDATE operators SET availability = ? WHERE operator_id = ?');
    $stmt->execute([$availability, $operatorId]);

    send_response(true, 'Availability updated successfully', [
        'operator_id'   => $operatorId,
        'old_status'    => $existing['availability'],
        'new_status'    => $availability
    ]);
} catch (Exception $e) {
    send_response(false, 'Error updating availability', ['error' => $e->getMessage()], 500);
}