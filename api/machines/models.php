

<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

if ($categoryId <= 0) {
    send_response(false, 'category_id is required', null, 400);
}

try {
    $stmt = $pdo->prepare('
        SELECT machine_id, model_name, price_per_hour, specs, model_year, image
        FROM machines
        WHERE category_id = ?
        ORDER BY model_name
    ');
    $stmt->execute([$categoryId]);
    $rows = $stmt->fetchAll();

    send_response(true, 'Machine models fetched', $rows);
} catch (Exception $e) {
    send_response(false, 'Error fetching machine models', ['error' => $e->getMessage()], 500);
}