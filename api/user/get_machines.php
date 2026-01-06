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

$response = array('success' => false, 'message' => '', 'data' => array());

try {
    if (!isset($conn) || $conn === null || ($conn instanceof mysqli && $conn->connect_error)) {
        throw new Exception('Database connection failed: ' . ($conn instanceof mysqli ? $conn->connect_error : 'Connection not available'));
    }

    // Fetch all machines from the database
    // Note: model_name column has been removed, using machine_model instead
    $query = "SELECT 
                machine_id,
                category_id,
                machine_model,
                price_per_hour,
                specs,
                model_year,
                image,
                machine_image_1,
                equipment_type
              FROM machines
              ORDER BY machine_id ASC";
    
    $result = $conn->query($query);
    
    if ($result) {
        $machines = array();
        
        while ($row = $result->fetch_assoc()) {
            // Prefer machine_image_1 over image
            $imagePath = !empty($row['machine_image_1']) ? $row['machine_image_1'] : ($row['image'] ?? null);
            $machineImage1 = !empty($row['machine_image_1']) ? $row['machine_image_1'] : null;
            
            $machine = array(
                'machine_id' => (int)$row['machine_id'],
                'category_id' => $row['category_id'] !== null ? (int)$row['category_id'] : null,
                'model_name' => $row['machine_model'] ?? '', // Keep for backward compatibility
                'machine_model' => $row['machine_model'] ?? '',
                'price_per_hour' => (float)$row['price_per_hour'],
                'specs' => $row['specs'] ?? '',
                'model_year' => $row['model_year'] !== null ? (int)$row['model_year'] : null,
                'equipment_type' => $row['equipment_type'] ?? '',
                'image' => $imagePath,
                'machine_image_1' => $machineImage1
            );
            
            // Build full image URL if image exists (for both image and machine_image_1)
            $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/Earth_mover/';
            
            if ($machine['image'] && !empty($machine['image'])) {
                // If image path doesn't already contain full URL, construct it
                if (strpos($machine['image'], 'http://') !== 0 && strpos($machine['image'], 'https://') !== 0) {
                    // Remove leading slash if present
                    $imagePath = ltrim($machine['image'], '/');
                    $machine['image'] = $baseUrl . $imagePath;
                }
            }
            
            // Also build full URL for machine_image_1 if it exists
            if ($machine['machine_image_1'] && !empty($machine['machine_image_1'])) {
                if (strpos($machine['machine_image_1'], 'http://') !== 0 && strpos($machine['machine_image_1'], 'https://') !== 0) {
                    $machineImage1Path = ltrim($machine['machine_image_1'], '/');
                    $machine['machine_image_1'] = $baseUrl . $machineImage1Path;
                }
            }
            
            $machines[] = $machine;
        }
        
        $response['success'] = true;
        $response['message'] = 'Machines fetched successfully';
        $response['data'] = $machines;
    } else {
        throw new Exception('Failed to fetch machines: ' . $conn->error);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['data'] = array();
}

ob_end_clean();
echo json_encode($response);
exit;






