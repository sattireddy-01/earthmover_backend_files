<?php
/**
 * Update User Profile API
 * Handles updating user profile information including profile picture and location
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
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit();
}

// Database configuration
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'earthmover';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Logging
error_log("=== UPDATE USER PROFILE REQUEST ===");
error_log("Raw input length: " . strlen($input));

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    
    // Validate user_id
    if (!isset($data['user_id']) || empty($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        $conn->close();
        exit();
    }
    
    $user_id = intval($data['user_id']);
    
    // Build dynamic update query
    $updateFields = [];
    $updateValues = [];
    $types = '';
    
    // Name
    if (isset($data['name']) && !empty($data['name'])) {
        $updateFields[] = "name = ?";
        $updateValues[] = $data['name'];
        $types .= 's';
    }
    
    // Phone
    if (isset($data['phone']) && !empty($data['phone'])) {
        $updateFields[] = "phone = ?";
        $updateValues[] = $data['phone'];
        $types .= 's';
    }
    
    // Email
    if (isset($data['email'])) {
        $updateFields[] = "email = ?";
        $updateValues[] = empty($data['email']) ? null : $data['email'];
        $types .= 's';
    }
    
    // Address
    if (isset($data['address'])) {
        $updateFields[] = "address = ?";
        $updateValues[] = empty($data['address']) ? null : $data['address'];
        $types .= 's';
    }
    // Location (New Column)
    if (isset($data['location'])) {
        $updateFields[] = "location = ?";
        $updateValues[] = empty($data['location']) ? null : $data['location'];
        $types .= 's';
        error_log("Updating location: " . $data['location']);
    }

    // Latitude (Make sure this column exists in your `users` table)
    if (isset($data['latitude'])) {
        $updateFields[] = "latitude = ?";
        $updateValues[] = $data['latitude'];
        $types .= 'd'; // double
        error_log("Updating latitude: " . $data['latitude']);
    }

    // Longitude (Make sure this column exists in your `users` table)
    if (isset($data['longitude'])) {
        $updateFields[] = "longitude = ?";
        $updateValues[] = $data['longitude'];
        $types .= 'd'; // double
        error_log("Updating longitude: " . $data['longitude']);
    }
    
    // Profile Picture
    if (isset($data['profile_picture']) && !empty($data['profile_picture'])) {
        $profilePicturePath = saveProfilePicture($data['profile_picture'], $user_id);
        if ($profilePicturePath) {
            $updateFields[] = "profile_picture = ?";
            $updateValues[] = $profilePicturePath;
            $types .= 's';
        }
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        $conn->close();
        exit();
    }
    
    // Add WHERE clause
    $query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
    $updateValues[] = $user_id;
    $types .= 'i';
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$updateValues);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function saveProfilePicture($base64Image, $userId) {
    try {
        if (strpos($base64Image, ',') !== false) {
            $parts = explode(',', $base64Image);
            $base64Image = $parts[1];
        }
        $imageData = base64_decode($base64Image, true);
        if (!$imageData) return null;
        
        $uploadDir = __DIR__ . '/../../uploads/profiles/'; // adjusted path for api/user/
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $filename = 'user_' . $userId . '_' . time() . '.jpg';
        $filePath = $uploadDir . $filename;
        
        if (file_put_contents($filePath, $imageData)) {
            return 'uploads/profiles/' . $filename;
        }
        return null;
    } catch (Exception $e) {
        return null;
    }
}
?>