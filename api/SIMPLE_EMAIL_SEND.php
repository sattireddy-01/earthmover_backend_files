<?php
/**
 * Simple Email Sending Test
 * This will send a test email using Gmail SMTP
 * Access: http://localhost/Earth_mover/api/SIMPLE_EMAIL_SEND.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gmail credentials
$gmailUsername = 'earthmover998@gmail.com';
$gmailPassword = 'hrgbnwarhtksgrkf'; // App Password (no spaces)

// Test email
$toEmail = 'bhadradrisattireddy@gmail.com'; // Change to your email
$testOTP = '123456';

echo "<h2>Simple Email Test</h2>";
echo "<p>From: $gmailUsername</p>";
echo "<p>To: $toEmail</p>";
echo "<p>OTP: $testOTP</p>";
echo "<hr>";

// Method 1: Try PHPMailer first
$phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';

if (file_exists($phpmailerPath)) {
    echo "<p>Using PHPMailer...</p>";
    try {
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Gmail SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $gmailUsername;
        $mail->Password = $gmailPassword;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom($gmailUsername, 'EarthMover');
        $mail->addAddress($toEmail);
        $mail->Subject = 'Test OTP - EarthMover';
        $mail->Body = "Hello,\n\nYour test OTP is: $testOTP\n\n- EarthMover Team";
        
        $mail->send();
        echo "<p style='color:green;'><strong>SUCCESS!</strong> Email sent via PHPMailer!</p>";
        echo "<p>Check your inbox: <strong>$toEmail</strong></p>";
        echo "<p>Also check spam/junk folder</p>";
        
    } catch (\Exception $e) {
        echo "<p style='color:red;'><strong>PHPMailer Error:</strong> " . $e->getMessage() . "</p>";
        echo "<p>Error Info: " . (isset($mail) ? $mail->ErrorInfo : 'N/A') . "</p>";
    }
} else {
    echo "<p style='color:red;'><strong>PHPMailer NOT FOUND!</strong></p>";
    echo "<p>Install PHPMailer:</p>";
    echo "<ol>";
    echo "<li>Download: <a href='https://github.com/PHPMailer/PHPMailer/releases' target='_blank'>PHPMailer</a></li>";
    echo "<li>Extract and copy 'src' folder to: <code>" . __DIR__ . "/PHPMailer/src/</code></li>";
    echo "</ol>";
}

?>




































