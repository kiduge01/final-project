-- Migration: Add head_name column to departments
-- Date: 2026-04-10
-- Purpose: Store the name of department head for UI display

ALTER TABLE departments
  ADD COLUMN IF NOT EXISTS head_name VARCHAR(150)  AFTER description;
