<?php
/**
 * XAMPP Setup Verification Script
 * 
 * This script checks if all required files and directories exist in XAMPP
 * and creates them if they don't exist.
 * 
 * PLACE THIS FILE IN: C:\xampp\htdocs\Earth_mover\verify_xampp_setup.php
 * Then open in browser: http://localhost/Earth_mover/verify_xampp_setup.php
 */

$baseDir = __DIR__; // C:\xampp\htdocs\Earth_mover
$errors = [];
$warnings = [];
$success = [];

echo "<h1>XAMPP Setup Verification for Earth Mover</h1>";
echo "<h2>Base Directory: $baseDir</h2>";
echo "<hr>";

// ============================================
// 1. Check API Directory Structure
// ============================================
echo "<h2>1. API Directory Structure</h2>";

$apiDir = $baseDir . '/api';
if (!file_exists($apiDir)) {
    if (mkdir($apiDir, 0755, true)) {
        $success[] = "Created API directory: $apiDir";
        echo "✅ Created: $apiDir<br>";
    } else {
        $errors[] = "Failed to create API directory: $apiDir";
        echo "❌ Failed to create: $apiDir<br>";
    }
} else {
    echo "✅ API directory exists: $apiDir<br>";
}

$apiUserDir = $baseDir . '/api/user';
if (!file_exists($apiUserDir)) {
    if (mkdir($apiUserDir, 0755, true)) {
        $success[] = "Created API user directory: $apiUserDir";
        echo "✅ Created: $apiUserDir<br>";
    } else {
        $errors[] = "Failed to create API user directory: $apiUserDir";
        echo "❌ Failed to create: $apiUserDir<br>";
    }
} else {
    echo "✅ API user directory exists: $apiUserDir<br>";
}

// ============================================
// 2. Check update_user_profile.php
// ============================================
echo "<h2>2. Backend PHP Files</h2>";

$updateProfileFile = $apiUserDir . '/update_user_profile.php';
if (!file_exists($updateProfileFile)) {
    $warnings[] = "update_user_profile.php is missing. You need to copy it from your project.";
    echo "❌ Missing: $updateProfileFile<br>";
    echo "   <strong>Action Required:</strong> Copy from your Android project:<br>";
    echo "   FROM: C:\\Users\\bhadr\\AndroidStudioProjects\\Eathmover\\api\\user\\update_user_profile.php<br>";
    echo "   TO: $updateProfileFile<br>";
} else {
    echo "✅ File exists: $updateProfileFile<br>";
    
    // Check if file has content
    $fileSize = filesize($updateProfileFile);
    if ($fileSize > 0) {
        echo "   File size: " . number_format($fileSize) . " bytes<br>";
        
        // Check for key functions
        $content = file_get_contents($updateProfileFile);
        if (strpos($content, 'saveProfilePicture') !== false) {
            echo "   ✅ Contains saveProfilePicture function<br>";
        } else {
            $warnings[] = "File exists but may be incomplete (missing saveProfilePicture function)";
            echo "   ⚠️ File may be incomplete<br>";
        }
    } else {
        $errors[] = "File exists but is empty";
        echo "   ❌ File is empty!<br>";
    }
}

// ============================================
// 3. Check Uploads Directory
// ============================================
echo "<h2>3. Uploads Directory</h2>";

$uploadsDir = $baseDir . '/uploads';
if (!file_exists($uploadsDir)) {
    if (mkdir($uploadsDir, 0755, true)) {
        $success[] = "Created uploads directory: $uploadsDir";
        echo "✅ Created: $uploadsDir<br>";
    } else {
        $errors[] = "Failed to create uploads directory: $uploadsDir";
        echo "❌ Failed to create: $uploadsDir<br>";
    }
} else {
    echo "✅ Uploads directory exists: $uploadsDir<br>";
}

$profilesDir = $baseDir . '/uploads/profiles';
if (!file_exists($profilesDir)) {
    if (mkdir($profilesDir, 0755, true)) {
        $success[] = "Created profiles directory: $profilesDir";
        echo "✅ Created: $profilesDir<br>";
    } else {
        $errors[] = "Failed to create profiles directory: $profilesDir";
        echo "❌ Failed to create: $profilesDir<br>";
    }
} else {
    echo "✅ Profiles directory exists: $profilesDir<br>";
}

// Check write permissions
if (file_exists($profilesDir)) {
    if (is_writable($profilesDir)) {
        echo "✅ Directory is writable<br>";
        
        // Test write
        $testFile = $profilesDir . '/test_write_' . time() . '.txt';
        if (file_put_contents($testFile, 'test') !== false) {
            echo "✅ Write test successful<br>";
            unlink($testFile); // Clean up
        } else {
            $errors[] = "Directory exists but cannot write files";
            echo "❌ Write test failed<br>";
        }
    } else {
        $warnings[] = "Directory exists but is not writable. Set permissions to 755 or 777";
        echo "⚠️ Directory is NOT writable<br>";
        echo "   <strong>Fix:</strong> Right-click folder → Properties → Security → Allow Write<br>";
        
        // Try to fix permissions
        if (chmod($profilesDir, 0755)) {
            echo "   ✅ Attempted to fix permissions (755)<br>";
            if (is_writable($profilesDir)) {
                echo "   ✅ Permissions fixed! Directory is now writable<br>";
            }
        }
    }
}

// ============================================
// 4. Check Database Connection
// ============================================
echo "<h2>4. Database Connection</h2>";

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'earthmover';

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    $errors[] = "Database connection failed: " . $conn->connect_error;
    echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
} else {
    echo "✅ Database connected successfully<br>";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Users table exists<br>";
        
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
            $warnings[] = "profile_picture column does not exist in users table";
            echo "⚠️ profile_picture column does NOT exist<br>";
            echo "   <strong>Fix:</strong> Run this SQL in phpMyAdmin:<br>";
            echo "   <code>ALTER TABLE users ADD COLUMN profile_picture VARCHAR(500) DEFAULT NULL;</code><br>";
        }
    } else {
        $errors[] = "Users table does not exist";
        echo "❌ Users table does NOT exist<br>";
    }
    
    $conn->close();
}

// ============================================
// 5. Summary
// ============================================
echo "<hr>";
echo "<h2>Summary</h2>";

if (count($success) > 0) {
    echo "<h3 style='color: green;'>✅ Successfully Created:</h3>";
    echo "<ul>";
    foreach ($success as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
}

if (count($warnings) > 0) {
    echo "<h3 style='color: orange;'>⚠️ Warnings:</h3>";
    echo "<ul>";
    foreach ($warnings as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
}

if (count($errors) > 0) {
    echo "<h3 style='color: red;'>❌ Errors:</h3>";
    echo "<ul>";
    foreach ($errors as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
}

if (count($errors) == 0 && count($warnings) == 0) {
    echo "<h3 style='color: green;'>✅ All checks passed! Your setup is ready.</h3>";
} else if (count($errors) == 0) {
    echo "<h3 style='color: orange;'>⚠️ Setup is mostly complete, but please address the warnings above.</h3>";
} else {
    echo "<h3 style='color: red;'>❌ Please fix the errors above before testing profile picture upload.</h3>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
if (!file_exists($updateProfileFile)) {
    echo "<li><strong>Copy update_user_profile.php</strong> from your Android project to: $updateProfileFile</li>";
}
if (count($warnings) > 0 || count($errors) > 0) {
    echo "<li><strong>Fix the warnings/errors</strong> listed above</li>";
}
echo "<li><strong>Test profile picture upload</strong> from your Android app</li>";
echo "<li><strong>Check logs:</strong><ul>";
echo "<li>Android Logcat (filter: EditProfileActivity)</li>";
echo "<li>PHP Error Log: C:\\xampp\\php\\logs\\php_error_log</li>";
echo "</ul></li>";
echo "</ol>";
?>




















