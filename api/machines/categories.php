

<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/response.php';

try {
    $stmt = $pdo->query('SELECT category_id, category_name FROM categories ORDER BY category_name');
    $rows = $stmt->fetchAll();

    send_response(true, 'Categories fetched', $rows);
} catch (Exception $e) {
    send_response(false, 'Error fetching categories', ['error' => $e->getMessage()], 500);
}