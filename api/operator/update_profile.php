<?php
/**
 * Update Operator Profile API
 * Handles updating operator profile information including profile image
 * 
 * Location: C:\xampp\htdocs\Earth_mover\api\operator\update_profile.php
 * 
 * Expected JSON Body:
 * {
 *   "operator_id": "51",
 *   "name": "Operator Name",
 *   "phone": "7675903108",
 *   "email": "operator@example.com",
 *   "address": "Operator Address",
 *   "profile_image": "base64_encoded_image_string" (optional)
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

// Database configuration
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // Default XAMPP password is empty
$DB_NAME = 'earthmover';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Enhanced logging for debugging
error_log("=== UPDATE OPERATOR PROFILE REQUEST ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log("Raw input length: " . strlen($input));
error_log("Raw input preview (first 500 chars): " . substr($input, 0, 500));

// Validate input
if (!$data) {
    error_log("ERROR: Invalid JSON data");
    error_log("JSON decode error: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data: ' . json_last_error_msg()
    ]);
    exit();
}

error_log("Decoded data keys: " . implode(', ', array_keys($data)));
error_log("Has profile_image: " . (isset($data['profile_image']) ? 'YES (length: ' . strlen($data['profile_image']) . ')' : 'NO'));

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
    // Validate operator_id
    if (!isset($data['operator_id']) || empty($data['operator_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Operator ID is required'
        ]);
        $conn->close();
        exit();
    }
    
    $operator_id = intval($data['operator_id']);
    error_log("Processing update for operator_id: $operator_id");
    
    // Check if operator exists
    $checkStmt = $conn->prepare("SELECT operator_id FROM operators WHERE operator_id = ?");
    $checkStmt->bind_param("i", $operator_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Operator not found'
        ]);
        $checkStmt->close();
        $conn->close();
        exit();
    }
    
    $checkStmt->close();
    
    // Build dynamic update query
    $updateFields = [];
    $updateValues = [];
    $types = '';
    
    // Handle name
    if (isset($data['name'])) {
        $updateFields[] = "name = ?";
        $updateValues[] = empty($data['name']) ? null : $data['name'];
        $types .= 's';
        error_log("Including name in update: " . ($data['name'] ?: 'NULL'));
    }
    
    // Handle phone
    if (isset($data['phone'])) {
        $updateFields[] = "phone = ?";
        $updateValues[] = empty($data['phone']) ? null : $data['phone'];
        $types .= 's';
        error_log("Including phone in update: " . ($data['phone'] ?: 'NULL'));
    }
    
    // Handle email
    if (isset($data['email'])) {
        $updateFields[] = "email = ?";
        $updateValues[] = empty($data['email']) ? null : $data['email'];
        $types .= 's';
        error_log("Including email in update: " . ($data['email'] ?: 'NULL'));
    }
    
    // Handle address
    if (isset($data['address'])) {
        $updateFields[] = "address = ?";
        $updateValues[] = empty($data['address']) ? null : $data['address'];
        $types .= 's';
        error_log("Including address in update: " . ($data['address'] ?: 'NULL'));
    }
    
    // Handle profile image (Base64 to file)
    $profileImagePath = null;
    $profileImageError = null;
    
    if (isset($data['profile_image']) && !empty($data['profile_image'])) {
        error_log("=== PROFILE IMAGE PROCESSING ===");
        error_log("Profile image received for operator $operator_id");
        error_log("Base64 length: " . strlen($data['profile_image']));
        error_log("Base64 preview (first 100 chars): " . substr($data['profile_image'], 0, 100));
        
        $profileImagePath = saveOperatorProfileImage($data['profile_image'], $operator_id);
        
        if ($profileImagePath) {
            error_log("Profile image saved successfully: $profileImagePath");
            $updateFields[] = "profile_image = ?";
            $updateValues[] = $profileImagePath;
            $types .= 's';
            error_log("Profile image path added to update query");
        } else {
            $profileImageError = "Failed to save profile image. Check PHP error log for details.";
            error_log("ERROR: Failed to save profile image for operator $operator_id");
            // If ONLY profile image was being updated and it failed, return error
            if (empty($updateFields)) {
                error_log("ERROR: Profile image was the only field to update and it failed");
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $profileImageError
                ]);
                $conn->close();
                exit();
            }
        }
    } else {
        error_log("No profile_image field in request data or it's empty");
        // Check if profile_image key exists but is empty
        if (isset($data['profile_image']) && empty($data['profile_image'])) {
            error_log("WARNING: profile_image field exists but is empty");
        }
    }
    
    // If no fields to update
    if (empty($updateFields)) {
        error_log("ERROR: No fields to update");
        error_log("Received data keys: " . implode(', ', array_keys($data)));
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update. Please provide at least one field to update.'
        ]);
        $conn->close();
        exit();
    }
    
    // Add operator_id for WHERE clause
    $updateValues[] = $operator_id;
    $types .= 'i';
    
    // Build and execute update query
    $sql = "UPDATE operators SET " . implode(", ", $updateFields) . " WHERE operator_id = ?";
    error_log("=== SQL UPDATE QUERY ===");
    error_log("SQL: $sql");
    error_log("Update fields count: " . count($updateFields));
    error_log("Types string: $types");
    error_log("Values count: " . count($updateValues));
    
    // Log each field being updated
    foreach ($updateFields as $index => $field) {
        $value = $updateValues[$index] ?? 'NULL';
        if (is_string($value) && strlen($value) > 100) {
            $value = substr($value, 0, 100) . '... (truncated)';
        }
        error_log("  Field $index: $field = " . $value);
    }
    
    // Specifically log profile_image if it's in the update
    if ($profileImagePath) {
        error_log("=== PROFILE IMAGE IN UPDATE ===");
        error_log("Profile image path to save: $profileImagePath");
        error_log("Profile image is in updateFields: " . (in_array("profile_image = ?", $updateFields) ? 'YES' : 'NO'));
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("ERROR: Prepare failed: " . $conn->error);
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    error_log("Binding parameters - Types: $types, Values count: " . count($updateValues));
    
    // Log the actual values being bound (for debugging)
    foreach ($updateValues as $idx => $val) {
        if (is_string($val) && strlen($val) > 50) {
            $preview = substr($val, 0, 50) . '... (length: ' . strlen($val) . ')';
        } else {
            $preview = $val ?? 'NULL';
        }
        error_log("  Value $idx: " . $preview);
    }
    
    $bindResult = $stmt->bind_param($types, ...$updateValues);
    if (!$bindResult) {
        error_log("ERROR: bind_param failed: " . $stmt->error);
        throw new Exception("bind_param failed: " . $stmt->error);
    }
    error_log("Parameters bound successfully");
    
    $executeResult = $stmt->execute();
    error_log("Execute result: " . ($executeResult ? 'SUCCESS' : 'FAILED'));
    
    if (!$executeResult) {
        error_log("ERROR: Execute failed: " . $stmt->error);
        error_log("Error number: " . $stmt->errno);
        throw new Exception("Update failed: " . $stmt->error);
    }
    
    if ($executeResult) {
        error_log("SQL UPDATE executed successfully");
        error_log("Affected rows: " . $stmt->affected_rows);
        
        // Verify the update was successful
        $verifyStmt = $conn->prepare("SELECT profile_image FROM operators WHERE operator_id = ?");
        $verifyStmt->bind_param("i", $operator_id);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        $operatorData = $verifyResult->fetch_assoc();
        $verifyStmt->close();
        
        $savedProfileImage = $operatorData['profile_image'] ?? null;
        error_log("=== DATABASE VERIFICATION ===");
        error_log("Verified profile_image in database: " . ($savedProfileImage ?? 'NULL'));
        
        // If profile image was supposed to be saved but database shows NULL, log error
        if ($profileImagePath && $savedProfileImage === null) {
            error_log("ERROR: Profile image path was set but database shows NULL!");
            error_log("  Expected: $profileImagePath");
            error_log("  Got: NULL");
            error_log("  This indicates the UPDATE query did not save the profile_image field");
            error_log("  Check if profile_image column exists and is writable");
            
            // Try to verify column exists
            $colCheck = $conn->query("SHOW COLUMNS FROM operators LIKE 'profile_image'");
            if ($colCheck && $colCheck->num_rows > 0) {
                $colInfo = $colCheck->fetch_assoc();
                error_log("  Column exists: Type=" . ($colInfo['Type'] ?? 'unknown'));
            } else {
                error_log("  ERROR: profile_image column does NOT exist in operators table!");
            }
        } else if ($profileImagePath && $savedProfileImage === $profileImagePath) {
            error_log("SUCCESS: Profile image saved correctly to database!");
        } else if ($profileImagePath) {
            error_log("WARNING: Database value doesn't match expected path!");
            error_log("  Expected: $profileImagePath");
            error_log("  Got: " . ($savedProfileImage ?? 'NULL'));
        }
        
        // Return success with profile image path if it was updated
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
        
        if ($profileImagePath) {
            $response['profile_image'] = $profileImagePath;
            $response['saved_path'] = $savedProfileImage; // Verify what was actually saved
            $response['file_saved'] = file_exists(__DIR__ . '/../' . $profileImagePath);
            error_log("Response includes profile_image: $profileImagePath");
            error_log("File exists on disk: " . ($response['file_saved'] ? 'YES' : 'NO'));
            
            // If database value doesn't match, log warning
            if ($savedProfileImage !== $profileImagePath) {
                error_log("WARNING: Database value doesn't match saved path!");
                error_log("  Expected: $profileImagePath");
                error_log("  Got: " . ($savedProfileImage ?? 'NULL'));
            }
        } else {
            error_log("No profile image path to return (saveOperatorProfileImage returned null)");
        }
        
        echo json_encode($response);
        error_log("Profile updated successfully for operator $operator_id");
    } else {
        error_log("ERROR: SQL UPDATE failed: " . $stmt->error);
        throw new Exception("Update failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("EXCEPTION: Error updating profile: " . $e->getMessage());
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
 * @param int $operatorId Operator ID for unique filename
 * @return string|null File path relative to web root, or null on failure
 */
function saveOperatorProfileImage($base64Image, $operatorId) {
    try {
        error_log("=== saveOperatorProfileImage() called ===");
        error_log("Operator ID: $operatorId");
        error_log("Base64 input length: " . strlen($base64Image));
        
        // Remove data URL prefix if present (data:image/jpeg;base64,...)
        if (strpos($base64Image, ',') !== false) {
            $parts = explode(',', $base64Image);
            $base64Image = $parts[1];
            error_log("Removed data URL prefix, remaining length: " . strlen($base64Image));
        }
        
        // Decode Base64
        $imageData = base64_decode($base64Image, true);
        
        if ($imageData === false) {
            error_log("ERROR: Failed to decode Base64 image for operator $operatorId");
            return null;
        }
        
        error_log("Base64 decoded successfully, image data size: " . strlen($imageData) . " bytes");
        
        // Validate that it's actually an image
        $imageInfo = @getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            error_log("ERROR: Decoded data is not a valid image for operator $operatorId");
            return null;
        }
        
        error_log("Image validated - Width: {$imageInfo[0]}, Height: {$imageInfo[1]}, Type: {$imageInfo[2]}");
        
        // Create uploads directory if it doesn't exist
        // Path relative to this PHP file: ../uploads/profile_images/
        // Full path: C:\xampp\htdocs\Earth_mover\uploads\profile_images\
        $uploadDir = __DIR__ . '/../uploads/profile_images/';
        
        error_log("Upload directory: $uploadDir");
        error_log("Directory exists: " . (file_exists($uploadDir) ? 'YES' : 'NO'));
        error_log("Directory is writable: " . (is_writable($uploadDir) ? 'YES' : 'NO'));
        
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
            error_log("Attempting to change permissions...");
            if (!chmod($uploadDir, 0755)) {
                error_log("ERROR: Failed to change directory permissions");
                return null;
            }
        }
        
        // Generate unique filename (matching the pattern from SQL dump: operator_51_profile_1767423272.jpg)
        $filename = 'operator_' . $operatorId . '_profile_' . time() . '.jpg';
        $filePath = $uploadDir . $filename;
        
        error_log("Saving file to: $filePath");
        
        // Save file
        $bytesWritten = file_put_contents($filePath, $imageData);
        if ($bytesWritten === false) {
            error_log("ERROR: Failed to save profile image for operator $operatorId to $filePath");
            return null;
        }
        
        error_log("Profile image saved successfully: $filePath ($bytesWritten bytes)");
        
        // Verify file was created
        if (!file_exists($filePath)) {
            error_log("ERROR: File was not created even though file_put_contents returned success");
            return null;
        }
        
        // Return relative path from web root
        // Example: uploads/profile_images/operator_51_profile_1767423272.jpg
        $relativePath = 'uploads/profile_images/' . $filename;
        error_log("Returning relative path: $relativePath");
        
        return $relativePath;
        
    } catch (Exception $e) {
        error_log("EXCEPTION in saveOperatorProfileImage: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return null;
    }
}
?>




