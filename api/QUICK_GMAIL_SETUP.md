# Quick Gmail Setup for OTP Emails

## Problem
PHP `mail()` function doesn't work on localhost/XAMPP. You need to use Gmail SMTP to send emails.

## Solution: Use PHPMailer with Gmail

### Step 1: Download PHPMailer
1. Go to: https://github.com/PHPMailer/PHPMailer/releases
2. Download the latest version (ZIP file)
3. Extract it
4. Copy the `src` folder contents to: `C:\xampp\htdocs\Earth_mover\api\PHPMailer\`

**OR** use Composer:
```bash
cd C:\xampp\htdocs\Earth_mover\api
composer require phpmailer/phpmailer
```

### Step 2: Get Gmail App Password
1. Go to: https://myaccount.google.com/
2. Click **Security** (left sidebar)
3. Enable **2-Step Verification** (if not already enabled)
4. Go to **App passwords** (under "Signing in to Google")
5. Select app: **Mail**
6. Select device: **Other (Custom name)** → Type "EarthMover"
7. Click **Generate**
8. **Copy the 16-character password** (you'll need this!)

### Step 3: Update request_password_reset.php

Open `C:\xampp\htdocs\Earth_mover\api\request_password_reset.php`

Find the `sendEmail()` function (around line 377) and replace it with this:

```php
// Email sending function for OTP using PHPMailer
function sendEmail($toEmail, $otp, $role) {
    // Include PHPMailer
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';
    require_once __DIR__ . '/PHPMailer/Exception.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration for Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com'; // ⚠️ CHANGE THIS to your Gmail
        $mail->Password   = 'your-app-password';    // ⚠️ CHANGE THIS to your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Email content
        $mail->setFrom('your-email@gmail.com', 'EarthMover'); // ⚠️ CHANGE THIS
        $mail->addAddress($toEmail);
        $mail->Subject = 'Password Reset OTP - EarthMover';
        $mail->Body    = "Hello,\n\nYour OTP for password reset is: $otp\n\nValid for 5 minutes.\n\nIf you didn't request this, please ignore this email.\n\n- EarthMover Team";
        
        $mail->send();
        
        // Log success
        $emailLog = date('Y-m-d H:i:s') . " - Email sent successfully to: $toEmail, OTP: $otp\n";
        @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
        
        return true;
    } catch (Exception $e) {
        // Log error
        $emailLog = date('Y-m-d H:i:s') . " - Email failed: {$mail->ErrorInfo}\n";
        $emailLog .= "  To: $toEmail, OTP: $otp\n";
        @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
        
        return false;
    }
}
```

**Important:** Replace:
- `your-email@gmail.com` with your actual Gmail address
- `your-app-password` with the 16-character App Password you generated

### Step 4: Test

1. Try password reset in the app
2. Check your Gmail inbox (and spam folder)
3. Check `email_log.txt` for sending status

## Alternative: Use Composer (Easier)

If you have Composer installed:

```bash
cd C:\xampp\htdocs\Earth_mover\api
composer require phpmailer/phpmailer
```

Then update the require_once paths:
```php
require_once __DIR__ . '/vendor/autoload.php';
```

## Troubleshooting

### "Class 'PHPMailer\PHPMailer\PHPMailer' not found"
- Check PHPMailer files are in correct location
- Verify file paths in require_once

### "SMTP connect() failed"
- Check Gmail App Password is correct
- Verify 2-Step Verification is enabled
- Check firewall isn't blocking port 587

### "Authentication failed"
- Use App Password, not your Gmail password
- Make sure App Password is 16 characters (no spaces)

### Still not receiving emails?
- Check spam/junk folder
- Verify email address in database
- Check `email_log.txt` for errors




































