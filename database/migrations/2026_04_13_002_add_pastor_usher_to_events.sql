-- Add pastor_on_duty and usher_on_duty fields to events table
-- Date: 2026-04-13

ALTER TABLE `events` 
ADD COLUMN `pastor_on_duty` VARCHAR(150) NULL AFTER `notes`,
ADD COLUMN `usher_on_duty` VARCHAR(150) NULL AFTER `pastor_on_duty`,
ADD COLUMN `location` VARCHAR(180) NULL AFTER `venue`;

-- Update category enum to use event_type naming if needed for consistency
-- Also remove old fields that are no longer used
ALTER TABLE `events` 
CHANGE COLUMN `category` `event_type` ENUM('service', 'seminar', 'meeting', 'appointment', 'other') NOT NULL DEFAULT 'other';
