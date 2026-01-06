# Admin Table Migration Guide

## Current Table Structure
Your existing `admins` table has:
- `admin_id` (primary key)
- `username` (unique)
- `password`

## Required Table Structure
The new implementation needs:
- `admin_id` (primary key, auto-increment)
- `name` (required)
- `email` (required, unique) - used for login
- `password` (required, bcrypt hashed)
- `created_at` (timestamp)

## Migration Options

### Option 1: Safe Migration (Recommended)
**File:** `MIGRATE_ADMINS_TABLE.sql`
- Preserves all existing data
- Automatically checks if columns exist before adding
- Migrates username to name and email
- Hashes plain text passwords
- **Use this if you want to keep existing admin data**

### Option 2: Simple Update
**File:** `UPDATE_ADMINS_TABLE.sql`
- Simpler SQL commands
- May show errors if columns already exist (you can ignore them)
- Migrates username to name and email
- **Use this if you're comfortable with SQL errors**

### Option 3: Clean Recreate
**File:** `RECREATE_ADMINS_TABLE.sql`
- Drops and recreates the table
- **WARNING: This deletes all existing admin data!**
- Creates a fresh table with one default admin
- **Use this only if you don't need existing data**

## Steps to Migrate

1. **Open phpMyAdmin:**
   - Go to: http://localhost/phpmyadmin/index.php?route=/sql&db=earthmover

2. **Choose a migration option:**
   - **Recommended:** Use `MIGRATE_ADMINS_TABLE.sql` (safest)
   - Or use `UPDATE_ADMINS_TABLE.sql` (simpler)
   - Or use `RECREATE_ADMINS_TABLE.sql` (if you don't need old data)

3. **Run the SQL script:**
   - Copy the contents of the chosen file
   - Paste into phpMyAdmin SQL tab
   - Click "Go"

4. **Verify the migration:**
   - Check that the table structure matches:
     ```
     admin_id (INT, PRIMARY KEY, AUTO_INCREMENT)
     name (VARCHAR(100), NOT NULL)
     email (VARCHAR(255), NOT NULL, UNIQUE)
     password (VARCHAR(255), NOT NULL)
     created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
     ```

5. **Test the admin signup/login:**
   - Try signing up a new admin through the app
   - Try logging in with email and password

## Data Migration Details

### Existing Admin Account
If you have an admin with:
- `username`: "admin"
- `password`: "admin123"

After migration:
- `name`: "admin" (copied from username)
- `email`: "admin@earthmover.com" (created from username)
- `password`: "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi" (hashed)

**To login after migration:**
- Email: `admin@earthmover.com`
- Password: `admin123` (the hash matches this password)

## Troubleshooting

### Error: "Duplicate column name"
- The column already exists, skip that ALTER TABLE command
- Or use `MIGRATE_ADMINS_TABLE.sql` which checks first

### Error: "Duplicate key name 'email'"
- The unique constraint already exists, skip that command

### Password not working after migration
- The password hash might not match
- Reset password through the app's "Forgot Password" feature
- Or manually update: `UPDATE admins SET password = '$2y$10$...' WHERE admin_id = 1;`

## After Migration

1. **Remove username column (optional):**
   ```sql
   ALTER TABLE `admins` DROP COLUMN `username`;
   ```

2. **Update existing admin email (if needed):**
   ```sql
   UPDATE `admins` SET `email` = 'your-email@example.com' WHERE `admin_id` = 1;
   ```

3. **Test admin signup/login in the app**




































