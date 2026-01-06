<?php
/**
 * Update User Profile API - Complete Standalone Version
 * Handles updating user profile information including profile picture
 * 
 * COPY THIS FILE TO: C:\xampp\htdocs\Earth_mover\api\user\update_user_profile.php
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

// Log received data for debugging
error_log("=== UPDATE USER PROFILE REQUEST ===");
error_log("Raw input length: " . strlen($input));
error_log("Decoded data: " . print_r($data, true));

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
    error_log("Processing update for user_id: $user_id");
    
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
        error_log("Updating name: " . $data['name']);
    }
    
    // Handle phone
    if (isset($data['phone']) && !empty($data['phone'])) {
        $updateFields[] = "phone = ?";
        $updateValues[] = $data['phone'];
        $types .= 's';
        error_log("Updating phone: " . $data['phone']);
    }
    
    // Handle email
    if (isset($data['email'])) {
        $updateFields[] = "email = ?";
        $updateValues[] = empty($data['email']) ? null : $data['email'];
        $types .= 's';
        error_log("Updating email: " . ($data['email'] ?: 'NULL'));
    }
    
    // Handle address
    if (isset($data['address'])) {
        $updateFields[] = "address = ?";
        $updateValues[] = empty($data['address']) ? null : $data['address'];
        $types .= 's';
        error_log("Updating address: " . ($data['address'] ?: 'NULL'));
    }
    
    // Handle profile picture (Base64 to file)
    $profilePicturePath = null;
    if (isset($data['profile_picture']) && !empty($data['profile_picture'])) {
        error_log("Profile picture received for user $user_id");
        error_log("Base64 length: " . strlen($data['profile_picture']));
        $profilePicturePath = saveProfilePicture($data['profile_picture'], $user_id);
        if ($profilePicturePath) {
            error_log("Profile picture saved successfully: $profilePicturePath");
            $updateFields[] = "profile_picture = ?";
            $updateValues[] = $profilePicturePath;
            $types .= 's';
        } else {
            error_log("ERROR: Failed to save profile picture for user $user_id");
        }
    } else {
        error_log("No profile_picture field in request data");
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
    error_log("Update fields count: " . count($updateFields));
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$updateValues);
    
    if ($stmt->execute()) {
        // Verify the update was successful by querying the database
        $verifyStmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        $verifyStmt->bind_param("i", $user_id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $userData = $verifyResult->fetch_assoc();
        $verifyStmt->close();
        
        error_log("Database verification - profile_picture value: " . ($userData['profile_picture'] ?: 'NULL'));
        
        // Return success with profile picture path if it was updated
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
        
        if ($profilePicturePath) {
            $response['profile_picture'] = $profilePicturePath;
            $response['saved_in_db'] = $userData['profile_picture'];
        }
        
        echo json_encode($response);
        error_log("Profile updated successfully for user $user_id");
    } else {
        error_log("SQL Execute failed: " . $stmt->error);
        throw new Exception("Update failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("EXCEPTION in update_user_profile.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
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
        error_log("saveProfilePicture called for user $userId");
        
        // Remove data URL prefix if present (data:image/jpeg;base64,...)
        if (strpos($base64Image, ',') !== false) {
            $parts = explode(',', $base64Image);
            $base64Image = $parts[1];
            error_log("Removed data URL prefix, remaining length: " . strlen($base64Image));
        }
        
        // Decode Base64
        $imageData = base64_decode($base64Image, true);
        
        if ($imageData === false) {
            error_log("ERROR: Failed to decode Base64 image for user $userId");
            return null;
        }
        
        error_log("Base64 decoded successfully, image data size: " . strlen($imageData) . " bytes");
        
        // Validate that it's actually an image
        $imageInfo = @getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            error_log("ERROR: Decoded data is not a valid image for user $userId");
            return null;
        }
        
        error_log("Image validated - Width: {$imageInfo[0]}, Height: {$imageInfo[1]}, Type: {$imageInfo[2]}");
        
        // Create uploads directory if it doesn't exist
        // Path relative to this PHP file: ../uploads/profiles/
        // Full path: C:\xampp\htdocs\Earth_mover\uploads\profiles\
        $uploadDir = __DIR__ . '/../uploads/profiles/';
        
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
            // Try to change permissions
            @chmod($uploadDir, 0755);
            if (!is_writable($uploadDir)) {
                return null;
            }
        }
        
        // Generate unique filename
        $filename = 'user_' . $userId . '_' . time() . '.jpg';
        $filePath = $uploadDir . $filename;
        
        error_log("Saving file to: $filePath");
        
        // Save file
        $bytesWritten = file_put_contents($filePath, $imageData);
        if ($bytesWritten === false) {
            error_log("ERROR: Failed to save profile picture for user $userId to $filePath");
            return null;
        }
        
        error_log("SUCCESS: Profile picture saved - File: $filePath, Size: $bytesWritten bytes");
        
        // Verify file was created
        if (!file_exists($filePath)) {
            error_log("ERROR: File was not created even though file_put_contents returned success");
            return null;
        }
        
        // Return relative path from web root
        // Example: uploads/profiles/user_14_1234567890.jpg
        $relativePath = 'uploads/profiles/' . $filename;
        error_log("Returning relative path: $relativePath");
        
        return $relativePath;
        
    } catch (Exception $e) {
        error_log("EXCEPTION in saveProfilePicture: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return null;
    }
}
?>




















