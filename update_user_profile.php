<?php
/**
 * Update User Profile API - Complete Standalone Version
 * Handles updating user profile information including profile picture
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\user\update_user_profile.php
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

// Database configuration - Adjust these values to match your setup
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // Default XAMPP password is empty
$DB_NAME = 'earthmover';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log received data for debugging (remove in production)
error_log("Received data: " . print_r($data, true));

// Validate input
if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit();
}

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
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
        error_log("Profile picture received for user $user_id, length: " . strlen($data['profile_picture']));
        $profilePicturePath = saveProfilePicture($data['profile_picture'], $user_id);
        if ($profilePicturePath) {
            error_log("Profile picture saved successfully: $profilePicturePath");
            $updateFields[] = "profile_picture = ?";
            $updateValues[] = $profilePicturePath;
            $types .= 's';
        } else {
            error_log("Failed to save profile picture for user $user_id");
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
    error_log("SQL Query: $sql");
    error_log("Update values: " . print_r($updateValues, true));
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$updateValues);
    
    if ($stmt->execute()) {
        // Verify the update was successful
        $verifyStmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        $verifyStmt->bind_param("i", $user_id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $userData = $verifyResult->fetch_assoc();
        $verifyStmt->close();
        
        // Return success with profile picture path if it was updated
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
        
        if ($profilePicturePath) {
            $response['profile_picture'] = $profilePicturePath;
            $response['saved_path'] = $userData['profile_picture']; // Verify what was actually saved
        }
        
        echo json_encode($response);
        error_log("Profile updated successfully for user $user_id");
    } else {
        throw new Exception("Update failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error updating profile: " . $e->getMessage());
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
        $imageData = base64_decode($base64Image, true);
        
        if ($imageData === false) {
            error_log("Failed to decode Base64 image for user $userId");
            return null;
        }
        
        // Validate that it's actually an image
        $imageInfo = @getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            error_log("Decoded data is not a valid image for user $userId");
            return null;
        }
        
        // Create uploads directory if it doesn't exist
        // Path relative to this PHP file: ../uploads/profiles/
        // Full path: C:\xampp\htdocs\Earth_mover\uploads\profiles\
        $uploadDir = __DIR__ . '/../uploads/profiles/';
        
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create upload directory: $uploadDir");
                return null;
            }
        }
        
        // Check if directory is writable
        if (!is_writable($uploadDir)) {
            error_log("Upload directory is not writable: $uploadDir");
            return null;
        }
        
        // Generate unique filename
        $filename = 'user_' . $userId . '_' . time() . '.jpg';
        $filePath = $uploadDir . $filename;
        
        // Save file
        $bytesWritten = file_put_contents($filePath, $imageData);
        if ($bytesWritten === false) {
            error_log("Failed to save profile picture for user $userId to $filePath");
            return null;
        }
        
        error_log("Profile picture saved successfully: $filePath ($bytesWritten bytes)");
        
        // Return relative path from web root
        // Example: uploads/profiles/user_14_1234567890.jpg
        return 'uploads/profiles/' . $filename;
        
    } catch (Exception $e) {
        error_log("Error saving profile picture: " . $e->getMessage());
        return null;
    }
}
?>




















