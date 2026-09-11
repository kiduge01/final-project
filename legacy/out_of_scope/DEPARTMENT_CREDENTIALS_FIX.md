# Department Credentials Issue - Fix Guide

## Problem
When trying to add/edit a department with credentials (email and password), you get an error:
```
Unexpected token '<', "<br /> <fo"... is not valid JSON
```

## Root Cause
The database migrations for department head login credentials haven't been run yet. The columns `head_email` and `password` are missing from the `departments` table.

## Solution

### Option 1: Quick Fix (Recommended)
Run the migration runner script we've created:

1. Open your browser and navigate to:
   ```
   http://localhost/Cmain/run_migrations.php
   ```

2. You'll see a list of all migrations being applied. Wait for it to complete.

3. Once done, go back to Settings → Departments and try adding a department again.

### Option 2: Manual Fix via phpMyAdmin or MySQL CLI

**Via MySQL command line:**
```sql
USE church_cms;

-- Add the missing columns
ALTER TABLE departments
  ADD COLUMN IF NOT EXISTS head_email VARCHAR(100) NULL UNIQUE AFTER description,
  ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL AFTER head_email;

-- Add index for performance
ALTER TABLE departments
  ADD INDEX IF NOT EXISTS idx_departments_head_email (head_email);
```

**Via phpMyAdmin:**
1. Open phpMyAdmin and select the `church_cms` database
2. Go to the SQL tab
3. Copy and paste the SQL above
4. Execute

### Option 3: Re-import Full Setup
If you prefer to start fresh:
1. Delete the `church_cms` database
2. Create it again
3. Import `database/full_setup.sql` (which includes all migrations)

## Verification
After applying the migration, you should be able to:
- Add departments with email/password for separate department subsystem login
- Access department portal at `/department/auth/login.php` with those credentials

## Still Having Issues?
If you still see errors after migration:
1. Check that the columns exist:
   ```sql
   DESCRIBE departments;
   ```
   You should see columns: `head_email` and `password`

2. Clear your browser cache (Ctrl+Shift+Delete)

3. Try reloading the Settings page

## Files Updated
- Created: `run_migrations.php` - Helper script to run all migrations
