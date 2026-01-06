<?php
/**
 * Save Operator License Details and Machine Images API
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\save_license_details.php
 */

// Suppress any warnings/errors that might output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

// Set headers first, before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
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

// Get JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON: ' . json_last_error_msg()
    ]);
    exit;
}

// Validate required fields
$operator_id = isset($input['operator_id']) ? trim($input['operator_id']) : '';
$license_no = isset($input['license_no']) ? trim($input['license_no']) : '';
$rc_number = isset($input['rc_number']) ? trim($input['rc_number']) : '';
$equipment_type = isset($input['equipment_type']) ? trim($input['equipment_type']) : '';
$machine_model = isset($input['machine_model']) ? trim($input['machine_model']) : '';
$machine_year = isset($input['machine_year']) ? trim($input['machine_year']) : '';
// Don't trim base64 images - they might be very long
$machine_image_1 = isset($input['machine_image_1']) ? $input['machine_image_1'] : '';

// Calculate category_id based on equipment_type
$category_id = null;
if (!empty($equipment_type)) {
    $equipment_type_lower = strtolower($equipment_type);
    if (strpos($equipment_type_lower, 'backhoe') !== false || strpos($equipment_type_lower, 'loader') !== false) {
        $category_id = 1; // Backhoe Loader
    } elseif (strpos($equipment_type_lower, 'excavator') !== false) {
        $category_id = 2; // Excavator
    } elseif (strpos($equipment_type_lower, 'dozer') !== false) {
        $category_id = 3; // Dozer
    }
}

if (empty($operator_id) || !is_numeric($operator_id)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'operator_id is required and must be numeric'
    ]);
    exit;
}

if (empty($license_no)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'license_no is required'
    ]);
    exit;
}

if (empty($rc_number)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'rc_number is required'
    ]);
    exit;
}

if (empty($machine_model)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'machine_model is required'
    ]);
    exit;
}

// Check if operator exists
$check_stmt = $conn->prepare("SELECT operator_id FROM operators WHERE operator_id = ?");
if (!$check_stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$check_stmt->bind_param("i", $operator_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    ob_end_clean();
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Operator not found with ID: ' . $operator_id
    ]);
    exit;
}
$check_stmt->close();

// Save image to disk and get file path
$image1_path = saveImage($machine_image_1, $operator_id, 1);

// Update operator table with license details and machine information
// This single UPDATE will trigger the database trigger to automatically link machines
$operator_id_int = (int)$operator_id;
$machine_year_int = !empty($machine_year) ? (int)$machine_year : null;

// Build the UPDATE query with all fields at once
$sql = "UPDATE operators 
    SET license_no = ?, 
        rc_number = ?,
        approve_status = 'PENDING',
        approval_pending = 1";

$params = [$license_no, $rc_number];
$types = "ss";

// Add equipment_type and category_id if provided
if (!empty($equipment_type)) {
    $sql .= ", equipment_type = ?";
    $params[] = $equipment_type;
    $types .= "s";
}

if ($category_id !== null) {
    $sql .= ", category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

// Add optional machine fields
if (!empty($machine_model)) {
    $sql .= ", machine_model = ?";
    $params[] = $machine_model;
    $types .= "s";
}

if ($machine_year_int !== null) {
    $sql .= ", machine_year = ?";
    $params[] = $machine_year_int;
    $types .= "i";
}

if ($image1_path !== null) {
    $sql .= ", machine_image_1 = ?";
    $params[] = $image1_path;
    $types .= "s";
}

$sql .= " WHERE operator_id = ?";
$params[] = $operator_id_int;
$types .= "i";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database prepare error: ' . $conn->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    $stmt->close();
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update operator: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->close();

// NOTE: The database trigger 'update_machine_from_operator' automatically links/updates machines
// The code below is kept as a backup to ensure machine linking works even if trigger fails
// This provides redundancy and ensures machines are always linked when license details are saved

// Backup: Explicitly update machines table after updating operators
// This runs after the trigger, so it acts as a safety net
// First, try to update existing machine linked to this operator
if ($category_id !== null && !empty($equipment_type)) {
    // Calculate price based on category_id
    $machine_price = null;
    if ($category_id == 1) {
        $machine_price = 1250.00;
    } elseif ($category_id == 2) {
        $machine_price = 1600.00;
    } elseif ($category_id == 3) {
        $machine_price = 1200.00;
    }
    
    // Get operator data for machine update
    $operator_data_stmt = $conn->prepare("SELECT phone, address, equipment_type, machine_model, machine_year, machine_image_1, availability, profile_image FROM operators WHERE operator_id = ?");
    if ($operator_data_stmt) {
        $operator_data_stmt->bind_param("i", $operator_id_int);
        $operator_data_stmt->execute();
        $operator_data_result = $operator_data_stmt->get_result();
        
        if ($operator_data_result && $operator_data_result->num_rows > 0) {
            $operator_data = $operator_data_result->fetch_assoc();
            
            // Try to update existing machine linked to this operator
            $update_machine_sql = "UPDATE machines 
                SET phone = ?,
                    address = ?,
                    equipment_type = ?,
                    machine_model = ?,
                    machine_year = ?,
                    machine_image_1 = ?,
                    availability = ?,
                    profile_image = ?,
                    price_per_hour = ?,
                    category_id = ?
                WHERE operator_id = ?";
            
            $update_machine_stmt = $conn->prepare($update_machine_sql);
            if ($update_machine_stmt) {
                $op_phone = $operator_data['phone'] ?? null;
                $op_address = $operator_data['address'] ?? null;
                $op_equipment_type = $operator_data['equipment_type'] ?? null;
                $op_machine_model = $operator_data['machine_model'] ?? null;
                $op_machine_year = $operator_data['machine_year'] ?? null;
                $op_machine_image_1 = $operator_data['machine_image_1'] ?? null;
                $op_availability = $operator_data['availability'] ?? null;
                $op_profile_image = $operator_data['profile_image'] ?? null;
                
                $update_machine_stmt->bind_param("ssssisssdii", 
                    $op_phone, $op_address, $op_equipment_type, $op_machine_model, 
                    $op_machine_year, $op_machine_image_1, $op_availability, $op_profile_image, 
                    $machine_price, $category_id, $operator_id_int
                );
                $update_machine_stmt->execute();
                $rows_updated = $update_machine_stmt->affected_rows;
                $update_machine_stmt->close();
                
                // If no machine is linked yet, link one from the same category
                if ($rows_updated == 0) {
                    // First, check if there are available machines in this category
                    $check_available_sql = "SELECT COUNT(*) as available_count FROM machines 
                                           WHERE category_id = ? AND operator_id IS NULL";
                    $check_stmt = $conn->prepare($check_available_sql);
                    $available_count = 0;
                    
                    if ($check_stmt) {
                        $check_stmt->bind_param("i", $category_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        if ($check_result && $check_result->num_rows > 0) {
                            $check_row = $check_result->fetch_assoc();
                            $available_count = (int)$check_row['available_count'];
                        }
                        $check_stmt->close();
                    }
                    
                    // Try to link if machines are available, otherwise CREATE a new machine
                    if ($available_count > 0) {
                        // Link an available machine
                        $link_machine_sql = "UPDATE machines 
                            SET operator_id = ?,
                                phone = ?,
                                address = ?,
                                equipment_type = ?,
                                machine_model = ?,
                                machine_year = ?,
                                machine_image_1 = ?,
                                availability = ?,
                                profile_image = ?,
                                price_per_hour = ?,
                                category_id = ?
                            WHERE category_id = ? 
                            AND operator_id IS NULL
                            LIMIT 1";
                        
                        $link_machine_stmt = $conn->prepare($link_machine_sql);
                        if ($link_machine_stmt) {
                            $link_machine_stmt->bind_param("issssisssdii",
                                $operator_id_int, $op_phone, $op_address, $op_equipment_type, 
                                $op_machine_model, $op_machine_year, $op_machine_image_1, 
                                $op_availability, $op_profile_image, $machine_price, $category_id, $category_id
                            );
                            $link_machine_stmt->execute();
                            $link_rows = $link_machine_stmt->affected_rows;
                            $link_machine_stmt->close();
                            
                            // Log if linking failed even though machines are available
                            if ($link_rows == 0) {
                                error_log("WARNING: Failed to link machine for operator_id $operator_id_int (category_id: $category_id). Available machines: $available_count");
                            }
                        }
                    }
                    
                    // If no machine was linked (no available machines), CREATE a new one
                    // Check again if operator has a machine (might have been created by trigger)
                    $check_linked_sql = "SELECT machine_id FROM machines WHERE operator_id = ? LIMIT 1";
                    $check_linked_stmt = $conn->prepare($check_linked_sql);
                    $machine_exists = false;
                    
                    if ($check_linked_stmt) {
                        $check_linked_stmt->bind_param("i", $operator_id_int);
                        $check_linked_stmt->execute();
                        $check_linked_result = $check_linked_stmt->get_result();
                        if ($check_linked_result && $check_linked_result->num_rows > 0) {
                            $machine_exists = true;
                        }
                        $check_linked_stmt->close();
                    }
                    
                    // Create machine if it doesn't exist
                    if (!$machine_exists) {
                        $create_machine_sql = "INSERT INTO machines (
                            operator_id,
                            phone,
                            address,
                            equipment_type,
                            machine_model,
                            machine_year,
                            machine_image_1,
                            availability,
                            profile_image,
                            price_per_hour,
                            category_id,
                            specs,
                            model_year,
                            image
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        
                        $create_machine_stmt = $conn->prepare($create_machine_sql);
                        if ($create_machine_stmt) {
                            $specs = $op_equipment_type; // Use equipment_type as specs
                            $model_year_for_machine = $op_machine_year; // Use machine_year as model_year
                            $image_path = $op_machine_image_1; // Use machine_image_1 as image
                            
                            $create_machine_stmt->bind_param("issssisssdiss",
                                $operator_id_int,
                                $op_phone,
                                $op_address,
                                $op_equipment_type,
                                $op_machine_model,
                                $op_machine_year,
                                $op_machine_image_1,
                                $op_availability,
                                $op_profile_image,
                                $machine_price,
                                $category_id,
                                $specs,
                                $model_year_for_machine,
                                $image_path
                            );
                            
                            if ($create_machine_stmt->execute()) {
                                error_log("SUCCESS: Created new machine for operator_id $operator_id_int (category_id: $category_id)");
                            } else {
                                error_log("ERROR: Failed to create machine for operator_id $operator_id_int: " . $create_machine_stmt->error);
                            }
                            $create_machine_stmt->close();
                        }
                    }
                }
            }
        }
        $operator_data_stmt->close();
    }
}

// If we got here, the update was successful
$conn->close();

// Clean any output before sending JSON
ob_end_clean();

http_response_code(200);
echo json_encode([
    'success' => true,
    'ok' => true,
    'message' => 'License details and machine images saved successfully',
    'operator_id' => $operator_id
], JSON_UNESCAPED_UNICODE);
exit;

// Connection closed in success/error blocks above

/**
 * Save Base64 image to disk and return file path
 */
function saveImage($base64Image, $operatorId, $imageNumber) {
    if (empty($base64Image)) {
        return null;
    }
    
    // Remove data URL prefix if present
    $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
    
    // Decode base64
    $imageData = base64_decode($base64Image);
    
    if ($imageData === false) {
        error_log("Failed to decode base64 image for operator $operatorId, image $imageNumber");
        return null;
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../uploads/machine_images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate filename
    $filename = 'machine_' . $operatorId . '_' . $imageNumber . '_' . time() . '.jpg';
    $filepath = $uploadDir . $filename;
    
    // Save file
    if (file_put_contents($filepath, $imageData)) {
        // Return relative path for database storage
        return 'uploads/machine_images/' . $filename;
    } else {
        error_log("Failed to save image file: $filepath");
        return null;
    }
}

