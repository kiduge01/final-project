# Department Subsystem - Documentation

## 🎯 Architecture Overview

The Department Subsystem operates on a **dual authentication system**:

### System 1: Main Admin System
- **Purpose**: Church administrators manage overall operations
- **Login**: `http://localhost/Cmain/public` (main admin interface)
- **Authentication**: Main admin users (from `users` table)
- **Functions**: Create departments, assign department heads, manage global settings
- **Department Link**: Established via `departments.head_user_id` (FK to users table)

### System 2: Department Head Login System
- **Purpose**: Department heads manage their own department independently
- **Login**: `http://localhost/Cmain/department/auth/login.php` (separate subsystem)
- **Authentication**: Department-specific credentials (from `departments` table)
  - Email: `departments.head_email`
  - Password: `departments.password` (hashed)
- **Functions**: Manage members, leaders, finances, and submit reports
- **Isolation**: Each department head sees ONLY their own department data

### How They Work Together

```
ADMIN CREATES DEPARTMENT
        ↓
Admin goes to Settings → Departments
Admin clicks "Add Department"
        ↓
ADMIN ASSIGNS HEAD USER
        ↓
Select a main admin user as department head
(This sets departments.head_user_id)
        ↓
ADMIN SETS HEAD CREDENTIALS
        ↓
Admin fills in head_email and password
(This sets departments.head_email and departments.password)
        ↓
DEPARTMENT HEAD CAN LOGIN
        ↓
Department head visits /department/auth/login.php
Logs in with their email and password
Access to separate dashboard with members, finance, reports
```

---

## Quick Start

### 1. Run Database Migrations

The migrations will:
1. Add email/password columns to existing `departments` table (keeps `head_user_id`)
2. Create new tables for members, leaders, reports
3. Add department support to finance table

```sql
-- Run these migrations in order:
1. database/migrations/2026_04_09_001_modify_departments_for_separate_login.sql
2. database/migrations/2026_04_09_002_create_department_members_junction.sql
```

**Tables Added/Modified:**
- `departments` - Added: `head_email`, `password` (kept: `head_user_id`)
- `department_members` - NEW: Junction table linking members to departments
- `department_leaders` - NEW: Leadership roles within departments
- `department_reports` - NEW: Reports submitted by departments
- `finance_entries` - MODIFIED: Added `department_id` column

### 2. Admin Creates Department & Sets Credentials

**In main admin panel:**

1. Login to `http://localhost/Cmain/public`
2. Go to **Settings → Departments**
3. Click **"Add Department"** or **"Edit Department"**
4. Fill in:
   - **Department Name** (e.g., "Ibada")
   - **Head User** (select from main admin users)
   - **Head Email** (e.g., `ibada@church.local`)
   - **Head Password** (e.g., `secure_password_123`)
   - **Confirm Password**
5. Click **"Save"**

### 3. Department Head Logs In

**In browser:**

1. Navigate to: `http://localhost/Cmain/department/auth/login.php`
2. Enter:
   - **Email**: The email set by admin (e.g., `ibada@church.local`)
   - **Password**: The password set by admin
3. Click **"Login"**
4. Redirected to Department Dashboard

### 4. Department Head Uses Subsystem

Once logged in, department head can:
- View and manage members
- View and manage leaders
- Record income and expenses
- Create and submit reports
- View department statistics

---

## Features

### Authentication
- **Separate Login System**: Department heads use their own login separate from the main admin system
- **Session Management**: Secure session handling with regeneration after login
- **Logout Function**: Clean session destruction on logout
- **Audit Logging**: All login/logout activities logged to database

### Dashboard
- Welcome message with department name
- Quick statistics:
  - Total Members
  - Total Leaders
  - Account Balance
  - Pending Reports
- Quick action buttons
- Finance summary
- Department information display

### Members Management
- **View Members**: List all members assigned to the department
- **Add Member**: Register new members with full profile
- **Edit Member**: Update member details
- **Delete Member**: Remove member from department (many-to-many relationship)
- **Search**: Find members by name or phone

### Leaders Management
- **View Leaders**: Display all leadership roles
- **Add Leader**: Assign leadership positions
- **Delete Leader**: Remove leaders from department
- **Flexible Roles**: Support for multiple role types (Chairperson, Secretary, Treasurer, etc.)

### Finance Management
- **View Finances**: Browse all income and expense records filtered by date range
- **Add Income**: Record income transactions (Tithe, Offering, Donation, Grants, etc.)
- **Add Expense**: Record expense transactions (Supplies, Maintenance, Event, etc.)
- **Delete Records**: Remove transactions (soft delete - mark as deleted)
- **Balance Calculation**: Automatic calculation of department balance
- **Filtering**: Filter by type, category, date range

### Reports Management
- **Create Report**: Write detailed department reports (saved as draft)
- **View Reports**: Display all reports with status tracking
- **Submit Report**: Submit draft reports to admin for review
- **Status Tracking**: Drafts → Submitted → Approved/Rejected
- **Admin Review**: Admin can review and add notes to reports

---

## File Structure

```
/department/
├── auth/                    # Authentication pages
│   ├── login.php           # Login form
│   ├── login_process.php   # Process login credentials
│   └── logout.php          # Logout handler
│
├── dashboard/              # Dashboard
│   └── index.php           # Main dashboard with stats
│
├── members/                # Members management
│   ├── view.php            # List members
│   ├── add.php             # Add member form
│   ├── edit.php            # Edit member form
│   └── delete.php          # Delete member
│
├── leaders/                # Leaders management
│   ├── view.php            # List leaders
│   ├── add.php             # Add leader form
│   └── delete.php          # Delete leader
│
├── finance/                # Finance management
│   ├── view.php            # View finance records
│   ├── add_income.php      # Record income
│   ├── add_expense.php     # Record expense
│   └── delete.php          # Delete transaction
│
├── reports/                # Reports management
│   ├── view.php            # List reports
│   ├── create.php          # Create new report
│   ├── view-detail.php     # View single report
│   └── submit.php          # Submit report to admin
│
├── includes/               # Shared includes
│   ├── db.php             # Database connection
│   ├── session.php        # Session management
│   ├── auth_check.php     # Authentication verification
│   ├── header.php         # Header template
│   ├── sidebar.php        # Sidebar navigation
│   └── footer.php         # Footer template
│
└── assets/                 # Static assets
    ├── css/
    │   └── department.css  # Main stylesheet
    └── js/
        └── department.js   # JavaScript utilities
```

---

## Database Schema

### departments (Modified)
```sql
id              - Primary key
name            - Department name (unique)
description     - Department description
head_user_id    - FK to users table (ADMIN RELATIONSHIP - links to main admin user)
head_email      - Department head email (unique, used for department subsystem login)
password        - Hashed password (used for department subsystem login)
is_active       - Department status (1=active, 0=inactive)
created_at      - Creation timestamp
updated_at      - Last update timestamp

IMPORTANT:
- head_user_id: Allows main admin to assign this department to a main admin user
- head_email + password: Allows that person to login to the separate department subsystem
- Both can coexist (e.g., an admin user can be assigned as head AND have separate subsystem credentials)
```

### department_members (New)
```sql
id              - Primary key
department_id   - FK to departments
member_id       - FK to members
assigned_date   - When member was assigned
notes           - Role or additional info
created_at      - Creation timestamp
updated_at      - Last update timestamp
```

### department_leaders (New)
```sql
id              - Primary key
department_id   - FK to departments
member_id       - FK to members (optional)
leader_type     - Role (Chairperson, Secretary, etc.)
leader_name     - Leader's full name
email           - Contact email
phone           - Contact phone
bio             - Bio/credentials
is_active       - Status (1=active, 0=inactive)
created_at      - Creation timestamp
updated_at      - Last update timestamp
```

### department_reports (New)
```sql
id              - Primary key
department_id   - FK to departments
title           - Report title
description     - Report content
report_date     - Date of report
category        - Report type
status          - draft|submitted|approved|rejected
submitted_at    - When submitted to admin
reviewed_by     - FK to users (admin who reviewed)
reviewed_at     - When admin reviewed
review_notes    - Admin's feedback
created_at      - Creation timestamp
updated_at      - Last update timestamp
```

---

## Security Features

### Password Protection
- Passwords hashed using PHP's `password_hash()` with default algorithm
- Verified using `password_verify()` during login
- Session regeneration after successful login prevents session fixation attacks

### Session Security
- Secure session management with regenerable ID
- Session cookies with HTTPOnly flag (prevents JavaScript access)
- SameSite=Lax cookie attribute (CSRF protection)
- Session timeout: 1 hour of inactivity

### SQL Injection Prevention
- All database queries use prepared statements with named parameters
- PDO with `ATTR_EMULATE_PREPARES = false` for true parameterized queries

### Output Escaping
- All user-supplied data escaped with `htmlspecialchars()` before output
- Prevents XSS attacks

### Data Isolation
- Each department head can only access their own department's data
- Ownership verification on all edit/delete operations
- Cross-department access denied

### Audit Logging
- All department actions logged to `audit_logs` table
- Tracks: actor, action, entity, timestamp, IP address, user agent
- Supports JSON storage of old/new values for updates

---

## Usage Examples

### Department Head Login
1. Go to `/department/auth/login.php`
2. Enter email and password
3. Click "Login"
4. Redirected to dashboard

### Add a Member
1. Click "Add Member" in sidebar
2. Fill in member details
3. Click "Add Member" button
4. Member automatically assigned to your department

### Record Income
1. Click "Add Income" in sidebar
2. Select date and category
3. Enter amount
4. Add description (optional)
5. Click "Record Income"

### Submit Report
1. Click "Create Report" in sidebar
2. Write report (saved as draft)
3. Click "View Reports"
4. Click "Submit" on the report
5. Report status changes to "submitted" for admin review

---

## Troubleshooting

### "Invalid email or password" on login
- Verify department head email and password are correct
- Check that department is marked as active
- Verify password was hashed with `password_hash()`

### "Permission denied" when viewing member details
- Verify member is assigned to your department
- Check that membership link exists in `department_members` table

### Session expires unexpectedly
- Default session timeout is 1 hour
- Inactive sessions are automatically destroyed
- Login again to continue

### Database connection error
- Verify `app/config.php` has correct database credentials
- Check that database and tables are created
- Run migration files if tables don't exist

---

## Admin Integration Points

The department subsystem is fully integrated with the main admin system:

### 1. Manage Department Head Credentials
**Location**: Main admin → Settings → Departments → Edit Department
- Admin can set/update department head email and password
- Endpoint: `/app/controllers/DepartmentCredentialsController.php`
- Function: Validates email, password, hashes, stores, audits

### 2. View Department Data
**Location**: Main admin dashboard can display:
- Aggregate department member counts
- Department finance summaries
- Pending reports for admin review
- Department activity logs

### 3. Approve/Reject Reports
**Location**: Main admin → Reports section (future)
- Admin can review submitted reports from all departments
- Add review notes
- Update report status (approved/rejected)
- Department head sees status changes

### 4. Financial Consolidation
**Location**: Main Finance module
- Pull department finance_entries with `department_id` filter
- Create reports combining all department financials
- Generate consolidated church statements

### 5. Member Directory
**Location**: Members module (potential enhancement)
- View all members across all departments
- Filter by department
- Cross-department statistics

### 6. Audit Logging
- All department actions logged to `audit_logs` table
- Admin can review all department activities
- Security tracking and compliance

---

## Best Practices

### For Department Heads
1. **Change password regularly** - Once admin feature added
2. **Don't share login credentials** - Keep email/password private
3. **Save reports as drafts first** - Review before submitting to admin
4. **Keep records accurate** - Clear descriptions help admin understand activities
5. **Log out when done** - Secure your session

### For Administrators
1. **Create strong passwords** - Ensure department head passwords are secure
2. **Review reports promptly** - Provide feedback on submitted reports
3. **Monitor audit logs** - Check for unauthorized access attempts
4. **Archive old reports** - Move completed reports to archive
5. **Backup database regularly** - Protect against data loss

---

## Support & Customization

### Adding New Features
- Follow the existing pattern for new modules
- Always include ownership verification
- Log all state-changing actions
- Test thoroughly before deployment

### Database Extensions
- Add fields to existing tables using ALTER TABLE migrations
- Create junction tables for relationships (like department_members)
- Always include timestamps (created_at, updated_at)

### Styling Customization
- Modify `/assets/css/department.css` for design changes
- Color scheme: Dark primary (#1a1a2e) with gold accents (#d4af37)
- Responsive breakpoints: 1024px, 768px, 480px

---

## Contact & Issues

For issues or questions:
1. Check database connection settings
2. Review error logs in browser console
3. Check server error logs
4. Verify migration files were run successfully

---

**Last Updated**: April 9, 2026
**Version**: 1.0
**Status**: Production Ready
