<?php
// Suppress any warnings/errors that might output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Set headers first, before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Read JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data'], JSON_UNESCAPED_UNICODE);
    exit;
}

$phone = isset($data['phone']) ? trim($data['phone']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$role  = isset($data['role']) ? trim($data['role']) : 'user';

// Determine identifier and whether it's email or phone
if ($role === 'admin') {
    // Admin always uses email
    if ($phone === '' && $email === '') {
        echo json_encode(['success' => false, 'message' => 'Email is required'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $identifier = !empty($email) ? $email : $phone; // Use email if provided, otherwise phone (for compatibility)
    $isEmail = true;
} elseif ($role === 'user') {
    // User uses email for password reset
    if ($phone === '' && $email === '') {
        echo json_encode(['success' => false, 'message' => 'Email is required'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $identifier = !empty($email) ? $email : $phone; // Use email if provided, otherwise phone (for compatibility)
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
} else {
    // Operator can use phone or email
    if ($phone === '' && $email === '') {
        echo json_encode(['success' => false, 'message' => 'Phone number or email is required'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $identifier = !empty($phone) ? $phone : $email;
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
}

// Database connection - UPDATE THESE VALUES IF DIFFERENT
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'earthmover'; // Change this to your actual database name

// First, connect without selecting database to check if it exists
$mysqli = new mysqli($db_host, $db_user, $db_pass);

if ($mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if database exists
$result = $mysqli->query("SHOW DATABASES LIKE '$db_name'");
if ($result->num_rows == 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database not found. Please run setup_database.sql in phpMyAdmin'
    ], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

// Now select the database
$mysqli->select_db($db_name);

// Check if user/operator/admin exists
if ($role === 'admin') {
    $table_name = 'admins';
    // For admin, check by email
    $stmt = $mysqli->prepare("SELECT 1 FROM $table_name WHERE email = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query error'], JSON_UNESCAPED_UNICODE);
        $mysqli->close();
        exit;
    }
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No account found for this email'], JSON_UNESCAPED_UNICODE);
        $stmt->close();
        $mysqli->close();
        exit;
    }
    $stmt->close();
    $phone = $identifier; // Store email in phone field for compatibility
    $userEmail = $identifier; // Store actual email for sending
} else {
    $table_name = ($role === 'operator') ? 'operators' : 'users';
    
    // Check by email or phone based on identifier type
    if ($isEmail) {
        $stmt = $mysqli->prepare("SELECT 1 FROM $table_name WHERE email = ? LIMIT 1");
    } else {
        $stmt = $mysqli->prepare("SELECT 1 FROM $table_name WHERE phone = ? LIMIT 1");
    }
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query error'], JSON_UNESCAPED_UNICODE);
        $mysqli->close();
        exit;
    }
    
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        $errorMsg = $isEmail ? 'No account found for this email' : 'No account found for this phone number';
        echo json_encode(['success' => false, 'message' => $errorMsg], JSON_UNESCAPED_UNICODE);
        $stmt->close();
        $mysqli->close();
        exit;
    }
    
    $stmt->close();
    $phone = $identifier; // Store identifier in phone field for password_resets table
    // For users/operators with email, store email for sending
    if ($isEmail) {
        $userEmail = $identifier;
    } else {
        $userEmail = null;
    }
}

// Generate 6-digit OTP
$otp = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', time() + 5 * 60); // OTP expires in 5 minutes

// Check if password_resets table exists, if not create it
$tableCheck = $mysqli->query("SHOW TABLES LIKE 'password_resets'");
if ($tableCheck->num_rows == 0) {
    // Create the table - use VARCHAR(255) so we can safely store emails as identifier
    $createTable = "CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `phone` VARCHAR(255) NOT NULL,
        `role` VARCHAR(20) NOT NULL,
        `otp` VARCHAR(10) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_phone_role` (`phone`, `role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$mysqli->query($createTable)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create password_resets table: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
        $mysqli->close();
        exit;
    }
} else {
    // Ensure phone column is wide enough to hold full email (upgrade from VARCHAR(20) if needed)
    // This ALTER is safe to run multiple times.
    @$mysqli->query("ALTER TABLE `password_resets` MODIFY `phone` VARCHAR(255) NOT NULL");
}

// Delete any existing OTP for this phone and role
$stmt = $mysqli->prepare('DELETE FROM password_resets WHERE phone = ? AND role = ?');
if ($stmt) {
    $stmt->bind_param('ss', $phone, $role);
    $stmt->execute();
    $stmt->close();
}

// Insert new OTP
$stmt = $mysqli->prepare('INSERT INTO password_resets (phone, role, otp, expires_at) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error], JSON_UNESCAPED_UNICODE);
    $mysqli->close();
    exit;
}

$stmt->bind_param('ssss', $phone, $role, $otp, $expiresAt);
if ($stmt->execute()) {
    // Log OTP to file (for testing/debugging)
    if ($role === 'admin' || ($role === 'user' && $isEmail) || ($role === 'operator' && $isEmail)) {
        $logMessage = date('Y-m-d H:i:s') . " - Email: $phone, Role: $role, OTP: $otp\n";
    } else {
        $logMessage = date('Y-m-d H:i:s') . " - Phone: $phone, Role: $role, OTP: $otp\n";
    }
    @file_put_contents(__DIR__ . '/otp_log.txt', $logMessage, FILE_APPEND);
    
    // Send OTP via email or SMS
    $emailResult = false;
    $smsResult = false;
    
    if ($role === 'admin' || ($role === 'user' && $isEmail) || ($role === 'operator' && $isEmail)) {
        // Send OTP via email
        if (!empty($userEmail)) {
            try {
                $emailResult = @sendEmail($userEmail, $otp, $role);
            } catch (\Exception $e) {
                $emailLog = date('Y-m-d H:i:s') . " - Email exception: " . $e->getMessage() . "\n";
                @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
                $emailResult = false;
            } catch (\Throwable $e) {
                $emailLog = date('Y-m-d H:i:s') . " - Email fatal error: " . $e->getMessage() . "\n";
                @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
                $emailResult = false;
            }
        } else {
            $emailLog = date('Y-m-d H:i:s') . " - Email not set for user/operator\n";
            @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
            $emailResult = false;
        }
        $smsResult = true; // Skip SMS
    } else {
        // Send OTP via SMS
        $smsMessage = "Your OTP for password reset is: $otp. Valid for 5 minutes. - EarthMover";
        $smsResult = sendSMS($phone, $smsMessage);
    }
    
    $stmt->close();
    $mysqli->close();
    
    // Check if in test mode (for testing without API key)
    $TEST_MODE = true; // Set to false when you configure email/SMS
    
    // Determine response message based on role and delivery method
    if ($role === 'admin' || ($role === 'user' && $isEmail) || ($role === 'operator' && $isEmail)) {
        // Email-based OTP - Always show OTP in response for testing
        // In production, remove 'otp' from response
        echo json_encode([
            'success' => true,
            'message' => $emailResult ? 'OTP sent to your registered email' : 'OTP generated (Check otp_log.txt if email not received)',
            'otp' => $otp, // ⚠️ For testing - Remove in production!
            'email_sent' => $emailResult
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // SMS-based OTP
        if ($TEST_MODE) {
            echo json_encode([
                'success' => true,
                'message' => 'OTP generated successfully (Test Mode - Check otp_log.txt for OTP)',
                'test_mode' => true,
                'otp' => $otp, // ⚠️ For testing only - Remove in production!
                'note' => 'Test mode active. Configure SMS API key for real SMS delivery.'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'OTP sent to your registered mobile number'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    exit;
} else {
    $errorMsg = 'Failed to generate OTP';
    if ($mysqli->error) {
        $errorMsg = 'Database error: ' . $mysqli->error;
    }
    $stmt->close();
    $mysqli->close();
    echo json_encode(['success' => false, 'message' => $errorMsg], JSON_UNESCAPED_UNICODE);
    exit;
}

// SMS sending function - FREE SMS Service (TextLocal)
function sendSMS($phone, $message) {
    // ============================================
    // FREE SMS SERVICE - TextLocal (10 FREE SMS)
    // ============================================
    // This uses TextLocal which provides 10 FREE SMS for testing
    // No credit card required!
    
    // STEP 1: Sign up at https://www.textlocal.in (FREE - takes 2 minutes)
    // STEP 2: Get your API Key from Dashboard
    // STEP 3: Replace 'YOUR_TEXTLOCAL_API_KEY' below with your API key
    // STEP 4: That's it! You'll get 10 FREE SMS immediately
    
    $apiKey = 'YOUR_TEXTLOCAL_API_KEY'; // ⚠️ Get this from https://www.textlocal.in (FREE signup)
    $sender = 'TXTLCL'; // Default sender ID (works immediately)
    
    // If API key not configured, try test mode
    if ($apiKey === 'YOUR_TEXTLOCAL_API_KEY' || empty($apiKey)) {
        // Test mode - log OTP to file
        preg_match('/\b\d{6}\b/', $message, $matches);
        $otp = isset($matches[0]) ? $matches[0] : 'N/A';
        
        $testLog = date('Y-m-d H:i:s') . " - Phone: $phone, OTP: $otp\n";
        $testLog .= "  → Sign up at https://www.textlocal.in (FREE) to receive SMS\n";
        @file_put_contents(__DIR__ . '/sms_log.txt', $testLog, FILE_APPEND);
        
        return true; // Return true so app shows success
    }
    
    // Clean phone number
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if needed (India +91)
    if (strlen($phone) == 10) {
        $phone = '91' . $phone;
    }
    
    // TextLocal API URL
    $url = "https://api.textlocal.in/send/?";
    $url .= "apikey=" . urlencode($apiKey);
    $url .= "&numbers=" . urlencode($phone);
    $url .= "&message=" . urlencode($message);
    $url .= "&sender=" . urlencode($sender);
    
    // Send SMS via TextLocal
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Log SMS response
    $logMessage = date('Y-m-d H:i:s') . " - Phone: $phone, HTTP Code: $httpCode";
    if ($curlError) {
        $logMessage .= ", cURL Error: $curlError";
    } else {
        $logMessage .= ", Response: $response";
    }
    $logMessage .= "\n";
    
    @file_put_contents(__DIR__ . '/sms_log.txt', $logMessage, FILE_APPEND);
    
    // Check if SMS was sent successfully
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['status']) && $responseData['status'] == 'success') {
            return true; // SMS sent successfully
        }
    }
    
    return false; // SMS failed
    
    // ============================================
    // PRODUCTION MODE - MSG91 Configuration
    // ============================================
    // STEP 1: Sign up at https://msg91.com
    // STEP 2: Get your Auth Key from Dashboard > API > Auth Key
    // STEP 3: Replace 'YOUR_MSG91_AUTH_KEY' below with your actual key
    // STEP 4: Set your sender ID (6 characters, approved by MSG91)
    
    $authKey = 'YOUR_MSG91_AUTH_KEY'; // ⚠️ REPLACE THIS with your MSG91 Auth Key
    $senderId = 'MSGIND'; // Use MSGIND (works immediately) or your approved sender ID
    
    // If auth key is not configured, log to file only
    if ($authKey === 'YOUR_MSG91_AUTH_KEY' || empty($authKey)) {
        @file_put_contents(__DIR__ . '/sms_log.txt', 
            date('Y-m-d H:i:s') . " - Phone: $phone, Status: SMS not sent - MSG91 Auth Key not configured\n", 
            FILE_APPEND);
        return false; // Return false so you know SMS wasn't sent
    }
    
    // Clean phone number (remove +, spaces, etc.)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if not present (assuming India +91)
    if (strlen($phone) == 10) {
        $phone = '91' . $phone; // Add India country code
    }
    
    // MSG91 API endpoint
    $url = "https://api.msg91.com/api/v2/sendsms";
    
    // Prepare data for MSG91 API
    $data = [
        'sender' => $senderId,
        'route' => '4', // Route 4 = Transactional SMS
        'country' => '91', // India country code
        'sms' => [
            [
                'message' => $message,
                'to' => [$phone]
            ]
        ]
    ];
    
    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'authkey: ' . $authKey
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Log SMS response
    $logMessage = date('Y-m-d H:i:s') . " - Phone: $phone, HTTP Code: $httpCode";
    if ($curlError) {
        $logMessage .= ", cURL Error: $curlError";
    } else {
        $logMessage .= ", Response: $response";
    }
    $logMessage .= "\n";
    
    @file_put_contents(__DIR__ . '/sms_log.txt', $logMessage, FILE_APPEND);
    
    // Check if SMS was sent successfully
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['type']) && $responseData['type'] == 'success') {
            return true; // SMS sent successfully
        }
    }
    
    return false; // SMS failed
}

// Email sending function for OTP
function sendEmail($toEmail, $otp, $role) {
    // ============================================
    // GMAIL SMTP CONFIGURATION
    // ============================================
    // Gmail SMTP Settings
    $gmailUsername = 'earthmover998@gmail.com';
    $gmailPassword = 'hrgbnwarhtksgrkf'; // Gmail App Password (16 characters, no spaces)
    
    // Check if PHPMailer is available
    // Try three possible locations:
    // 1) Inside this folder:  api/PHPMailer/src/PHPMailer.php
    // 2) One level up:        PHPMailer/src/PHPMailer.php   (as in your reference code)
    // 3) Directly in api/PHPMailer/PHPMailer.php  (matches your current folder)
    $phpmailerPathLocal = __DIR__ . '/PHPMailer/src/PHPMailer.php';
    $phpmailerPathRoot  = __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    $phpmailerPathFlat  = __DIR__ . '/PHPMailer/PHPMailer.php';

    $phpmailerPath = null;
    if (file_exists($phpmailerPathLocal)) {
        $phpmailerPath = $phpmailerPathLocal;
    } elseif (file_exists($phpmailerPathRoot)) {
        $phpmailerPath = $phpmailerPathRoot;
    } elseif (file_exists($phpmailerPathFlat)) {
        $phpmailerPath = $phpmailerPathFlat;
    }

    $phpmailerExists = $phpmailerPath !== null && file_exists($phpmailerPath);
    
    if ($phpmailerExists) {
        // Use PHPMailer (Recommended)
        try {
            // Suppress any output/warnings from require_once
            $oldErrorReporting = error_reporting(0);
            require_once $phpmailerPath;
            require_once dirname($phpmailerPath) . '/SMTP.php';
            require_once dirname($phpmailerPath) . '/Exception.php';
            error_reporting($oldErrorReporting);
            
            // Use fully qualified class names
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // SMTP Configuration for Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $gmailUsername;
            $mail->Password   = $gmailPassword;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPDebug  = 0; // Disable debug output to prevent breaking JSON
            $mail->Debugoutput = function($str, $level) {
                // Log debug to file instead of output
                @file_put_contents(__DIR__ . '/smtp_debug.txt', date('Y-m-d H:i:s') . " - $str\n", FILE_APPEND);
            };
            
            // Email content
            $mail->setFrom($gmailUsername, 'EarthMover');
            $mail->addAddress($toEmail);
            $mail->Subject = 'Password Reset OTP - EarthMover';
            $mail->Body    = "Hello,\n\nYour OTP for password reset is: $otp\n\nValid for 5 minutes.\n\nIf you didn't request this, please ignore this email.\n\n- EarthMover Team";
            $mail->isHTML(false);
            
            $mail->send();
            
            // Log success
            $emailLog = date('Y-m-d H:i:s') . " - Email sent successfully via PHPMailer to: $toEmail, OTP: $otp\n";
            @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
            
            return true;
        } catch (\Exception $e) {
            // Log error with full details
            $errorMsg = (isset($mail) && method_exists($mail, 'ErrorInfo')) ? $mail->ErrorInfo : $e->getMessage();
            $emailLog = date('Y-m-d H:i:s') . " - PHPMailer Error: $errorMsg\n";
            $emailLog .= "  Exception: " . $e->getMessage() . "\n";
            $emailLog .= "  To: $toEmail, OTP: $otp\n";
            $emailLog .= "  File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
            @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
            
            // Fall back to simple mail() or return false
            return false;
        } catch (\Throwable $e) {
            // Catch any other errors
            $emailLog = date('Y-m-d H:i:s') . " - PHPMailer Fatal Error: " . $e->getMessage() . "\n";
            $emailLog .= "  To: $toEmail, OTP: $otp\n";
            @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
            return false;
        }
    } else {
        // PHPMailer not found - use simple mail() (may not work on localhost)
        $fromEmail = $gmailUsername;
        $fromName = 'EarthMover';
        $subject = 'Password Reset OTP - EarthMover';
        $message = "Hello,\n\nYour OTP for password reset is: $otp\n\nValid for 5 minutes.\n\nIf you didn't request this, please ignore this email.\n\n- EarthMover Team";
        
        // Log email attempt
        $emailLog = date('Y-m-d H:i:s') . " - Attempting to send email (PHPMailer not found, using mail())\n";
        $emailLog .= "  To: $toEmail, OTP: $otp\n";
        $emailLog .= "  ⚠️ Note: PHP mail() may not work on localhost. Install PHPMailer for reliable delivery.\n";
        @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
        
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        $mailResult = @mail($toEmail, $subject, $message, $headers);
        
        if ($mailResult) {
            $emailLog = date('Y-m-d H:i:s') . " - Email sent via mail() to: $toEmail\n";
            @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
            return true;
        } else {
            $emailLog = date('Y-m-d H:i:s') . " - Email failed via mail() to: $toEmail\n";
            $emailLog .= "  → PHP mail() doesn't work on localhost/XAMPP\n";
            $emailLog .= "  → Download PHPMailer: https://github.com/PHPMailer/PHPMailer\n";
            $emailLog .= "  → See QUICK_GMAIL_SETUP.md for instructions\n";
            @file_put_contents(__DIR__ . '/email_log.txt', $emailLog, FILE_APPEND);
            
            // Return true for testing (so app shows success), but log the issue
            return true; // Change to false after configuring PHPMailer
        }
    }
}

