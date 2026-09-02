## MariaDB Connection Issue - Root Cause & Fix

### Problem
```
ERROR 1130 (HY000): Host 'localhost' is not allowed to connect to this MariaDB server
```

The root user in MariaDB doesn't have proper host entries to accept connections from the application.

###Why This Happens
- The MySQL user table doesn't have a valid entry for `root@localhost` or `root@127.0.0.1`
- MariaDB is running but rejecting all connection attempts from the application
- The database `church_cms` and all tables exist, but are inaccessible

### Solution Options

#### Option 1: Use phpMyAdmin (Easiest - Try First)
1. Open http://localhost/phpmyadmin in your browser
2. If it connects, you can use it to:
   - Check the current root user configuration
   - Grant proper permissions
   - Create/fix the root user accounts

####Option 2: Reset Root User via Windows Service (Admin Required)
1. Open Command Prompt as Administrator
2. Run:
   ```cmd
   net stop "MySQL80"
   C:\xampp\mysql\bin\mysqld.exe --console --skip-grant-tables --port=3306
   ```
3. In another Admin Command Prompt:
   ```cmd
   C:\xampp\mysql\bin\mysql -u root
   FLUSH PRIVILEGES;
   DELETE FROM mysql.user WHERE User='root';
   CREATE USER 'root'@'localhost' IDENTIFIED BY '';
   CREATE USER 'root'@'127.0.0.1' IDENTIFIED BY '';
   GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
   GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;
   FLUSH PRIVILEGES;
   ```
4. Stop the console mysqld and restart normally

#### Option 3: Reinstall MariaDB
1. Backup your database:
   - Copy your `C:\xampp\mysql\data\church_cms` folder
2. Uninstall/reinstall XAMPP MariaDB
3. Restore your data directory

#### Option 4: Create AlternateDatabase User
If you have phpMyAdmin access:
1. Create a new user: `cms_user`@`localhost` with a password
2. Grant all privileges on `church_cms`
3. Update `app/config.php`:
   ```php
   'user' => 'cms_user',
   'pass' => 'your_password',
   ```

### Files Modified
- `app/config.php` - Changed host from 127.0.0.1 to localhost

### Test Files Created (Can be deleted)
- `db_test.php`
- `db_test2.php`
- `diagnose.php`

Recommend trying Option 1 first (phpMyAdmin) as it doesn't require admin privileges!
