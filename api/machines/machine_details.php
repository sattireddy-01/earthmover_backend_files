<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

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

$machineId = isset($_GET['machine_id']) ? (int)$_GET['machine_id'] : 0;

if ($machineId <= 0) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'machine_id is required'
    ]);
    exit;
}

try {
    if (!isset($conn) || $conn === null || ($conn instanceof mysqli && $conn->connect_error)) {
        throw new Exception('Database connection failed: ' . ($conn instanceof mysqli ? $conn->connect_error : 'Connection not available'));
    }

    // Fetch machine details with all fields
    $query = "SELECT 
                machine_id,
                operator_id,
                category_id,
                machine_model,
                price_per_hour,
                specs,
                model_year,
                image,
                machine_image_1,
                machine_year,
                equipment_type,
                availability,
                address,
                phone
              FROM machines
              WHERE machine_id = ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $machineId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Prefer machine_image_1 over image
        $imagePath = !empty($row['machine_image_1']) ? $row['machine_image_1'] : ($row['image'] ?? null);
        
        $machine = array(
            'machine_id' => (int)$row['machine_id'],
            'operator_id' => $row['operator_id'] !== null ? (int)$row['operator_id'] : null,
            'category_id' => $row['category_id'] !== null ? (int)$row['category_id'] : null,
            'model_name' => $row['machine_model'] ?? '',
            'machine_model' => $row['machine_model'] ?? '',
            'price_per_hour' => (float)$row['price_per_hour'],
            'specs' => $row['specs'] ?? '',
            'model_year' => $row['model_year'] !== null ? (int)$row['model_year'] : ($row['machine_year'] !== null ? (int)$row['machine_year'] : null),
            'equipment_type' => $row['equipment_type'] ?? '',
            'availability' => $row['availability'] ?? 'OFFLINE',
            'address' => $row['address'] ?? '',
            'phone' => $row['phone'] ?? '',
            'image' => $imagePath,
            'machine_image_1' => $row['machine_image_1'] ?? null
        );
        
        // Build full image URL if image exists
        $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Earth_mover/';
        if ($machine['image'] && !empty($machine['image'])) {
            if (strpos($machine['image'], 'http://') !== 0 && strpos($machine['image'], 'https://') !== 0) {
                $imagePath = ltrim($machine['image'], '/');
                $machine['image'] = $baseUrl . $imagePath;
            }
        }
        
        if ($machine['machine_image_1'] && !empty($machine['machine_image_1'])) {
            if (strpos($machine['machine_image_1'], 'http://') !== 0 && strpos($machine['machine_image_1'], 'https://') !== 0) {
                $machineImage1Path = ltrim($machine['machine_image_1'], '/');
                $machine['machine_image_1'] = $baseUrl . $machineImage1Path;
            }
        }
        
        ob_end_clean();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Machine details fetched successfully',
            'data' => $machine
        ], JSON_UNESCAPED_UNICODE);
    } else {
        ob_end_clean();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Machine not found'
        ], JSON_UNESCAPED_UNICODE);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;