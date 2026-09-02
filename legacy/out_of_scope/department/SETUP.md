# Department Subsystem - Setup Guide

## ⚠️ Important: Dual Authentication System

**The Department Subsystem works ALONGSIDE the main admin system:**

1. **Main Admin System** - Used by church administrators
   - Departments are created/managed in main settings
   - Admin assigns main admin users as department heads (`head_user_id`)
   - Admin can also manage department-wide operations

2. **Department Head Login System** - Used by department heads
   - Separate login at `/department/auth/login.php`
   - Uses email + password stored in `departments` table
   - Department heads manage their own members, leaders, finances, reports
   - Completely separate from main admin login

## Pre-Requisites
✅ WAMP Server installed and running  
✅ Church Management System already installed  
✅ MySQL/MariaDB database running  
✅ Admin has access to main admin panel (Settings → Departments)

## Setup Steps

### Step 1: Run Database Migration

1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Select your database (`church_cms`)
3. Go to the **SQL** tab
4. Copy and paste BOTH migration files in order:

#### Migration 1: Add Email/Password to Departments
```
File: database/migrations/2026_04_09_001_modify_departments_for_separate_login.sql
```
- Adds `head_email` and `password` columns to existing departments table
- **Keeps** the existing `head_user_id` (admin relationship)
- Adds index for email lookups

#### Migration 2: Create Supporting Tables
```
File: database/migrations/2026_04_09_002_create_department_members_junction.sql
```
- Creates `department_members` (many-to-many membership)
- Creates `department_leaders` (leadership roles)
- Creates `department_reports` (report submissions)
- Adds `department_id` to `finance_entries`

### Step 2: Verify Database Changes

In phpMyAdmin, run:
```sql
DESCRIBE departments;          -- Should show head_email, password columns
DESCRIBE department_members;   -- Should exist
DESCRIBE department_leaders;   -- Should exist
DESCRIBE department_reports;   -- Should exist
```

### Step 3: Set Department Head Credentials

**Option A: Via Admin Panel (RECOMMENDED)**

1. Login to main admin system (http://localhost/Cmain/public)
2. Go to **Settings** → **Departments**
3. Find a department (e.g., "Ibada")
4. Click **Edit** (or look for "Set Head Credentials" button)
5. Fill in:
   - **Head Email**: The email for department head login (e.g., `ibada@church.local`)
   - **Password**: Secure password (min 6 characters)
   - **Confirm Password**: Repeat the password
6. Click **Save**

*Note: If the admin interface doesn't have this yet, use Option B below.*

**Option B: Via SQL (Direct Database)**

In phpMyAdmin SQL tab:

```sql
-- Generate password hash (use a PHP file or tool to create this)
-- Example password: "secure_password_123"
-- Hashed result: $2y$10$...

UPDATE departments 
SET 
    head_email = 'ibada@church.local',
    password = '$2y$10$aIjmUlHoVF34F5FGoaP1jOVUKaWZatvKUBWDx5O4BQScPvhQHLvWy'
WHERE name = 'Ibada';
```

### Step 3a: Generate Password Hash (if using SQL)

Create a temporary file `/test_password.php`:

```php
<?php
$password = 'your_secure_password';
echo password_hash($password, PASSWORD_DEFAULT);
?>
```

1. Save the file
2. Visit: http://localhost/Cmain/test_password.php
3. Copy the hashed output
4. Use it in the SQL UPDATE above
5. Delete the test file

### Step 4: Test the Department Head Login

1. Open browser: `http://localhost/Cmain/department/auth/login.php`
2. Login with:
   - **Email**: The email you set above (e.g., `ibada@church.local`)
   - **Password**: The plain text password you used for hashing
3. Click **Login**
4. Should redirect to the department dashboard

### Step 5: Configure All Departments (Optional)

Set credentials for all departments:

```sql
UPDATE departments SET head_email = 'vijana@church.local', password = '$2y$10$...' WHERE name = 'Vijana';
UPDATE departments SET head_email = 'ujenzi@church.local', password = '$2y$10$...' WHERE name = 'Ujenzi';
UPDATE departments SET head_email = 'huduma@church.local', password = '$2y$10$...' WHERE name = 'Huduma';
UPDATE departments SET head_email = 'elimu@church.local', password = '$2y$10$...' WHERE name = 'Elimu';
UPDATE departments SET head_email = 'utawala@church.local', password = '$2y$10$...' WHERE name = 'Utawala';
```

---

## Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│          CHURCH MANAGEMENT SYSTEM                        │
└────────────────┬────────────────────────────────────────┘
                 │
         ┌───────┴────────┐
         │                │
    ┌────▼─────┐    ┌────▼──────────────┐
    │  ADMIN    │    │ DEPARTMENT HEAD   │
    │  LOGIN    │    │ LOGIN             │
    ├────────────┤    ├───────────────────┤
    │ URL:       │    │ URL:              │
    │ /public    │    │ /department/auth  │
    │            │    │                   │
    │ User:      │    │ Email:            │
    │ Main Users │    │ department.head   │
    │            │    │                   │
    │ Password:  │    │ Password:         │
    │ DB users   │    │ DB departments    │
    └────┬────────    └────┬──────────────┘
         │                 │
    ┌────▼─────┐      ┌────▼──────────────┐
    │ Admin     │      │ Department        │
    │ Dashboard │      │ Dashboard         │
    │           │      │                   │
    │ Manage:   │      │ Manage:           │
    │ - Settings│      │ - Members         │
    │ - Users   │      │ - Leaders         │
    │ - Depts   │      │ - Finance         │
    │ - Reports │      │ - Reports         │
    └───────────┘      └───────────────────┘
```

---

## Quick Test Checklist

After setup, verify the following works:

### Admin System
- [ ] Can create/edit departments in main admin
- [ ] Can set department head email/password
- [ ] Departments appear in settings

### Department Head Login  
- [ ] Can login with email/password at `/department/auth/login.php`
- [ ] Invalid credentials show error message
- [ ] Successfully logged in redirects to dashboard

### Dashboard
- [ ] Dashboard loads without errors
- [ ] Shows department name
- [ ] Statistics cards display (Members, Leaders, Balance, Reports)

### Modules
- [ ] Can view/add/edit members
- [ ] Can view/add leaders
- [ ] Can add income/expense
- [ ] Can create/submit reports
- [ ] Can logout successfully

---

## Default Departments

System comes with 6 pre-seeded departments:

| Department | Name (Swahili) | Description |
|-----------|----------------|-------------|
| Ibada | Worship & Music | Worship services and music |
| Vijana | Youth | Youth activities and programs |
| Ujenzi | Construction | Building and expansion projects |
| Huduma | Care & Relief | Community care and relief |
| Elimu | Bible Education | Teaching and education |
| Utawala | Governance | Church administration |

---

## File Locations

```
✓ /department/               - Main subsystem f-- Add columns for department head information and subsystem login
ALTER TABLE `departments` ADD COLUMN `head_name` VARCHAR(255) NULL AFTER `id` COMMENT 'Name of department head';
ALTER TABLE `departments` ADD COLUMN `head_email` VARCHAR(255) UNIQUE NULL COMMENT 'Email for subsystem login';
ALTER TABLE `departments` ADD COLUMN `password` VARCHAR(255) NULL COMMENT 'Hashed password for subsystem login';

-- Add index for email lookups
CREATE INDEX idx_head_email ON `departments`(head_email);
✓ /department/auth/          - Login pages  
✓ /department/dashboard/     - Dashboard page
✓ /department/members/       - Members management
✓ /department/leaders/       - Leaders management
✓ /department/finance/       - Finance management
✓ /department/reports/       - Reports management
✓ /database/migrations/      - Migration files
```

---

## Troubleshooting Setup

### Error: "Table already exists"
- Migration files are safe to run multiple times
- If error occurs, check that migration ran partially
- You may need to run remaining statements individually

### Error: "Foreign key constraint fails"
- Ensure all referenced tables exist
- Run migrations in order (1, then 2)

### Login Page Shows Blank
- Check browser console for JavaScript errors (F12)
- Verify CSS files loaded in Network tab
- Ensure `/department/auth/login.php` file exists

### Cannot Login - "Invalid email or password"
- Verify credentials were set correctly
- Check database: `DESCRIBE departments;` should show head_email, password
- Verify password hash is correct (if set via SQL)
- Test password worked through admin interface

### After Login, Redirects Back to Login
- Check that migrations ran successfully
- Verify session.php is being included
- Check browser allows cookies
- Check PHP error logs for database errors

---

## Security Notes

✅ Passwords hashed using `password_hash()` with default algorithm  
✅ Department isolation - heads see only their department data  
✅ Prepared statements prevent SQL injection  
✅ Output escaping prevents XSS attacks  
✅ Session regeneration prevents fixation attacks  
✅ All actions audit logged  

---

## Next Steps

1. **Run migrations** - Database setup
2. **Set credentials** - Via admin or SQL
3. **Test login** - Verify access works
4. **Train heads** - Show how to use system
5. **Monitor usage** - Check audit logs

---

**Setup Guide completed!** 🎉  
Visit: `http://localhost/Cmain/department/auth/login.php`
