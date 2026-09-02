# SETUP INSTRUCTIONS

## Prerequisites
- WAMP (Apache + MySQL + PHP)
- Database: church_cms
- Running on http://localhost

## Installation Steps

### 1. Create Database
```sql
CREATE DATABASE church_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import Schema
In MySQL command line or phpMyAdmin:
```sql
source c:/wamp64/www/kanisa/church-cms/database/schema.sql;
```

### 3. Import Sample Data
```sql
source c:/wamp64/www/kanisa/church-cms/database/sample_data.sql;
```

### 4. Verify Database Credentials
Edit: `app/config.php`
```php
'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'church_cms',
    'user' => 'root',
    'pass' => '',  // Update if needed
    'charset' => 'utf8mb4',
],
```

### 5. Enable Apache Rewrite Module
If not already enabled, uncomment in httpd.conf:
```
LoadModule rewrite_module modules/mod_rewrite.so
```

Also ensure AllowOverride is set in httpd.conf:
```
<Directory "c:/wamp64/www">
    AllowOverride All
</Directory>
```

### 6. Restart Apache
- Right-click WAMP icon → Restart All Services
- Or manually restart Apache

## Access the App

### Option A (Direct - No rewrite needed)
```
http://localhost/kanisa/church-cms/public/
```

### Option B (With .htaccess rewrite)
```
http://localhost/kanisa/church-cms/
```

## Login Credentials

Demo users created in sample_data.sql:

**User 1 (Admin)**
- Phone: +255700000001
- Password: 12345678
- Role: Admin

**User 2 (Finance Officer)**
- Phone: +255700000002
- Password: 12345678
- Role: Finance Officer

**User 3 (Secretary)**
- Phone: +255700000003
- Password: 12345678
- Role: Secretary

**User 4 (Standard User)**
- Phone: +255700000004
- Password: 12345678
- Role: Standard User

## Troubleshooting

### "Page not found" when accessing /login
- Make sure you're accessing: `http://localhost/kanisa/church-cms/public/login`
- Or set up .htaccess rewrite and access: `http://localhost/kanisa/church-cms/login`

### Database connection error
- Check database credentials in `app/config.php`
- Verify MySQL is running
- Verify church_cms database exists
- Verify schema.sql was imported successfully

### "Error: Unauthenticated" on first load
- This is expected. You need to log in first at `/login`
- Use credentials from above

## Project Structure

```
church-cms/
├── public/              # Web root - point Apache here
│   ├── index.php       # Main router and front controller
│   └── assets/css/     # Stylesheets
├── app/
│   ├── config.php      # Configuration
│   ├── core/           # Core classes (Auth, Database, Response, Audit)
│   ├── controllers/    # Page and API controllers
│   └── views/          # PHP templates
├── database/
│   ├── schema.sql      # Full database schema
│   └── sample_data.sql # Demo data
├── docs/               # Documentation
└── storage/logs/       # Log files (if needed)
```

## Key API Endpoints

All authenticated with session:

- `POST /api/v1/auth/login` - Login (JSON)
- `GET /api/v1/dashboard/stats` - Dashboard stats
- `GET /api/v1/members` - List members
- `POST /api/v1/members` - Create member
- `POST /api/v1/finance/entries` - Record finance entry
- `GET /api/v1/events/{id}/report` - Get event report

All endpoints return JSON:
```json
{
  "success": true,
  "message": "...",
  "data": {}
}
```

## Next Steps

After login works:
1. Register members in Members module
2. Create events in Events module
3. Record income/expenses in Finance module
4. Track attendance in Attendance module
5. Manage procurement requests in Procurement module
6. View reports in Reports center
