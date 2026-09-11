-- ============================================================================
-- MIGRATION: 2026-04-07 - Expand Members Table with Detailed Fields
-- ============================================================================
-- Purpose:
--   Extend the members table to capture comprehensive member information
--   from the Google Form (Fomu Ya Ushirika) including geographic, professional,
--   financial, and service-related details.
--
-- Author: Church CMS Development Team
-- Date: April 7, 2026
-- Version: 1.0
--
-- Related Files:
--   - app/views/pages/members.php (UI form)
--   - app/controllers/ApiController.php (saveMember & updateMember functions)
--   - database/migrations/2026_04_07_001_add_member_details_fields.sql (this file)
--
-- Changes Made:
--   - Added 14 new columns to the members table
--   - Created 3 performance indexes for improved query speed
--   - All new columns are optional (nullable) for backward compatibility
-- ============================================================================

-- GEOGRAPHIC & LOCATION FIELDS
-- Capture member's location information
ALTER TABLE members ADD COLUMN city_village VARCHAR(100) NULL COMMENT 'Mji/Kijiji - City or village name';
-- Country/Nchi - Defaults to Tanzania for most members
ALTER TABLE members ADD COLUMN country VARCHAR(100) NOT NULL DEFAULT 'Tanzania' COMMENT 'Nchi - Country of residence';
-- PROFESSIONAL FIELDS
-- Track member's education and employment status for demographic analysis
-- Supports: Primary, Secondary, Diploma, Bachelor, Masters, PhD
ALTER TABLE members ADD COLUMN education_level VARCHAR(100) NULL COMMENT 'Kiwango cha Elimu - Educational attainment';
-- Job title or position for identifying skill sets within congregation
ALTER TABLE members ADD COLUMN job_title VARCHAR(150) NULL COMMENT 'Cheo Kazi/Majukumu Kazini - Professional title/role';
-- CHURCH SERVICE & COMMITMENT FIELDS
-- Track member involvement, service opportunities, and commitment level
-- Examples: Choir, Ushering, Prayer Team, Sound System, Nursery, etc.
ALTER TABLE members ADD COLUMN church_services TEXT NULL COMMENT 'Huduma Kanisani - Church services/ministries member participates in';
-- Boolean (1/0) indicating whether member is fully committed to their assigned service
ALTER TABLE members ADD COLUMN is_doing_service_fully TINYINT(1) NULL COMMENT 'Je Unafanya Huduma Yako - Fully performing assigned duties (1=Yes, 0=No)';
-- Service level: Beginner, Active, Leader, etc. - for progression tracking
ALTER TABLE members ADD COLUMN service_level VARCHAR(100) NULL COMMENT 'Daraja la Huduma - Service level/status (Leader, Active, Beginner)';
-- FINANCE & TITHE FIELDS
-- Track member's financial contributions and tithing commitment
-- Boolean (1/0) indicating consistent tithe payment behavior
ALTER TABLE members ADD COLUMN pays_tithes_faithfully TINYINT(1) NULL COMMENT 'Je Unatoa zaka - Pays tithes faithfully (1=Yes, 0=No)';
-- Bank account number for tithe deposit/transfer tracking
ALTER TABLE members ADD COLUMN account_number VARCHAR(50) NULL COMMENT 'Nambari Ya Bahasha - Bank account for tithe payments';
-- Monthly tithe amount committed by member for budget planning
ALTER TABLE members ADD COLUMN tithe_amount_monthly DECIMAL(10, 2) NULL COMMENT 'Kiasi cha Zaka kila Mwezi - Monthly tithe commitment (TZS)';
-- EMERGENCY CONTACT & DETAILED ADDRESS FIELDS
-- Enhanced emergency contact information beyond existing field
-- Relationship type: Spouse, Sibling, Parent, Child, Friend, Relative
ALTER TABLE members ADD COLUMN emergency_contact_relationship VARCHAR(100) NULL COMMENT 'Uhusiano - Relationship type of emergency contact';
-- Email address for emergency contact as supplement to phone
ALTER TABLE members ADD COLUMN emergency_contact_email VARCHAR(150) NULL COMMENT 'Baruapepe ya Dharura - Emergency contact email';
-- Additional phone number for member (mobile, corporate, etc.)
ALTER TABLE members ADD COLUMN alt_phone_2 VARCHAR(30) NULL COMMENT 'Alternative phone 2 - Additional contact number';
-- Detailed street address for correspondence and visitation
ALTER TABLE members ADD COLUMN physical_address_detailed VARCHAR(255) NULL COMMENT 'Anuani - Detailed physical address (street, building)';

-- PERFORMANCE INDEXES
-- Created for frequently used queries and report generation
-- Used for filtering members by country (international congregation tracking)
ALTER TABLE members ADD INDEX idx_members_country (country);
-- Used for education-based demographic reports and planning
ALTER TABLE members ADD INDEX idx_members_education (education_level);
-- Used for service-based analysis and team assignment reports
ALTER TABLE members ADD INDEX idx_members_service_level (service_level);
