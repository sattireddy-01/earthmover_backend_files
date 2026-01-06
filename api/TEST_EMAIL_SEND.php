<?php
/**
 * DIRECT EMAIL TEST - This will test email sending immediately
 * Access: http://localhost/Earth_mover/api/TEST_EMAIL_SEND.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Direct Email Test</h2>";
echo "<hr>";

// Gmail credentials
$gmailUsername = 'earthmover998@gmail.com';
$gmailPassword = 'hrgbnwarhtksgrkf';
$toEmail = 'bhadradrisattireddy@gmail.com'; // Change to your email
$testOTP = '123456';

echo "<p><strong>From:</strong> $gmailUsername</p>";
echo "<p><strong>To:</strong> $toEmail</p>";
echo "<p><strong>OTP:</strong> $testOTP</p>";
echo "<hr>";

// Check PHPMailer locations (same as CHECK_PHPMailer)
$phpmailerPathLocal = __DIR__ . '/PHPMailer/src/PHPMailer.php';
$phpmailerPathRoot  = __DIR__ . '/../PHPMailer/src/PHPMailer.php';
$phpmailerPathFlat  = __DIR__ . '/PHPMailer/PHPMailer.php';

echo "<h3>Checking PHPMailer:</h3>";
echo "<p>Location 1: <code>$phpmailerPathLocal</code> - " . (file_exists($phpmailerPathLocal) ? "<span style='color:green;'>✓ EXISTS</span>" : "<span style='color:red;'>✗ NOT FOUND</span>") . "</p>";
echo "<p>Location 2: <code>$phpmailerPathRoot</code> - " . (file_exists($phpmailerPathRoot) ? "<span style='color:green;'>✓ EXISTS</span>" : "<span style='color:red;'>✗ NOT FOUND</span>") . "</p>";
echo "<p>Location 3: <code>$phpmailerPathFlat</code> - " . (file_exists($phpmailerPathFlat) ? "<span style='color:green;'>✓ EXISTS</span>" : "<span style='color:red;'>✗ NOT FOUND</span>") . "</p>";

$phpmailerPath = null;
if (file_exists($phpmailerPathLocal)) {
    $phpmailerPath = $phpmailerPathLocal;
    echo "<p style='color:green;'><strong>Using:</strong> Location 1</p>";
} elseif (file_exists($phpmailerPathRoot)) {
    $phpmailerPath = $phpmailerPathRoot;
    echo "<p style='color:green;'><strong>Using:</strong> Location 2</p>";
} elseif (file_exists($phpmailerPathFlat)) {
    $phpmailerPath = $phpmailerPathFlat;
    echo "<p style='color:green;'><strong>Using:</strong> Location 3 (flat)</p>";
} else {
    echo "<p style='color:red;'><strong>ERROR:</strong> PHPMailer not found in any location!</p>";
    echo "<p>Please make sure PHPMailer.php, SMTP.php and Exception.php are inside <code>C:\\xampp\\htdocs\\Earth_mover\\api\\PHPMailer\\</code></p>";
    exit;
}

echo "<hr>";

// Try to send email
try {
    require_once $phpmailerPath;
    require_once dirname($phpmailerPath) . '/SMTP.php';
    require_once dirname($phpmailerPath) . '/Exception.php';
    
    echo "<p style='color:green;'>✓ PHPMailer classes loaded</p>";
    
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    // Enable verbose debug output
    $mail->SMTPDebug = 2; // Show detailed debug info
    $mail->Debugoutput = function($str, $level) {
        echo "<pre style='background:#f0f0f0;padding:5px;margin:5px 0;'>" . htmlspecialchars($str) . "</pre>";
    };
    
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $gmailUsername;
    $mail->Password   = $gmailPassword;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    
    // Email content
    $mail->setFrom($gmailUsername, 'EarthMover');
    $mail->addAddress($toEmail);
    $mail->Subject = 'Test OTP - EarthMover';
    $mail->Body    = "Hello,\n\nYour test OTP is: $testOTP\n\n- EarthMover Team";
    $mail->isHTML(false);
    
    echo "<h3>Sending Email...</h3>";
    $mail->send();
    
    echo "<p style='color:green;font-size:18px;'><strong>✓✓✓ EMAIL SENT SUCCESSFULLY! ✓✓✓</strong></p>";
    echo "<p>Check your inbox: <strong>$toEmail</strong></p>";
    echo "<p>Also check <strong>spam/junk folder</strong></p>";
    
} catch (\Exception $e) {
    echo "<p style='color:red;font-size:18px;'><strong>✗✗✗ EMAIL FAILED ✗✗✗</strong></p>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    if (isset($mail)) {
        echo "<p><strong>PHPMailer Error Info:</strong> " . $mail->ErrorInfo . "</p>";
    }
    echo "<hr>";
    echo "<h3>Common Issues:</h3>";
    echo "<ul>";
    echo "<li><strong>Wrong App Password:</strong> Make sure you're using a Gmail App Password (16 characters), not your regular Gmail password</li>";
    echo "<li><strong>2-Step Verification:</strong> Must be enabled on Gmail account</li>";
    echo "<li><strong>App Password not generated:</strong> Go to Google Account → Security → App Passwords → Generate new password for 'Mail'</li>";
    echo "<li><strong>Firewall:</strong> Port 587 might be blocked</li>";
    echo "</ul>";
}

?>

