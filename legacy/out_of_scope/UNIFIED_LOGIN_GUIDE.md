# Unified Login System

## Overview

The Unified Login System allows both Admin and Department users to log in through a single, modern login interface with role selection. This eliminates the need for separate login pages and provides a seamless user experience.

## Features

✅ **Single Login Page** - Both Admin and Department users use the same URL  
✅ **Role Selection Dropdown** - Users select their role: Admin, Pastor, Accountant, Secretary, or Department Head  
✅ **Responsive Design** - Works beautifully on desktop and mobile  
✅ **Professional UI** - Modern, dark-themed design with gold accents  
✅ **Session Management** - Automatically routes users to appropriate dashboard  
✅ **Brute-Force Protection** - Rate limiting on failed login attempts  
✅ **Audit Logging** - All logins tracked for security  

---

## Access Points

### Admin System Login
```
URL: /login?unified=1
```

### Department System Login
Users can also access through the same URL and select "Department Head" role.

---

## Architecture

### Files Created

#### 1. **Unified Login Page**
- **Path**: `app/views/pages/unified_login.php`
- **Purpose**: Beautiful, responsive login interface with role dropdown
- **Features**:
  - Role selection (Admin roles + Department)
  - Email field
  - Password field
  - Error/success alerts
  - Forgot password link
  - Brand panel with logo and church info

#### 2. **Unified Auth Controller**
- **Path**: `app/controllers/UnifiedAuthController.php`
- **Class**: `UnifiedAuthController`
- **Methods**:
  - `login(array $input)` - Main entry point
  - `loginAdmin(string $email, string $password, string $roleName)` - Admin role handling
  - `loginDepartment(string $email, string $password)` - Department head handling

#### 3. **Updated Routing**
- **File**: `public/index.php`
- **New Route**: `POST /api/v1/unified-login`
- **Handler**: `UnifiedAuthController->login()`

---

## Database Schema

### Required Tables

#### Users Table
```sql
users
├── id (BIGINT, PK, AI)
├── role_id (BIGINT, FK → roles)
├── full_name (VARCHAR)
├── email (VARCHAR, UNIQUE)
├── phone (VARCHAR, UNIQUE)
├── password_hash (VARCHAR)
├── is_active (TINYINT)
└── ...other fields
```

**Supported Admin Roles**:
- Administrator
- Pastor
- Accountant
- Secretary

#### Departments Table
```sql
departments
├── id (BIGINT, PK, AI)
├── name (VARCHAR, UNIQUE)
├── head_user_id (BIGINT, FK → users)
├── head_email (VARCHAR, UNIQUE) ← Used for login
├── password (VARCHAR) ← Hashed password
├── is_active (TINYINT)
└── ...other fields
```

---

## API Endpoint

### POST /api/v1/unified-login

**Request Body**:
```json
{
  "role": "admin|pastor|accountant|secretary|department",
  "email": "user@church.org",
  "password": "password123"
}
```

**Success Response (Admin)**:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "full_name": "John Doe",
      "role": "administrator",
      "permissions": [...]
    },
    "redirect": "/dashboard"
  }
}
```

**Success Response (Department)**:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "department": {
      "id": 5,
      "name": "Finance Department"
    },
    "redirect": "/Cmain/department/dashboard/index.php"
  }
}
```

**Error Response**:
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

## Session/Authentication Flow

### Admin System
1. User visits `/login?unified=1`
2. Selects Admin role and account type (Admin/Pastor/Accountant/Secretary)
3. Enters email & password
4. Server:
   - Validates credentials against `users` table
   - Verifies selected role matches user's actual role
   - Checks brute-force protection
   - Logs audit entry
   - Creates session via `Auth::login()`
5. JavaScript redirects to `/dashboard`

### Department System
1. User visits `/login?unified=1`
2. Selects "Department Head" role
3. Enters email & password
4. Server:
   - Queries `departments` table for matching email
   - Verifies password against hashed value
   - Checks department is active
   - Creates department session
   - Logs audit entry
5. JavaScript redirects to `/Cmain/department/dashboard/index.php`

---

## Usage Examples

### For End Users

**Admin/Pastor/Accountant/Secretary**:
1. Navigate to: `http://church.local/Cmain/login?unified=1`
2. Select role from dropdown (e.g., "Administrator")
3. Enter email address
4. Enter password
5. Click "Sign In"
6. ✅ Redirected to admin dashboard

**Department Head**:
1. Navigate to: `http://church.local/Cmain/login?unified=1`
2. Select "Department Head" from dropdown
3. Enter department email
4. Enter department password (set by admin in Settings)
5. Click "Sign In"
6. ✅ Redirected to department dashboard

### For Developers

**Testing the API**:
```bash
# Admin login
curl -X POST http://church.local/Cmain/api/v1/unified-login \
  -H "Content-Type: application/json" \
  -d '{
    "role": "administrator",
    "email": "admin@church.org",
    "password": "password123"
  }'

# Department login
curl -X POST http://church.local/Cmain/api/v1/unified-login \
  -H "Content-Type: application/json" \
  -d '{
    "role": "department",
    "email": "finance@church.org",
    "password": "dept_password123"
  }'
```

**JavaScript Client**:
```javascript
async function unifiedLogin(role, email, password) {
  const response = await fetch('/api/v1/unified-login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      role,
      email,
      password
    })
  });
  
  const data = await response.json();
  
  if (data.success) {
    window.location.href = data.data.redirect;
  } else {
    alert(data.message);
  }
}

// Usage
unifiedLogin('admin', 'john@church.org', 'pass123');
```

---

## Security Features

### Implemented
- ✅ Password hashing using `PASSWORD_DEFAULT`
- ✅ CSRF token validation
- ✅ Brute-force protection with rate limiting
- ✅ Email format validation
- ✅ Role verification (users can't login as different role)
- ✅ Audit logging of all login attempts
- ✅ Session management
- ✅ HTTP-only session cookies (recommended in config)

### Best Practices
- ✅ Passwords validated on both client & server
- ✅ Generic error messages (no user enumeration)
- ✅ Login attempts tracked and limited
- ✅ All communications over HTTPS (recommended)

---

## Styling & Customization

### Design Elements
- **Color Scheme**: Dark blue (#0B0E1A) with gold accents (#D4A017)
- **Layout**: 2-column split on desktop (brand left, form right)
- **Responsive**: Single column on mobile devices
- **Font**: Inter font family with fallbacks
- **Icons**: FontAwesome 6.4.0

### Customization Points

**Logo URL** - Loaded from `church_settings` table:
```php
// In unified_login.php
<?php if ($logoUrl): ?>
    <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
<?php else: ?>
    <i class="fas fa-church"></i>
<?php endif; ?>
```

**Church Name** - From config:
```php
$churchName = $config['app']['church_name'] ?? 'Church CMS';
```

**Base URL** - From routing:
```php
$baseUrl = BASE_URL;
```

---

## Backward Compatibility

### Old Endpoints Still Active
- ✅ `/api/v1/auth/login` - Original Admin login API
- ✅ `/department/auth/login.php` - Original Department login page
- ✅ Existing sessions continue to work

Unified login is an **addition**, not a replacement. Existing systems remain operational.

---

## Migration Path

### For Admins Setting Up Department Credentials

1. Go to **Settings** → **Departments**
2. Select or create a department
3. Enter **Department Head Login Credentials**:
   - Email: `finance@church.org`
   - Password: `secure_password_123` (minimum 6 characters)
4. Save department
5. Credentials automatically hashed and stored
6. Department head can now login via unified page

### For Department Heads Using New System

- Old: Direct to `/department/auth/login.php`
- New: Navigate to `/login?unified=1` and select "Department Head"
- Same credentials work (admin shouldn't change them)

---

## Troubleshooting

### "Invalid credentials" error
- ✅ Verify email is correct
- ✅ Check password is typed correctly
- ✅ Ensure account is active in database
- ✅ For departments: confirm credentials were set by admin

### "Too many login attempts" error
- ✅ Wait 5-15 minutes (based on login_attempts table settings)
- ✅ Or contact administrator to clear attempts

### Redirect loop
- ✅ Verify Auth middleware is working
- ✅ Check session is being created properly
- ✅ Review browser console for JavaScript errors

### Department login not working
- ✅ Verify department is marked as `is_active = 1`
- ✅ Check `head_email` and `password` are set in departments table
- ✅ Confirm password is hashed (should start with `$2y$`)

---

## Configuration

### In `app/config.php`
```php
return [
    'app' => [
        'base_path' => '/Cmain',
        'church_name' => 'Your Church Name',
        'debug' => true,
        ...
    ],
    ...
];
```

### In database/migrations
Ensure these tables exist:
- `users` with `email`, `password_hash`, `role_id`, `is_active`
- `roles` with role names (Administrator, Pastor, Accountant, Secretary)
- `departments` with `head_email`, `password`, `is_active`
- `audit_logs` for login tracking

---

## Next Steps

### Optional Enhancements
- 🔄 Add "Remember Me" checkbox
- 🔄 Add social login (OAuth)
- 🔄 Add 2FA support
- 🔄 Add password strength meter
- 🔄 Add login history view
- 🔄 Implement SMS-based OTP verification

### Recommended Monitoring
- Monitor `login_attempts` table for brute-force patterns
- Review `audit_logs` for unusual login activity
- Set up alerts for failed logins from multiple IPs

---

## Summary

This unified login system streamlines authentication for your church management platform by:
1. **Elimination of multiple login pages**
2. **Role-based routing** to correct dashboards
3. **Professional, accessible UI**
4. **Security-first implementation**
5. **Backward compatibility** with existing systems

Users can now access any dashboard through a single, familiar login experience.
