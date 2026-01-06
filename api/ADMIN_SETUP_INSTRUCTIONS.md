# Admin Signup, Login, and Password Reset Setup

## Database Setup

1. **Create the admins table:**
   - Open phpMyAdmin: http://localhost/phpmyadmin/index.php?route=/sql&db=earthmover
   - Run the SQL script: `CREATE_ADMINS_TABLE.sql`
   - This creates the `admins` table with:
     - `admin_id` (primary key, auto-increment)
     - `name` (required)
     - `email` (required, unique)
     - `password` (required, bcrypt hashed)
     - `created_at` (timestamp)

## Features Implemented

### 1. Admin Signup
- **Location:** Choose Role screen → "Sign Up as Admin" button
- **Fields:** Name, Email, Password, Confirm Password
- **Validation:**
  - All fields required
  - Email format validation
  - Password minimum 6 characters
  - Password confirmation match
- **API:** `auth/admin_signup.php`
- **Database:** Stores admin with hashed password

### 2. Admin Login
- **Location:** Admin Signup screen → "Log In" link
- **Fields:** Email, Password
- **Features:**
  - Email/password authentication
  - "Forgot Password?" link
- **API:** `auth/user_login.php` (supports admin role)
- **Validation:** Email and password required

### 3. Admin Password Reset
- **Flow:**
  1. Click "Forgot Password?" on login screen
  2. Enter registered email
  3. Receive OTP (currently logged to `otp_log.txt` - email sending not implemented)
  4. Enter OTP, new password, and confirm password
  5. Password updated successfully
- **APIs:**
  - `request_password_reset.php` (supports admin role with email)
  - `confirm_password_reset.php` (supports admin role)
- **Note:** For admin, OTP is logged to file. Email sending can be implemented later.

## Android Changes

### Files Modified:
1. `ChooseRoleActivity.java` - Changed button to "Sign Up as Admin"
2. `AdminSignupActivity.java` - Full signup implementation with API
3. `AdminLoginActivity.java` - Full login implementation with forgot password
4. `ResetPasswordActivity.java` - Updated to handle admin (email instead of phone)
5. `ConfirmResetPasswordActivity.java` - Updated to navigate to AdminLoginActivity
6. `ApiService.java` - Added `createAdmin()` endpoint
7. `activity_choose_role.xml` - Button text changed
8. `activity_admin_signup.xml` - Password hint updated to "Create Password"
9. `activity_admin_login.xml` - Changed to email field, added forgot password link

### New Files:
1. `models/Admin.java` - Admin model class

## PHP Backend Changes

### New Files:
1. `auth/admin_signup.php` - Admin signup endpoint
2. `CREATE_ADMINS_TABLE.sql` - SQL script to create admins table

### Modified Files:
1. `auth/user_login.php` - Added admin role support
2. `request_password_reset.php` - Added admin role support (uses email)
3. `confirm_password_reset.php` - Added admin role support (uses email)

## Important Notes

1. **Admin uses email instead of phone:**
   - Login: Email + Password
   - Password Reset: Email (OTP sent to email - currently logged only)

2. **Password Reset for Admin:**
   - The `password_resets` table stores email in the `phone` field for compatibility
   - OTP is currently logged to `otp_log.txt` file
   - Email sending functionality can be added later using PHPMailer or similar

3. **Database:**
   - Admin table: `admins`
   - Primary key: `admin_id`
   - Email must be unique

## Testing

1. **Signup:**
   - Navigate to Choose Role → Sign Up as Admin
   - Fill in name, email, password, confirm password
   - Should create admin account

2. **Login:**
   - From signup screen, click "Log In"
   - Enter email and password
   - Should login successfully

3. **Password Reset:**
   - On login screen, click "Forgot Password?"
   - Enter registered email
   - Check `otp_log.txt` for OTP
   - Enter OTP, new password, confirm password
   - Should reset password successfully

## Next Steps (Optional)

1. Implement email sending for admin OTP (using PHPMailer or similar)
2. Create Admin Dashboard activity
3. Add admin-specific features and permissions




































