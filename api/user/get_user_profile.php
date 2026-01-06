<?php
/**
 * Get User Profile API
 * Returns user profile information from the users table
 * Location: C:\xampp\htdocs\Earth_mover\api\user\get_user_profile.php
 */

error_reporting(0);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only GET method allowed'], JSON_UNESCAPED_UNICODE);
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

if (!isset($conn) || $conn === null || ($conn instanceof mysqli && $conn->connect_error)) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . ($conn instanceof mysqli ? $conn->connect_error : 'Connection not available')
    ]);
    exit;
}

// Get user_id from query parameter
$userId = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';

if (empty($userId) || !is_numeric($userId)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Valid user_id is required'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$response = array('success' => false, 'message' => '', 'data' => null);

try {
    // Fetch user profile from database
    $query = "SELECT 
                user_id,
                name,
                phone,
                email,
                address,
                location,
                profile_picture,
                created_at
              FROM users
              WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare query: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Format the response
        $userData = array(
            'user_id' => (int)$user['user_id'],
            'name' => $user['name'] ?? '',
            'phone' => $user['phone'] ?? '',
            'email' => $user['email'] ?? null,
            'address' => $user['address'] ?? null,
            'location' => $user['location'] ?? null,
            'profile_picture' => $user['profile_picture'] ?? null,
            'created_at' => $user['created_at'] ?? null
        );
        
        $response['success'] = true;
        $response['message'] = 'User profile fetched successfully';
        $response['data'] = $userData;
    } else {
        $response['success'] = false;
        $response['message'] = 'User not found';
        $response['data'] = null;
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['data'] = null;
}

$conn->close();

ob_end_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
