# Email Setup Guide for User Password Reset

## Overview
The user password reset now sends OTP via email instead of SMS. This guide explains how to configure email sending.

## Current Implementation

### Method 1: PHP mail() Function (Default)
The system currently uses PHP's built-in `mail()` function. This requires:
- A mail server configured on your XAMPP server
- Or a mail relay service

**Note:** PHP `mail()` function may not work on localhost without proper mail server configuration.

### Method 2: PHPMailer with SMTP (Recommended)
For reliable email delivery, use PHPMailer with SMTP (Gmail, Outlook, etc.).

## Setup Instructions

### Option A: Using Gmail SMTP (Recommended)

1. **Enable Gmail App Password:**
   - Go to your Google Account: https://myaccount.google.com/
   - Security → 2-Step Verification (enable if not already)
   - App passwords → Generate app password
   - Copy the 16-character password

2. **Download PHPMailer:**
   - Download from: https://github.com/PHPMailer/PHPMailer
   - Extract to: `C:\xampp\htdocs\Earth_mover\api\PHPMailer\`

3. **Update `request_password_reset.php`:**
   - Uncomment the PHPMailer code (lines with `/*` and `*/`)
   - Update these settings:
     ```php
     $mail->Username   = 'your-email@gmail.com'; // Your Gmail
     $mail->Password   = 'your-app-password'; // Gmail App Password
     $mail->setFrom('your-email@gmail.com', 'EarthMover');
     ```

4. **Comment out the PHP mail() code** (the simple mail() function)

### Option B: Using Other SMTP Services

You can use any SMTP service:
- **Outlook/Hotmail:** `smtp-mail.outlook.com`, Port 587
- **Yahoo:** `smtp.mail.yahoo.com`, Port 587
- **Custom SMTP:** Use your provider's SMTP settings

Update the PHPMailer configuration accordingly:
```php
$mail->Host       = 'smtp-mail.outlook.com'; // Your SMTP server
$mail->Port       = 587; // Your SMTP port
$mail->Username   = 'your-email@outlook.com';
$mail->Password   = 'your-password';
```

## Testing

1. **Check OTP Log:**
   - File: `C:\xampp\htdocs\Earth_mover\api\otp_log.txt`
   - Contains all generated OTPs

2. **Check Email Log:**
   - File: `C:\xampp\htdocs\Earth_mover\api\email_log.txt`
   - Contains email sending status

3. **Test Flow:**
   - User login → Forgot Password
   - Enter registered email
   - Check email inbox for OTP
   - Enter OTP and new password

## Troubleshooting

### Email not received?
1. Check `email_log.txt` for errors
2. Check spam/junk folder
3. Verify email address is correct in database
4. Ensure SMTP credentials are correct
5. Check firewall/antivirus blocking SMTP

### PHP mail() not working?
- PHP `mail()` requires mail server configuration
- Use PHPMailer with SMTP instead (recommended)

### PHPMailer errors?
- Ensure PHPMailer files are in correct location
- Check file paths in require_once statements
- Verify SMTP credentials
- Check PHP error logs

## Security Notes

1. **Never commit email passwords to Git**
2. **Use App Passwords, not main password** (for Gmail)
3. **Consider using environment variables** for sensitive data
4. **Enable SSL/TLS** for SMTP connections

## Current Status

- ✅ Email sending function implemented
- ✅ User password reset uses email
- ✅ Admin password reset uses email
- ✅ Operator can use email or phone
- ⚠️ Currently using PHP mail() (may need SMTP configuration)
- 📝 PHPMailer code ready (commented out)

## Next Steps

1. Choose email method (PHP mail() or PHPMailer)
2. Configure SMTP settings if using PHPMailer
3. Test email delivery
4. Update `TEST_MODE` to `false` in production




































