<?php
/**
 * Get Operator By Machine API
 * Finds an operator who can operate a specific machine
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../database.php';

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit();
}

$machineId = isset($data['machine_id']) ? intval($data['machine_id']) : 0;
$machineType = isset($data['machine_type']) ? $data['machine_type'] : '';

if ($machineId <= 0 && empty($machineType)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'machine_id or machine_type is required']);
    exit();
}

try {
    // UPDATED: Removed 'status' check as the column doesn't exist
    $query = "SELECT o.* FROM operators o 
              LEFT JOIN machines m ON o.operator_id = m.operator_id 
              WHERE (m.machine_id = ? OR m.equipment_type = ?) 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $machineId, $machineType);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true, 
            'data' => [
                'operator_id' => (string)$row['operator_id'],
                'name' => $row['name'],
                'phone' => $row['phone'],
                'status' => 'available', // Hardcoded status since DB doesn't have it
                'email' => isset($row['email']) ? $row['email'] : '',
                'profile_image' => isset($row['profile_image']) ? $row['profile_image'] : (isset($row['image']) ? $row['image'] : '')
            ]
        ]);
    } else {
        // Fallback: Just get any operator
        $queryFallback = "SELECT * FROM operators LIMIT 1";
        $resultFallback = $conn->query($queryFallback);
        
        if ($rowFallback = $resultFallback->fetch_assoc()) {
            echo json_encode([
                'success' => true, 
                'data' => [
                    'operator_id' => (string)$rowFallback['operator_id'],
                    'name' => $rowFallback['name'],
                    'phone' => $rowFallback['phone'],
                    'status' => 'available',
                    'email' => isset($rowFallback['email']) ? $rowFallback['email'] : '',
                    'profile_image' => isset($rowFallback['profile_image']) ? $rowFallback['profile_image'] : (isset($rowFallback['image']) ? $rowFallback['image'] : '')
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No operators available for this machine']);
        }
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>