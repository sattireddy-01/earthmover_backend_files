@echo off
echo ========================================
echo PHPMailer Installation Script
echo ========================================
echo.
echo This script will download and install PHPMailer
echo.

cd /d "%~dp0"

echo Step 1: Checking if Composer is installed...
where composer >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo Composer found! Using Composer to install PHPMailer...
    composer require phpmailer/phpmailer
    echo.
    echo Installation complete!
    echo PHPMailer is now in: vendor\phpmailer\phpmailer\
    echo.
    echo Next steps:
    echo 1. Update request_password_reset.php to use: require_once __DIR__ . '/vendor/autoload.php';
    echo 2. Configure Gmail credentials in sendEmail() function
    goto :end
) else (
    echo Composer not found. Downloading PHPMailer manually...
    echo.
)

echo Step 2: Creating PHPMailer directory...
if not exist "PHPMailer" mkdir PHPMailer
cd PHPMailer

echo Step 3: Downloading PHPMailer...
echo Please download PHPMailer manually:
echo.
echo 1. Go to: https://github.com/PHPMailer/PHPMailer/releases
echo 2. Download the latest ZIP file
echo 3. Extract it
echo 4. Copy the 'src' folder contents to: %CD%
echo.
echo OR use this PowerShell command (run in PowerShell as Administrator):
echo.
echo powershell -Command "Invoke-WebRequest -Uri 'https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip' -OutFile 'phpmailer.zip'; Expand-Archive -Path 'phpmailer.zip' -DestinationPath '.' -Force; Move-Item -Path 'PHPMailer-master\src\*' -Destination '.' -Force; Remove-Item -Path 'phpmailer.zip','PHPMailer-master' -Recurse -Force"
echo.

:end
echo.
echo ========================================
echo Setup Instructions:
echo ========================================
echo 1. Get Gmail App Password:
echo    - Go to: https://myaccount.google.com/
echo    - Security ^> 2-Step Verification ^> App passwords
echo    - Generate app password for "Mail"
echo.
echo 2. Update request_password_reset.php:
echo    - Find sendEmail() function
echo    - Replace 'your-email@gmail.com' with your Gmail
echo    - Replace 'your-app-password' with 16-char App Password
echo.
echo 3. Test password reset in the app
echo.
pause




































