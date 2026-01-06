<?php
/**
 * Simple Email Sending Function for OTP
 * This uses PHP mail() function which works if mail server is configured
 * For Gmail, use PHPMailer instead (see request_password_reset.php)
 */

function sendEmailOTP($toEmail, $otp, $role) {
    $fromEmail = 'noreply@earthmover.com'; // Change this
    $fromName = 'EarthMover';
    $subject = 'Password Reset OTP - EarthMover';
    
    $message = "Hello,\n\n";
    $message .= "Your OTP for password reset is: $otp\n\n";
    $message .= "Valid for 5 minutes.\n\n";
    $message .= "If you didn't request this, please ignore this email.\n\n";
    $message .= "- EarthMover Team";
    
    $headers = "From: $fromName <$fromEmail>\r\n";
    $headers .= "Reply-To: $fromEmail\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Log email attempt
    $emailLog = date('Y-m-d H:i:s') . " - Attempting to send email to: $toEmail, OTP: $otp\n";
    @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
    
    $mailResult = @mail($toEmail, $subject, $message, $headers);
    
    if ($mailResult) {
        $emailLog = date('Y-m-d H:i:s') . " - Email sent successfully to: $toEmail\n";
        @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
        return true;
    } else {
        $emailLog = date('Y-m-d H:i:s') . " - Email failed to send to: $toEmail\n";
        $emailLog .= "  → PHP mail() requires mail server configuration\n";
        $emailLog .= "  → Use PHPMailer with Gmail SMTP for reliable delivery\n";
        @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
        return false;
    }
}




































