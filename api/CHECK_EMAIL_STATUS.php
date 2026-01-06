<?php
/**
 * Check Email Status - View logs and diagnose issues
 * Access: http://localhost/Earth_mover/api/CHECK_EMAIL_STATUS.php
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Email Status Check</h2>";
echo "<hr>";

// Check email log
$emailLog = __DIR__ . '/email_log.txt';
echo "<h3>Email Log:</h3>";
if (file_exists($emailLog)) {
    $content = file_get_contents($emailLog);
    if (!empty($content)) {
        echo "<pre style='background:#f0f0f0;padding:10px;max-height:400px;overflow:auto;'>";
        echo htmlspecialchars($content);
        echo "</pre>";
    } else {
        echo "<p>Log file is empty (no email attempts yet)</p>";
    }
} else {
    echo "<p>Log file not found (no email attempts yet)</p>";
}

echo "<hr>";

// Check OTP log
$otpLog = __DIR__ . '/otp_log.txt';
echo "<h3>OTP Log (Last 10 entries):</h3>";
if (file_exists($otpLog)) {
    $content = file_get_contents($otpLog);
    if (!empty($content)) {
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -10);
        echo "<pre style='background:#f0f0f0;padding:10px;'>";
        echo htmlspecialchars(implode("\n", $lastLines));
        echo "</pre>";
    } else {
        echo "<p>OTP log is empty</p>";
    }
} else {
    echo "<p>OTP log not found</p>";
}

echo "<hr>";

// Check SMTP debug log
$smtpDebug = __DIR__ . '/smtp_debug.txt';
echo "<h3>SMTP Debug Log:</h3>";
if (file_exists($smtpDebug)) {
    $content = file_get_contents($smtpDebug);
    if (!empty($content)) {
        echo "<pre style='background:#f0f0f0;padding:10px;max-height:400px;overflow:auto;'>";
        echo htmlspecialchars($content);
        echo "</pre>";
    } else {
        echo "<p>SMTP debug log is empty</p>";
    }
} else {
    echo "<p>SMTP debug log not found (will be created on first email attempt)</p>";
}

echo "<hr>";
echo "<p><a href='TEST_EMAIL_SEND.php'>Test Email Sending</a> | <a href='DIAGNOSE_EMAIL.php'>Full Diagnosis</a></p>";

?>



































