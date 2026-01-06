@echo off
echo ========================================
echo PHPMailer Download Helper
echo ========================================
echo.
echo This script will help you download PHPMailer.
echo.
echo Step 1: Opening PHPMailer download page...
start https://github.com/PHPMailer/PHPMailer/releases/latest
echo.
echo Step 2: Instructions:
echo   1. Download the ZIP file (e.g., PHPMailer-6.9.1.zip)
echo   2. Extract it
echo   3. Copy the 'src' folder from: PHPMailer-6.9.1\PHPMailer\src\
echo   4. Paste to: %CD%\PHPMailer\src\
echo.
echo Target folder: %CD%\PHPMailer\src\
echo.
echo After copying, run CHECK_PHPMailer.php to verify installation.
echo.
pause



































