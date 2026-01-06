<?php
/**
 * Test Script for Profile Picture Upload
 * 
 * This script tests if the backend can receive and process profile picture data
 * 
 * Usage: Open in browser: http://localhost/Earth_mover/test_profile_upload.php
 */

// A small test image (1x1 pixel red PNG) in Base64
$testBase64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==";

echo "<h1>Profile Picture Upload Test</h1>";
echo "<h2>Step 1: Test Backend File Exists</h2>";

$backendFile = __DIR__ . '/api/user/update_user_profile.php';
if (file_exists($backendFile)) {
    echo "✅ Backend file exists: $backendFile<br>";
} else {
    echo "❌ Backend file NOT found: $backendFile<br>";
    echo "Please copy update_user_profile.php to: C:\\xampp\\htdocs\\Earth_mover\\api\\user\\<br>";
    exit;
}

echo "<h2>Step 2: Test Uploads Directory</h2>";
$uploadDir = __DIR__ . '/uploads/profiles/';
if (file_exists($uploadDir)) {
    echo "✅ Upload directory exists: $uploadDir<br>";
    if (is_writable($uploadDir)) {
        echo "✅ Directory is writable<br>";
    } else {
        echo "❌ Directory is NOT writable. Please set write permissions.<br>";
    }
} else {
    echo "❌ Upload directory NOT found: $uploadDir<br>";
    echo "Creating directory...<br>";
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Directory created successfully<br>";
    } else {
        echo "❌ Failed to create directory<br>";
    }
}

echo "<h2>Step 3: Test Database Connection</h2>";
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'earthmover';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
} else {
    echo "✅ Database connected successfully<br>";
    
    // Check if profile_picture column exists
    $result = $conn->query("DESCRIBE users");
    $columnExists = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'profile_picture') {
            $columnExists = true;
            echo "✅ profile_picture column exists (Type: " . $row['Type'] . ")<br>";
            break;
        }
    }
    
    if (!$columnExists) {
        echo "❌ profile_picture column does NOT exist<br>";
        echo "Run this SQL: ALTER TABLE users ADD COLUMN profile_picture VARCHAR(500) DEFAULT NULL;<br>";
    }
    
    $conn->close();
}

echo "<h2>Step 4: Test Base64 Decode</h2>";
$imageData = base64_decode($testBase64, true);
if ($imageData !== false) {
    echo "✅ Base64 decode successful (Size: " . strlen($imageData) . " bytes)<br>";
    
    $imageInfo = @getimagesizefromstring($imageData);
    if ($imageInfo !== false) {
        echo "✅ Image validation successful (Width: {$imageInfo[0]}, Height: {$imageInfo[1]})<br>";
    } else {
        echo "❌ Image validation failed<br>";
    }
} else {
    echo "❌ Base64 decode failed<br>";
}

echo "<h2>Step 5: Test File Save</h2>";
if (file_exists($uploadDir) && is_writable($uploadDir)) {
    $testFile = $uploadDir . 'test_' . time() . '.jpg';
    $bytesWritten = file_put_contents($testFile, $imageData);
    if ($bytesWritten !== false) {
        echo "✅ File saved successfully: $testFile ($bytesWritten bytes)<br>";
        if (file_exists($testFile)) {
            echo "✅ File verified on disk<br>";
            // Clean up test file
            unlink($testFile);
            echo "✅ Test file cleaned up<br>";
        } else {
            echo "❌ File was not created on disk<br>";
        }
    } else {
        echo "❌ File save failed<br>";
    }
} else {
    echo "❌ Cannot test file save - directory not writable<br>";
}

echo "<h2>Step 6: Test API Endpoint</h2>";
$apiUrl = "http://localhost/Earth_mover/api/user/update_user_profile.php";
echo "Testing: $apiUrl<br>";

// Test with cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'user_id' => 14,
    'profile_picture' => $testBase64
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode<br>";
echo "Response: " . htmlspecialchars($response) . "<br>";

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    if ($responseData && isset($responseData['success']) && $responseData['success']) {
        echo "✅ API test successful!<br>";
        if (isset($responseData['profile_picture'])) {
            echo "✅ Profile picture path returned: " . $responseData['profile_picture'] . "<br>";
        }
    } else {
        echo "❌ API returned error: " . ($responseData['message'] ?? 'Unknown error') . "<br>";
    }
} else {
    echo "❌ API request failed with HTTP code: $httpCode<br>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "If all tests pass (✅), your backend is ready to receive profile picture uploads!<br>";
echo "If any test fails (❌), fix the issue before testing from Android app.<br>";
?>




















