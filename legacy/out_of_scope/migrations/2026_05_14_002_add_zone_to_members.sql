-- Migration: Add Zone Column to Members
-- Date: 2026-05-14
-- Purpose: Add zone_id column to members table for direct zone assignment during registration

ALTER TABLE members
ADD COLUMN zone_id BIGINT UNSIGNED NULL AFTER region,
ADD CONSTRAINT fk_members_zone FOREIGN KEY (zone_id) REFERENCES zones(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
ADD INDEX idx_members_zone (zone_id);

-- Update zone_members to sync with members zone_id when set
-- (This will be handled by application logic)
