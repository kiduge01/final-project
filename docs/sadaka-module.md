# Sadaka Module Implementation Guide

## Overview
The Sadaka Module manages church offerings and contributions across four categories:
- **Sadaka za Upendo** - Offerings from love and willing heart
- **Sadaka za Maendeleo** - Development and building fund contributions
- **Mafungu ya Kumi** - Tithes (10% income offerings)
- **Machangizo** - Pledges and special contributions

## Features

### 1. Dropdown Navigation
- Hover over "Sadaka" in the main menu to see the four categories
- Click on any category to view and manage that type of offering

### 2. Data Entry Methods

#### Manual Entry
- Click "Add Entry" button to manually enter a single sadaka record
- Requires: Member selection, Amount, and optional Date, Week, Notes

#### Bulk Upload
- Click "Upload File" to import multiple entries from CSV/Excel
- Supported formats: CSV, XLSX, XLS
- CSV should have columns: `member_code`, `first_name`, `last_name`, `amount`, `date` (optional), `week` (optional), `notes` (optional)

### 3. Data Display
Each category page shows:
- **Members Table**: Lists all active church members
- **Weekly Breakdown**: Columns for Weeks 1-4 showing contributions per week
- **Monthly Total**: Total contributions for the selected month
- **Yearly Total** (highlighted): Total for the entire year to date
- **Actions**: Edit or delete entries

### 4. Monthly/Yearly Filtering
- Select month and year at the top to view data for that period
- Monthly view shows week-by-week breakdown
- Yearly total column always shows the full year total regardless of month selected

## Database Tables

### sadaka_categories
Stores the four types of sadaka offerings

### sadaka_entries
Individual contribution records with:
- Member reference
- Category
- Amount
- Entry date and week
- Notes
- Audit trail (who entered, when)

### sadaka_uploads
Tracks bulk import history for audit purposes

## API Endpoints

- `GET /api/v1/sadaka/categories` - List all sadaka categories
- `GET /api/v1/sadaka/entries/{category-slug}` - Get entries for a category
- `POST /api/v1/sadaka/entries` - Add single entry
- `POST /api/v1/sadaka/upload` - Bulk upload from file
- `DELETE /api/v1/sadaka/entries/{id}` - Delete entry
- `GET /api/v1/sadaka/statistics` - Get yearly statistics

## CSV Upload Format

Sample row:
```
member_code,first_name,last_name,amount,date,week,notes
MEM001,John,Doe,50000,2026-06-01,1,Regular contribution
```

**Required columns**: member_code OR (first_name + last_name), amount
**Optional columns**: date, week, notes

If date is not provided, today's date is used.
If week is not provided, no week tracking is recorded.

## Permissions
The Sadaka module uses the `finance.view` permission.
Users must have Finance view permission to access Sadaka management.

## Notes
- All amounts are in Tanzania Shillings (TZS)
- Member code or name matching is used to associate entries with members
- Audit logs track all sadaka entries and bulk uploads
- Yearly totals are automatically calculated from all entries in the year
