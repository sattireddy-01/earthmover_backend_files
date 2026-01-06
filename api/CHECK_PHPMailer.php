<?php
/**
 * Check if PHPMailer is installed
 * Access: http://localhost/Earth_mover/api/CHECK_PHPMailer.php
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>PHPMailer Installation Check</h2>";
echo "<hr>";

// Check three possible locations
// 1) api/PHPMailer/src/PHPMailer.php      (recommended)
// 2) api/../PHPMailer/src/PHPMailer.php   (project root)
// 3) api/PHPMailer/PHPMailer.php          (flat inside PHPMailer folder – your current setup)
$phpmailerPathLocal = __DIR__ . '/PHPMailer/src/PHPMailer.php';
$phpmailerPathRoot  = __DIR__ . '/../PHPMailer/src/PHPMailer.php';
$phpmailerPathFlat  = __DIR__ . '/PHPMailer/PHPMailer.php';

echo "<h3>Checking Installation Locations:</h3>";

// Location 1: Inside api/PHPMailer/src
echo "<p><strong>Location 1:</strong> <code>$phpmailerPathLocal</code></p>";
if (file_exists($phpmailerPathLocal)) {
    echo "<p style='color:green;font-size:16px;'>✓ <strong>FOUND</strong> - PHPMailer is installed here!</p>";
    
    // Check if all required files exist
    $requiredFiles = [
        'PHPMailer.php',
        'SMTP.php',
        'Exception.php'
    ];
    
    $allFilesExist = true;
    foreach ($requiredFiles as $file) {
        $filePath = __DIR__ . '/PHPMailer/src/' . $file;
        if (file_exists($filePath)) {
            echo "<p style='color:green;margin-left:30px;'>✓ $file</p>";
        } else {
            echo "<p style='color:red;margin-left:30px;'>✗ $file - MISSING</p>";
            $allFilesExist = false;
        }
    }
    
    if ($allFilesExist) {
        // Try to load PHPMailer
        try {
            require_once $phpmailerPathLocal;
            require_once __DIR__ . '/PHPMailer/src/SMTP.php';
            require_once __DIR__ . '/PHPMailer/src/Exception.php';
            
            if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                echo "<p style='color:green;font-size:18px;'><strong>✓✓✓ PHPMailer is WORKING! ✓✓✓</strong></p>";
                echo "<p>You can now send emails using Gmail SMTP.</p>";
            } else {
                echo "<p style='color:red;'>✗ PHPMailer class not found after loading</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color:red;'>✗ Error loading PHPMailer: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p style='color:red;'>✗ <strong>NOT FOUND</strong></p>";
}

echo "<hr>";

// Location 2: One level up ../PHPMailer/src
echo "<p><strong>Location 2:</strong> <code>$phpmailerPathRoot</code></p>";
if (file_exists($phpmailerPathRoot)) {
    echo "<p style='color:green;font-size:16px;'>✓ <strong>FOUND</strong> - PHPMailer is installed here!</p>";
    
    // Check if all required files exist
    $requiredFiles = [
        'PHPMailer.php',
        'SMTP.php',
        'Exception.php'
    ];
    
    $allFilesExist = true;
    foreach ($requiredFiles as $file) {
        $filePath = dirname($phpmailerPathRoot) . '/' . $file;
        if (file_exists($filePath)) {
            echo "<p style='color:green;margin-left:30px;'>✓ $file</p>";
        } else {
            echo "<p style='color:red;margin-left:30px;'>✗ $file - MISSING</p>";
            $allFilesExist = false;
        }
    }
    
    if ($allFilesExist) {
        // Try to load PHPMailer
        try {
            require_once $phpmailerPathRoot;
            require_once dirname($phpmailerPathRoot) . '/SMTP.php';
            require_once dirname($phpmailerPathRoot) . '/Exception.php';
            
            if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                echo "<p style='color:green;font-size:18px;'><strong>✓✓✓ PHPMailer is WORKING! ✓✓✓</strong></p>";
                echo "<p>You can now send emails using Gmail SMTP.</p>";
            } else {
                echo "<p style='color:red;'>✗ PHPMailer class not found after loading</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color:red;'>✗ Error loading PHPMailer: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p style='color:red;'>✗ <strong>NOT FOUND</strong></p>";
}

echo "<hr>";

// Location 3: Inside api/PHPMailer (flat)
echo "<p><strong>Location 3:</strong> <code>$phpmailerPathFlat</code></p>";
if (file_exists($phpmailerPathFlat)) {
    echo "<p style='color:green;font-size:16px;'>✓ <strong>FOUND</strong> - PHPMailer is installed here (flat)!</p>";

    // Check if all required files exist
    $requiredFiles = [
        'PHPMailer.php',
        'SMTP.php',
        'Exception.php'
    ];

    $allFilesExist = true;
    foreach ($requiredFiles as $file) {
        $filePath = __DIR__ . '/PHPMailer/' . $file;
        if (file_exists($filePath)) {
            echo "<p style='color:green;margin-left:30px;'>✓ $file</p>";
        } else {
            echo "<p style='color:red;margin-left:30px;'>✗ $file - MISSING</p>";
            $allFilesExist = false;
        }
    }

    if ($allFilesExist) {
        // Try to load PHPMailer
        try {
            require_once $phpmailerPathFlat;
            require_once __DIR__ . '/PHPMailer/SMTP.php';
            require_once __DIR__ . '/PHPMailer/Exception.php';

            if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                echo "<p style='color:green;font-size:18px;'><strong>✓✓✓ PHPMailer is WORKING! ✓✓✓</strong></p>";
                echo "<p>You can now send emails using Gmail SMTP.</p>";
            } else {
                echo "<p style='color:red;'>✗ PHPMailer class not found after loading</p>";
            }
        } catch (\Exception $e) {
            echo "<p style='color:red;'>✗ Error loading PHPMailer (flat): " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p style='color:red;'>✗ <strong>NOT FOUND</strong></p>";
}

echo "<hr>";

// Summary
if (!file_exists($phpmailerPathLocal) && !file_exists($phpmailerPathRoot) && !file_exists($phpmailerPathFlat)) {
    echo "<h3 style='color:red;'>PHPMailer is NOT INSTALLED</h3>";
    echo "<p><strong>Installation Instructions:</strong></p>";
    echo "<ol>";
    echo "<li>Download PHPMailer: <a href='https://github.com/PHPMailer/PHPMailer/releases' target='_blank'>https://github.com/PHPMailer/PHPMailer/releases</a></li>";
    echo "<li>Download the latest ZIP file (e.g., PHPMailer-6.x.x.zip)</li>";
    echo "<li>Extract the ZIP file</li>";
    echo "<li>Copy the <code>src</code> folder from the extracted PHPMailer folder</li>";
    echo "<li>Paste it to: <code>" . __DIR__ . "/PHPMailer/src/</code></li>";
    echo "<li>The final structure should be: <code>" . __DIR__ . "/PHPMailer/src/PHPMailer.php</code></li>";
    echo "</ol>";
    echo "<p><strong>Quick Path:</strong></p>";
    echo "<p><code>C:\\xampp\\htdocs\\Earth_mover\\api\\PHPMailer\\src\\</code></p>";
    echo "<p>After installation, refresh this page to verify.</p>";
} else {
    echo "<h3 style='color:green;'>PHPMailer is INSTALLED ✓</h3>";
    echo "<p><a href='TEST_EMAIL_SEND.php'>Test Email Sending</a></p>";
}

echo "<hr>";
echo "<p><a href='CHECK_EMAIL_STATUS.php'>Check Email Logs</a> | <a href='DIAGNOSE_EMAIL.php'>Full Diagnosis</a></p>";

?>
