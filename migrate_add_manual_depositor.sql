-- ============================================================================
-- FCDF PRO — Migration: Add Manual Depositor Entry Fields
-- ============================================================================
-- Only needed if you are UPGRADING an existing database rather than doing a
-- fresh import of database.sql. Adds the 3 new columns that support typing
-- the Depositor's Name/ID/Phone manually instead of choosing from Master
-- Data (Signatories).
--
-- 100% SAFE to run multiple times — ADD COLUMN IF NOT EXISTS does nothing
-- if the column already exists.
--
-- HOW TO USE: phpMyAdmin -> select your database -> SQL tab -> paste this
-- file's content -> Go.
-- ============================================================================

ALTER TABLE deposit_forms
    ADD COLUMN IF NOT EXISTS depositor_manual_name VARCHAR(150) DEFAULT NULL AFTER depositor_signatory_id,
    ADD COLUMN IF NOT EXISTS depositor_manual_id VARCHAR(60) DEFAULT NULL AFTER depositor_manual_name,
    ADD COLUMN IF NOT EXISTS depositor_manual_phone VARCHAR(30) DEFAULT NULL AFTER depositor_manual_id;
