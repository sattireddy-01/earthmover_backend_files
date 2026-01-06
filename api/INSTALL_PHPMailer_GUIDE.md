# PHPMailer Installation Guide

## ✅ Status: PHPMailer is NOT INSTALLED

You need to install PHPMailer to send OTP emails via Gmail.

---

## 📥 Installation Steps

### Method 1: Manual Installation (Recommended)

1. **Download PHPMailer:**
   - Go to: https://github.com/PHPMailer/PHPMailer/releases
   - Download the latest ZIP file (e.g., `PHPMailer-6.9.1.zip`)

2. **Extract the ZIP:**
   - Right-click the ZIP file → Extract All
   - You'll get a folder like `PHPMailer-6.9.1`

3. **Copy the `src` folder:**
   - Navigate to: `PHPMailer-6.9.1\PHPMailer\src\`
   - Copy the entire `src` folder

4. **Paste to your project:**
   - Navigate to: `C:\xampp\htdocs\Earth_mover\api\`
   - Create a folder named `PHPMailer` (if it doesn't exist)
   - Paste the `src` folder inside it

5. **Final Structure:**
   ```
   C:\xampp\htdocs\Earth_mover\api\
   └── PHPMailer\
       └── src\
           ├── PHPMailer.php
           ├── SMTP.php
           └── Exception.php
   ```

6. **Verify Installation:**
   - Open: http://localhost/Earth_mover/api/CHECK_PHPMailer.php
   - You should see "✓✓✓ PHPMailer is WORKING! ✓✓✓"

---

### Method 2: Using Composer (Advanced)

If you have Composer installed:

```bash
cd C:\xampp\htdocs\Earth_mover\api
composer require phpmailer/phpmailer
```

Then update `request_password_reset.php` to use:
```php
require_once __DIR__ . '/vendor/autoload.php';
```

---

## 🔍 Quick Check

After installation, verify by opening:
- http://localhost/Earth_mover/api/CHECK_PHPMailer.php

---

## ❓ Troubleshooting

**Problem:** "PHPMailer class not found"
- **Solution:** Make sure the `src` folder contains `PHPMailer.php`, `SMTP.php`, and `Exception.php`

**Problem:** "Permission denied"
- **Solution:** Make sure XAMPP has write permissions to the `api` folder

**Problem:** Still not working after installation
- **Solution:** Check `CHECK_PHPMailer.php` for detailed error messages

---

## 📧 After Installation

Once PHPMailer is installed:
1. Test email sending: http://localhost/Earth_mover/api/TEST_EMAIL_SEND.php
2. Check email logs: http://localhost/Earth_mover/api/CHECK_EMAIL_STATUS.php

---

## 🔗 Direct Download Link

Latest PHPMailer Release:
https://github.com/PHPMailer/PHPMailer/releases/latest



































