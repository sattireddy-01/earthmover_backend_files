<?php
/**
 * Get Machines API
 * Returns all machines with pricing information
 * Location: C:\xampp\htdocs\Earth_mover\api\admin\get_machines.php
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Include database config
$db_path = __DIR__ . '/../../config/database.php';
if (!file_exists($db_path)) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database config file not found'
    ]);
    exit;
}

$conn = require_once $db_path;

if (!isset($conn) || $conn === null || $conn->connect_error) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . ($conn->connect_error ?? 'Connection not available')
    ]);
    exit;
}

// Get all machines - using actual database column names
// Note: model_name column has been removed, using machine_model instead
$query = "SELECT 
    machine_id,
    category_id,
    machine_model,
    price_per_hour,
    specs,
    model_year,
    image,
    equipment_type
FROM machines 
ORDER BY machine_id ASC";

$result = $conn->query($query);

if (!$result) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$machines = [];
while ($row = $result->fetch_assoc()) {
    $machines[] = [
        'machine_id' => (int)$row['machine_id'],
        'category_id' => (int)($row['category_id'] ?? 0),
        'model' => $row['machine_model'] ?? '', // Use machine_model instead of model_name
        'model_name' => $row['machine_model'] ?? '', // Keep for backward compatibility
        'machine_model' => $row['machine_model'] ?? '',
        'type' => $row['specs'] ?? '', // Use specs as type
        'equipment_type' => $row['equipment_type'] ?? '',
        'price_per_hour' => (float)($row['price_per_hour'] ?? 0.0),
        'specs' => $row['specs'] ?? '',
        'model_year' => (int)($row['model_year'] ?? 0),
        'image' => $row['image'] ?? '',
        'last_updated' => '' // Not in database, will be empty
    ];
}

ob_end_clean();
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Machines retrieved successfully',
    'data_list' => $machines  // Use data_list for array response
], JSON_UNESCAPED_UNICODE);

$conn->close();

