# Browser Access Issues - Root Cause Analysis

## Problem
Getting "Not Found" error when accessing the project in browser, while other projects work fine.

---

## Issues Identified

### 1. **PRIMARY ISSUE: Conflicting .htaccess Rewrite Rules** ⚠️ CRITICAL
**Location:** Root `.htaccess` and `public/.htaccess`

**The Problem:**
- Root `.htaccess` rewrites all requests to `public/index.php?url=$1`
- Public `.htaccess` then rewrites again to `index.php`
- This creates a potential rewrite loop or incorrect file path resolution

**Current Root .htaccess:**
```apache
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_URI} !^/public/
RewriteCond %{REQUEST_URI} !^/department/
RewriteCond %{REQUEST_URI} !^/assets/
RewriteCond %{REQUEST_URI} !^/database/
RewriteCond %{REQUEST_URI} !^/docs/
RewriteRule ^(.*)$ public/index.php?url=$1 [QSA,L]
```

**Why It's Failing:**
- When you access `/`, the root `.htaccess` rewrites to `public/index.php`
- Apache may be looking for this file literally, not as a rewrite target
- The file path resolution becomes ambiguous

---

### 2. **Root index.php Redirect Conflict**
**Location:** Root `index.php`

```php
<?php
// Redirect to public front controller
header('Location: /public/');
exit;
```

**The Problem:**
- This is only executed if Apache actually serves the root `index.php`
- If `.htaccess` rewrites are happening first, this code never runs
- This creates confusion about how requests are being routed

---

### 3. **BASE_URL Configuration Issue**
**Location:** `app/config.php`

```php
'base_path' => '/public/',  // This assumes /public/ is accessible at root
```

**The Problem:**
- The routing in `public/index.php` tries to strip `BASE_URL` from REQUEST_URI
- If the rewrite rules aren't working correctly, REQUEST_URI still contains `/public/`
- This causes routing logic to fail

**Code in public/index.php (line 54-55):**
```php
if (str_starts_with($uri, BASE_URL)) {
    $uri = substr($uri, strlen(BASE_URL));
}
```

---

### 4. **Request URI Not Being Modified by Rewrite**
**Location:** Request routing in `public/index.php`

**The Problem:**
- Root `.htaccess` passes `?url=$1` parameter but `public/index.php` ignores it
- It uses `$_SERVER['REQUEST_URI']` instead of `$_GET['url']`
- The query parameter is never utilized for routing

**Relevant Code (line 50-60):**
```php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
```

---

## What Works for Other Projects

Other projects likely work because they either:
1. Have `public/` as the **DocumentRoot** in Apache VirtualHost
2. Don't have conflicting `.htaccess` files in root and public directories
3. Use simpler routing without nested rewrite rules

---

## Solutions

### **SOLUTION A: Use /public/ as DocumentRoot (RECOMMENDED)**
This is the proper structure for modern PHP applications.

**What To Do:**
1. Find your Apache/WAMP VirtualHost configuration
2. Set `DocumentRoot` to `c:\wamp64\www\htdocs\public`
3. Delete or simplify the root `.htaccess`
4. Keep only `public/.htaccess` for routing

**Benefits:**
- Clean separation of public/private files
- Matches the app's architecture intent
- Faster performance
- Better security

---

### **SOLUTION B: Fix .htaccess Routing (TEMPORARY)**
If you can't change DocumentRoot, fix the rewrite rules.

**Replace root `.htaccess` with:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Skip rewrite for real files and directories
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]
    
    # Skip specific directories
    RewriteCond %{REQUEST_URI} ^/(public|department|assets|database|docs) [L]
    
    # Rewrite everything else to public/index.php
    RewriteRule ^(.*)$ public/index.php [L]
</IfModule>
```

**Replace public/.htaccess with:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /public/
    
    # Skip actual files and directories
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Route to index.php
    RewriteRule ^ index.php [L]
</IfModule>
```

**Also fix public/index.php (line 50-60):**
```php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Strip /public/ prefix if it exists
if (str_starts_with($uri, '/public/')) {
    $uri = substr($uri, 7);  // Remove '/public/' (7 chars)
}
if ($uri === '' || $uri === false) {
    $uri = '/';
}
```

---

## Testing Steps

After applying a solution, test:

1. **Home page:** http://localhost/htdocs/ (or your project URL)
   - Should show dashboard/login page, NOT "Not Found"

2. **Login page:** http://localhost/htdocs/login
   - Should show login form

3. **API endpoint:** http://localhost/htdocs/api/v1/meta/users
   - Should return JSON (may need auth, but shouldn't be 404)

---

## Additional Checks

- [ ] Apache `mod_rewrite` is enabled
- [ ] `.htaccess` files have correct permissions (readable by Apache)
- [ ] No other `.htaccess` files in parent directories interfering
- [ ] Apache error log shows what's happening: `c:\wamp64\logs\apache_error.log`

---

## Quick Diagnosis Command

Check what Apache is actually doing:
```bash
# Add this to public/index.php temporarily:
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
error_log("SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME']);
```

Check WAMP Apache error log for these entries.

