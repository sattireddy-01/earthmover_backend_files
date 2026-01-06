<?php
/**
 * Update User Profile API
 * Handles updating user profile information including profile picture
 * 
 * Endpoint: POST /api/user/update_user_profile.php
 * 
 * Expected JSON Body:
 * {
 *   "user_id": 14,
 *   "name": "User Name",
 *   "phone": "7995778148",
 *   "email": "user@example.com",
 *   "address": "User Address",
 *   "profile_picture": "base64_encoded_image_string" (optional)
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit();
}

// Database configuration
require_once '../config/database.php'; // Adjust path as needed

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Validate user_id
    if (!isset($data['user_id']) || empty($data['user_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required'
        ]);
        $conn->close();
        exit();
    }
    
    $user_id = intval($data['user_id']);
    
    // Check if user exists
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();
    
    // Build update query dynamically based on provided fields
    $updateFields = [];
    $updateValues = [];
    $types = '';
    
    // Handle name
    if (isset($data['name']) && !empty($data['name'])) {
        $updateFields[] = "name = ?";
        $updateValues[] = $data['name'];
        $types .= 's';
    }
    
    // Handle phone
    if (isset($data['phone']) && !empty($data['phone'])) {
        $updateFields[] = "phone = ?";
        $updateValues[] = $data['phone'];
        $types .= 's';
    }
    
    // Handle email
    if (isset($data['email'])) {
        $updateFields[] = "email = ?";
        $updateValues[] = empty($data['email']) ? null : $data['email'];
        $types .= 's';
    }
    
    // Handle address
    if (isset($data['address'])) {
        $updateFields[] = "address = ?";
        $updateValues[] = empty($data['address']) ? null : $data['address'];
        $types .= 's';
    }
    
    // Handle profile picture (Base64 to file)
    $profilePicturePath = null;
    if (isset($data['profile_picture']) && !empty($data['profile_picture'])) {
        $profilePicturePath = saveProfilePicture($data['profile_picture'], $user_id);
        if ($profilePicturePath) {
            $updateFields[] = "profile_picture = ?";
            $updateValues[] = $profilePicturePath;
            $types .= 's';
        }
    }
    
    // If no fields to update
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update'
        ]);
        $conn->close();
        exit();
    }
    
    // Add user_id for WHERE clause
    $updateValues[] = $user_id;
    $types .= 'i';
    
    // Build and execute update query
    $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$updateValues);
    
    if ($stmt->execute()) {
        // Return success with profile picture path if it was updated
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
        
        if ($profilePicturePath) {
            $response['profile_picture'] = $profilePicturePath;
        }
        
        echo json_encode($response);
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Save Base64 encoded image to file and return the path
 * 
 * @param string $base64Image Base64 encoded image string
 * @param int $userId User ID for unique filename
 * @return string|null File path relative to web root, or null on failure
 */
function saveProfilePicture($base64Image, $userId) {
    try {
        // Remove data URL prefix if present (data:image/jpeg;base64,...)
        if (strpos($base64Image, ',') !== false) {
            $base64Image = explode(',', $base64Image)[1];
        }
        
        // Decode Base64
        $imageData = base64_decode($base64Image);
        
        if ($imageData === false) {
            error_log("Failed to decode Base64 image for user $userId");
            return null;
        }
        
        // Create uploads directory if it doesn't exist
        $uploadDir = '../uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = 'user_' . $userId . '_' . time() . '.jpg';
        $filePath = $uploadDir . $filename;
        
        // Save file
        if (file_put_contents($filePath, $imageData) === false) {
            error_log("Failed to save profile picture for user $userId");
            return null;
        }
        
        // Return relative path from web root (adjust based on your structure)
        // Example: uploads/profiles/user_14_1234567890.jpg
        return 'uploads/profiles/' . $filename;
        
    } catch (Exception $e) {
        error_log("Error saving profile picture: " . $e->getMessage());
        return null;
    }
}
?>




















