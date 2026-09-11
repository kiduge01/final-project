-- Migration: Add Department Head Login Credentials
-- Date: 2026-04-09
-- Purpose: Add email and password columns for separate department head login system
-- Note: Keeps existing head_user_id for admin relationship

SET NAMES utf8mb4;

-- ─── 1. Add head_email and head_password_hash columns (if not exist) ───
-- These are used for the separate department subsystem login (/department/auth/login.php)
-- Separate from the main admin system (which uses head_user_id)

ALTER TABLE departments
  ADD COLUMN IF NOT EXISTS head_email VARCHAR(100) NULL UNIQUE AFTER description,
  ADD COLUMN IF NOT EXISTS head_password_hash VARCHAR(255) NULL AFTER head_email;

-- ─── 2. Add index for email lookups (login performance) ───
ALTER TABLE departments
  ADD INDEX IF NOT EXISTS idx_departments_head_email (head_email);
