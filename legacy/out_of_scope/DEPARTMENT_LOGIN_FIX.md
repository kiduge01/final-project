# Department Dashboard Login - Bug Fix & Troubleshooting

## Issues Found and Fixed

### 1. **Session Name Mismatch** (PRIMARY ISSUE) 🔴
**Problem:** The department subsystem was using a different session name than the main app, causing login sessions to not persist.

- **Main app config:** `CMAIN_SESSION`
- **Department session.php:** `church_cms_session` (was hardcoded)

**Fix Applied:**
- Updated `department/includes/session.php` to use `CMAIN_SESSION` (matches config)

### 2. **Missing Session Regeneration** (SECURITY)
**Problem:** The UnifiedAuthController wasn't regenerating the session ID after department login, creating a security vulnerability.

**Fix Applied:**
- Added `session_regenerate_id(true)` after successful department authentication
- Added `head_name` session variable for dashboard compatibility

## Verification Steps

### Step 1: Run Diagnostics
Navigate to: `http://localhost/Cmain/test_department_login.php`

This will check:
- ✓ Database connection
- ✓ Department table structure (head_email, head_password_hash columns)
- ✓ Departments with credentials configured
- ✓ Session configuration
- ✓ File existence
- ✓ API endpoints

### Step 2: Test the Login
1. Go to: `http://localhost/Cmain/public/`
2. Select **"Department Head"** from Account Type dropdown
3. Enter your department head email and password
4. Click **Sign In**

### Step 3: Check Browser Console
If login still fails:
1. Open **Developer Tools** (F12)
2. Go to **Console** tab
3. Look for error messages
4. Go to **Network** tab and check the response from `/api/v1/unified-login`

### Step 4: Verify Database Setup
Make sure your department has login credentials set up:
- Admin → Departments
- Edit the department you want to test
- Ensure "Head Email" and "Head Password" are filled in

## How Department Login Works

1. User goes to `/Cmain/public/` (main login page)
2. Selects "Department Head" role
3. Form submits to `/api/v1/unified-login` (API endpoint)
4. UnifiedAuthController.php:loginDepartment() processes the request:
   - Finds department by email
   - Verifies password with `password_verify()`
   - Sets session variables: `department_id`, `department_name`, `head_name`
   - Returns redirect URL: `/Cmain/department/dashboard/index.php`
5. JavaScript redirects to dashboard
6. Dashboard includes `department/includes/auth_check.php` which:
   - Verifies session is active (checks `$_SESSION['department_id']`)
   - Confirms department is still active in database
   - Allows access or redirects to login

## Common Issues & Solutions

### Issue: "Login page just refreshes"

**Possible Causes:**

1. **Department has no credentials set**
   - Solution: Set "Head Email" and "Head Password" in admin panel
   
2. **JavaScript error preventing redirect**
   - Solution: Check browser Console (F12) for errors
   
3. **Invalid credentials**
   - Solution: Verify email and password are correct in admin panel
   
4. **Database connection issue**
   - Solution: Run test_department_login.php to diagnose

5. **Session not persisting**
   - Solution: The fixes above should resolve this

### Issue: "Access denied" after login

**Possible Causes:**

1. **Department is deactivated**
   - Solution: Check `is_active` in departments table
   
2. **Session variables not being set**
   - Solution: Run test to verify session configuration

3. **Missing or corrupted session**
   - Solution: Clear browser cookies, try login again

## Files Modified

- `department/includes/session.php` - Fixed session name
- `app/controllers/UnifiedAuthController.php` - Added session regeneration

## Additional Notes

- The unified login system serves both Admin and Department roles
- Sessions are stored server-side; no token/JWT required
- Department and Admin systems share the same PHP session
- Password security: Uses PHP `password_hash()` and `password_verify()`

## Testing with Demo Credentials

If you need to test quickly, you can manually add a department with credentials via database:

```sql
UPDATE departments 
SET 
  head_email = 'test@church.local',
  head_password_hash = '<hash here>'
WHERE id = 1;
```

To generate a password hash in PHP:
```php
echo password_hash('your-password-here', PASSWORD_DEFAULT);
```

---

If issues persist after these fixes, please check:
1. Browser console for JavaScript errors
2. Server error logs (PHP logs)
3. Network tab in DevTools to see API response
4. Database to confirm department credentials exist
