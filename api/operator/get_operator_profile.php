<?php
/**
 * Get Operator Profile API
 * Returns operator profile information including profile image
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\get_operator_profile.php
 * 
 * Query Parameters:
 *   operator_id: The ID of the operator
 * 
 * Returns JSON:
 * {
 *   "success": true,
 *   "data": {
 *     "operator_id": "51",
 *     "name": "Operator Name",
 *     "phone": "7675903108",
 *     "email": "operator@example.com",
 *     "address": "Operator Address",
 *     "profile_image": "uploads/profile_images/operator_51_profile_1767423272.jpg",
 *     ...
 *   }
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET.'
    ]);
    exit();
}

// Database configuration
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // Default XAMPP password is empty
$DB_NAME = 'earthmover';

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Get operator_id from query parameters
    if (!isset($_GET['operator_id']) || empty($_GET['operator_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Operator ID is required'
        ]);
        $conn->close();
        exit();
    }
    
    $operator_id = intval($_GET['operator_id']);
    error_log("Fetching operator profile for operator_id: $operator_id");
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT 
        operator_id,
        name,
        phone,
        email,
        address,
        license_no,
        rc_number,
        equipment_type,
        category_id,
        machine_model,
        machine_year,
        machine_image_1,
        approve_status,
        approval_pending,
        availability,
        profile_image,
        created_at
        FROM operators 
        WHERE operator_id = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $operator_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Operator not found'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    
    $operator = $result->fetch_assoc();
    $stmt->close();
    
    // Build response data
    $responseData = [
        'operator_id' => (string)$operator['operator_id'],
        'name' => $operator['name'] ?? null,
        'phone' => $operator['phone'] ?? null,
        'email' => $operator['email'] ?? null,
        'address' => $operator['address'] ?? null,
        'license_number' => $operator['license_no'] ?? null,
        'rc_number' => $operator['rc_number'] ?? null,
        'equipment_type' => $operator['equipment_type'] ?? null,
        'category_id' => $operator['category_id'] ?? null,
        'machine_model' => $operator['machine_model'] ?? null,
        'machine_year' => $operator['machine_year'] ?? null,
        'machine_image_1' => $operator['machine_image_1'] ?? null,
        'approve_status' => $operator['approve_status'] ?? null,
        'approval_pending' => $operator['approval_pending'] ?? null,
        'availability' => $operator['availability'] ?? null,
        'profile_image' => $operator['profile_image'] ?? null, // This is the key field
        'created_at' => $operator['created_at'] ?? null
    ];
    
    // Log profile_image for debugging
    error_log("Operator profile fetched. Profile image: " . ($responseData['profile_image'] ?? 'NULL'));
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Operator profile retrieved successfully',
        'data' => $responseData
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("EXCEPTION: Error fetching operator profile: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>





