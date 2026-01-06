<?php
/**
 * Test Email Script
 * Run this to test if email sending works with your Gmail credentials
 * Access: http://localhost/Earth_mover/api/TEST_EMAIL.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gmail credentials
$gmailUsername = 'earthmover998@gmail.com';
$gmailPassword = 'hrgbnwarhtksgrkf'; // App Password (16 chars, no spaces)

// Test email
$testEmail = 'earthmover998@gmail.com'; // Change to your test email
$testOTP = '123456';

echo "<h2>Testing Email Configuration</h2>";
echo "<p>From: $gmailUsername</p>";
echo "<p>To: $testEmail</p>";
echo "<p>OTP: $testOTP</p>";
echo "<hr>";

// Check if PHPMailer exists
$phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
$phpmailerExists = file_exists($phpmailerPath);

if (!$phpmailerExists) {
    echo "<p style='color: red;'><strong>ERROR:</strong> PHPMailer not found!</p>";
    echo "<p>Download PHPMailer from: <a href='https://github.com/PHPMailer/PHPMailer/releases' target='_blank'>https://github.com/PHPMailer/PHPMailer/releases</a></p>";
    echo "<p>Extract and copy 'src' folder to: " . __DIR__ . "/PHPMailer/src/</p>";
    exit;
}

echo "<p style='color: green;'>✓ PHPMailer found</p>";

try {
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);
    
    // SMTP Configuration for Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $gmailUsername;
    $mail->Password   = $gmailPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    $mail->SMTPDebug  = 2; // Enable verbose debug output
    
    // Email content
    $mail->setFrom($gmailUsername, 'EarthMover');
    $mail->addAddress($testEmail);
    $mail->Subject = 'Test Email - Password Reset OTP';
    $mail->Body    = "Hello,\n\nThis is a test email.\n\nYour test OTP is: $testOTP\n\nIf you received this, email configuration is working!\n\n- EarthMover Team";
    $mail->isHTML(false);
    
    echo "<p>Attempting to send email...</p>";
    echo "<pre>";
    ob_start();
    $mail->send();
    $debugOutput = ob_get_clean();
    echo htmlspecialchars($debugOutput);
    echo "</pre>";
    
    echo "<p style='color: green;'><strong>SUCCESS!</strong> Email sent successfully!</p>";
    echo "<p>Check your inbox: <strong>$testEmail</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> {$mail->ErrorInfo}</p>";
    echo "<p>Common issues:</p>";
    echo "<ul>";
    echo "<li>Check Gmail App Password is correct (16 characters, no spaces)</li>";
    echo "<li>Verify 2-Step Verification is enabled on Gmail account</li>";
    echo "<li>Check firewall isn't blocking port 587</li>";
    echo "<li>Verify Gmail account: $gmailUsername</li>";
    echo "</ul>";
}

?>




































