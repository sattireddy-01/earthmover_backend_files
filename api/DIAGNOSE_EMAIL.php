<?php
/**
 * Email Diagnosis Script
 * This will check everything needed for email to work
 * Access: http://localhost/Earth_mover/api/DIAGNOSE_EMAIL.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Email Configuration Diagnosis</h2>";
echo "<hr>";

// Check 1: PHPMailer Installation
echo "<h3>1. PHPMailer Check:</h3>";
$phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
if (file_exists($phpmailerPath)) {
    echo "<p style='color:green;'>✓ PHPMailer is installed</p>";
    try {
        require_once $phpmailerPath;
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        echo "<p style='color:green;'>✓ PHPMailer classes loaded successfully</p>";
    } catch (\Exception $e) {
        echo "<p style='color:red;'>✗ Error loading PHPMailer: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red;'>✗ PHPMailer NOT FOUND at: $phpmailerPath</p>";
    echo "<p><strong>Install PHPMailer:</strong></p>";
    echo "<ol>";
    echo "<li>Download: <a href='https://github.com/PHPMailer/PHPMailer/releases' target='_blank'>PHPMailer ZIP</a></li>";
    echo "<li>Extract and copy 'src' folder to: <code>" . __DIR__ . "/PHPMailer/src/</code></li>";
    echo "</ol>";
}

echo "<hr>";

// Check 2: Gmail Credentials
echo "<h3>2. Gmail Credentials:</h3>";
$gmailUsername = 'earthmover998@gmail.com';
$gmailPassword = 'hrgbnwarhtksgrkf';
echo "<p>Username: <strong>$gmailUsername</strong></p>";
echo "<p>Password: <strong>" . str_repeat('*', strlen($gmailPassword)) . "</strong> (16 characters)</p>";
echo "<p style='color:orange;'>⚠️ Make sure:</p>";
echo "<ul>";
echo "<li>2-Step Verification is enabled on Gmail account</li>";
echo "<li>App Password is correct (16 characters, no spaces)</li>";
echo "<li>App Password was generated for 'Mail' app</li>";
echo "</ul>";

echo "<hr>";

// Check 3: Test Email Sending
echo "<h3>3. Test Email Sending:</h3>";
$toEmail = 'bhadradrisattireddy@gmail.com';
$testOTP = '123456';

if (file_exists($phpmailerPath)) {
    try {
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $gmailUsername;
        $mail->Password = $gmailPassword;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 2; // Show debug info
        
        $mail->setFrom($gmailUsername, 'EarthMover');
        $mail->addAddress($toEmail);
        $mail->Subject = 'Test OTP - EarthMover';
        $mail->Body = "Hello,\n\nYour test OTP is: $testOTP\n\n- EarthMover Team";
        
        echo "<p>Attempting to send email to: <strong>$toEmail</strong></p>";
        echo "<pre style='background:#f0f0f0;padding:10px;'>";
        ob_start();
        $mail->send();
        $debugOutput = ob_get_clean();
        echo htmlspecialchars($debugOutput);
        echo "</pre>";
        
        echo "<p style='color:green;'><strong>✓ Email sent successfully!</strong></p>";
        echo "<p>Check your inbox: <strong>$toEmail</strong></p>";
        echo "<p>Also check <strong>spam/junk folder</strong></p>";
        
    } catch (\Exception $e) {
        echo "<p style='color:red;'><strong>✗ Email sending failed!</strong></p>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        if (isset($mail)) {
            echo "<p>PHPMailer Error Info: " . $mail->ErrorInfo . "</p>";
        }
        echo "<p><strong>Common issues:</strong></p>";
        echo "<ul>";
        echo "<li>Gmail App Password is incorrect</li>";
        echo "<li>2-Step Verification not enabled</li>";
        echo "<li>Firewall blocking port 587</li>";
        echo "<li>Gmail account security settings blocking access</li>";
        echo "</ul>";
    }
} else {
    echo "<p style='color:red;'>Cannot test - PHPMailer not installed</p>";
}

echo "<hr>";

// Check 4: Log Files
echo "<h3>4. Check Log Files:</h3>";
$otpLog = __DIR__ . '/otp_log.txt';
$emailLog = __DIR__ . '/email_log.txt';

if (file_exists($otpLog)) {
    echo "<p>✓ OTP Log exists</p>";
    $otpContent = file_get_contents($otpLog);
    if (!empty($otpContent)) {
        echo "<p>Last 5 OTP entries:</p>";
        echo "<pre style='background:#f0f0f0;padding:10px;max-height:200px;overflow:auto;'>";
        $lines = explode("\n", $otpContent);
        echo htmlspecialchars(implode("\n", array_slice($lines, -5)));
        echo "</pre>";
    }
} else {
    echo "<p>⚠️ OTP Log not found (will be created on first OTP request)</p>";
}

if (file_exists($emailLog)) {
    echo "<p>✓ Email Log exists</p>";
    $emailContent = file_get_contents($emailLog);
    if (!empty($emailContent)) {
        echo "<p>Last 5 email entries:</p>";
        echo "<pre style='background:#f0f0f0;padding:10px;max-height:200px;overflow:auto;'>";
        $lines = explode("\n", $emailContent);
        echo htmlspecialchars(implode("\n", array_slice($lines, -5)));
        echo "</pre>";
    }
} else {
    echo "<p>⚠️ Email Log not found (will be created on first email attempt)</p>";
}

echo "<hr>";
echo "<p><a href='SIMPLE_EMAIL_SEND.php'>Try Simple Email Test</a> | <a href='CHECK_PHPMailer.php'>Check PHPMailer</a></p>";

?>




































