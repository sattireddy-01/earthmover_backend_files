<?php
/**
 * Create Machine API with Image Upload Support
 * Creates a new machine in the database with optional image upload
 * Location: C:\xampp\htdocs\Earth_mover\api\admin\create_machine.php
 * 
 * Supports two methods:
 * 1. JSON (without image): POST with Content-Type: application/json
 * 2. Form-data (with image): POST with Content-Type: multipart/form-data
 * 
 * Form-data fields:
 * - category_id (required): int (price_per_hour is automatically set based on category_id)
 * - specs (optional): string
 * - model_year (optional): int
 * - image (optional): file upload
 * 
 * Note: model_name column has been removed. Price is automatically set:
 *   category_id = 1 → 1250.00
 *   category_id = 2 → 1600.00
 *   category_id = 3 → 1200.00
 */

ob_start();
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

// Determine if request is form-data (file upload) or JSON
$isFormData = !empty($_FILES) || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false);

if ($isFormData) {
    // Handle form-data (with file upload)
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $specs = isset($_POST['specs']) ? trim($_POST['specs']) : null;
    $model_year = isset($_POST['model_year']) ? (int)$_POST['model_year'] : null;
    // Price is automatically set by trigger based on category_id
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagePath = saveMachineImage($_FILES['image']);
        if ($imagePath === null) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to upload image. Please check file format and size.'
            ]);
            exit;
        }
    }
} else {
    // Handle JSON input
    $rawInput = file_get_contents('php://input');
    error_log("=== CREATE MACHINE REQUEST (JSON) ===");
    error_log("Raw input: " . substr($rawInput, 0, 500));
    
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
    
    $category_id = isset($input['category_id']) ? (int)$input['category_id'] : null;
    $specs = isset($input['specs']) ? trim($input['specs']) : null;
    $model_year = isset($input['model_year']) ? (int)$input['model_year'] : null;
    $imagePath = isset($input['image']) ? trim($input['image']) : null;
    // Price is automatically set by trigger based on category_id
}

// Validate required fields
if ($category_id === null || $category_id <= 0) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'category_id is required and must be greater than 0'
    ]);
    exit;
}

// Price is automatically set by trigger based on category_id
// category_id = 1 → 1250.00, category_id = 2 → 1600.00, category_id = 3 → 1200.00

// Insert new machine
// Note: price_per_hour is automatically set by trigger based on category_id
$stmt = $conn->prepare("INSERT INTO machines (category_id, specs, model_year, image) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("isis", 
    $category_id,
    $specs,
    $model_year,
    $imagePath
);

if ($stmt->execute()) {
    $machine_id = $conn->insert_id;
    ob_end_clean();
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'ok' => true,
        'message' => 'Machine created successfully',
        'machine_id' => $machine_id,
        'data' => [
            'machine_id' => $machine_id,
            'category_id' => $category_id,
            'price_per_hour' => ($category_id == 1 ? 1250.00 : ($category_id == 2 ? 1600.00 : 1200.00)),
            'specs' => $specs,
            'model_year' => $model_year,
            'image' => $imagePath
        ]
    ]);
} else {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'ok' => false,
        'message' => 'Failed to create machine: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

/**
 * Save uploaded machine image and return the relative path
 * 
 * @param array $file $_FILES['image'] array
 * @return string|null Relative path to image or null on failure
 */
function saveMachineImage($file) {
    try {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            error_log("ERROR: Invalid file upload");
            return null;
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("ERROR: File upload error code: " . $file['error']);
            return null;
        }
        
        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            error_log("ERROR: File too large: " . $file['size'] . " bytes");
            return null;
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            error_log("ERROR: Invalid file type: " . $mimeType);
            return null;
        }
        
        // Get file extension
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($extension), $validExtensions)) {
            error_log("ERROR: Invalid file extension: " . $extension);
            return null;
        }
        
        // Create uploads directory if it doesn't exist
        // Full path: C:\xampp\htdocs\Earth_mover\uploads\machines\
        $uploadDir = __DIR__ . '/../../uploads/machines/';
        
        error_log("Upload directory: $uploadDir");
        
        if (!file_exists($uploadDir)) {
            error_log("Creating upload directory: $uploadDir");
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("ERROR: Failed to create upload directory: $uploadDir");
                return null;
            }
            error_log("Upload directory created successfully");
        }
        
        // Check if directory is writable
        if (!is_writable($uploadDir)) {
            error_log("ERROR: Upload directory is not writable: $uploadDir");
            if (!chmod($uploadDir, 0755)) {
                error_log("ERROR: Failed to change directory permissions");
                return null;
            }
        }
        
        // Generate unique filename
        $filename = 'machine_' . time() . '_' . uniqid() . '.' . strtolower($extension);
        $filePath = $uploadDir . $filename;
        
        error_log("Moving uploaded file to: $filePath");
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            error_log("ERROR: Failed to move uploaded file");
            return null;
        }
        
        // Verify file was created
        if (!file_exists($filePath)) {
            error_log("ERROR: File was not created after move");
            return null;
        }
        
        error_log("Machine image saved successfully: $filePath");
        
        // Return relative path from web root
        // Example: uploads/machines/machine_1234567890_abc123.jpg
        $relativePath = 'uploads/machines/' . $filename;
        error_log("Returning relative path: $relativePath");
        
        return $relativePath;
        
    } catch (Exception $e) {
        error_log("EXCEPTION in saveMachineImage: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return null;
    }
}

