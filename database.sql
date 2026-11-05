-- ============================================================================
-- FCDF PRO — Foreign Currency Deposit Form System
-- Database Schema (MySQL 8.x / MariaDB, InnoDB, utf8mb4)
-- Walton Hi-Tech Industries PLC (WHIPLC)
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- users
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    phone VARCHAR(30) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at DATETIME DEFAULT NULL,
    twofa_enabled TINYINT(1) NOT NULL DEFAULT 0,
    twofa_secret VARCHAR(64) DEFAULT NULL,
    failed_login_count INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- email_verifications
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ev_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- password_resets
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Master data tables
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS legal_entities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS operating_units (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    legal_entity_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ou_entity FOREIGN KEY (legal_entity_id) REFERENCES legal_entities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    legal_entity_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pr_entity FOREIGN KEY (legal_entity_id) REFERENCES legal_entities(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visited_countries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS purposes_of_tour (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS signatories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    designation VARCHAR(150) DEFAULT NULL,
    role ENUM('depositor','verifier','receiver','approved') NOT NULL DEFAULT 'depositor',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS currencies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_currencies_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS terms_conditions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seed_key VARCHAR(50) DEFAULT NULL, -- stable key for default seed rows; prevents duplicate re-import
    content TEXT NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_terms_seed_key (seed_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- deposit_forms / deposit_currency_lines
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS deposit_forms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(40) NOT NULL UNIQUE,
    visited_country_id INT UNSIGNED DEFAULT NULL,
    deposit_date DATE NOT NULL,
    name VARCHAR(150) NOT NULL,
    id_no VARCHAR(60) DEFAULT NULL,
    department_id INT UNSIGNED DEFAULT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    operating_unit_id INT UNSIGNED DEFAULT NULL,
    legal_entity_id INT UNSIGNED DEFAULT NULL,
    ebs_number VARCHAR(60) DEFAULT NULL,
    purpose_of_tour_id INT UNSIGNED DEFAULT NULL,
    purpose_of_tour_text VARCHAR(255) DEFAULT NULL,
    total_bdt_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    amount_in_words VARCHAR(500) DEFAULT NULL,
    special_note TEXT DEFAULT NULL,
    depositor_signatory_id INT UNSIGNED DEFAULT NULL,
    depositor_manual_name VARCHAR(150) DEFAULT NULL,  -- used when Depositor is entered manually instead of chosen from Master Data
    depositor_manual_id VARCHAR(60) DEFAULT NULL,
    depositor_manual_phone VARCHAR(30) DEFAULT NULL,
    verified_by_signatory_id INT UNSIGNED DEFAULT NULL,
    received_by_signatory_id INT UNSIGNED DEFAULT NULL,
    status ENUM('draft','submitted') NOT NULL DEFAULT 'submitted',
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_df_country FOREIGN KEY (visited_country_id) REFERENCES visited_countries(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_ou FOREIGN KEY (operating_unit_id) REFERENCES operating_units(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_le FOREIGN KEY (legal_entity_id) REFERENCES legal_entities(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_purpose FOREIGN KEY (purpose_of_tour_id) REFERENCES purposes_of_tour(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_depositor FOREIGN KEY (depositor_signatory_id) REFERENCES signatories(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_verifier FOREIGN KEY (verified_by_signatory_id) REFERENCES signatories(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_receiver FOREIGN KEY (received_by_signatory_id) REFERENCES signatories(id) ON DELETE SET NULL,
    CONSTRAINT fk_df_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_deposit_date (deposit_date),
    INDEX idx_department (department_id),
    INDEX idx_legal_entity (legal_entity_id),
    INDEX idx_operating_unit (operating_unit_id),
    INDEX idx_country (visited_country_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS deposit_currency_lines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deposit_form_id INT UNSIGNED NOT NULL,
    currency_id INT UNSIGNED NOT NULL,
    amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    rate DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
    bdt_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_dcl_form FOREIGN KEY (deposit_form_id) REFERENCES deposit_forms(id) ON DELETE CASCADE,
    CONSTRAINT fk_dcl_currency FOREIGN KEY (currency_id) REFERENCES currencies(id),
    INDEX idx_dcl_form (deposit_form_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sequential per-month invoice counter (locking read target)
-- NOTE: the column is named `period_ym` (not `year_month`) because
-- YEAR_MONTH is a reserved keyword in MySQL/MariaDB and breaks CREATE TABLE
-- without backticks.
CREATE TABLE IF NOT EXISTS invoice_counters (
    period_ym CHAR(7) NOT NULL PRIMARY KEY, -- format YYYY-MM
    last_seq INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- audit_logs
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    user_name VARCHAR(150) DEFAULT NULL,
    action VARCHAR(60) NOT NULL,
    entity VARCHAR(60) DEFAULT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    details VARCHAR(500) DEFAULT NULL,
    ip_address VARCHAR(60) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- export_logs (record of PDF/JPG/PNG/Print export events)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS export_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    deposit_form_id INT UNSIGNED DEFAULT NULL,
    export_type ENUM('pdf','jpg','png','print') NOT NULL,
    context VARCHAR(60) DEFAULT NULL, -- e.g. invoice, report
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_export_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- settings (key/value application settings)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS app_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SEED DATA
-- ============================================================================

INSERT IGNORE INTO currencies (code, name, is_active) VALUES
('USD', 'US Dollar', 1),
('EUR', 'Euro', 1),
('RMB', 'Chinese Yuan (Renminbi)', 1),
('INR', 'Indian Rupee', 1),
('THB', 'Thai Baht', 1),
('GBP', 'British Pound', 1);

INSERT IGNORE INTO terms_conditions (seed_key, content, sort_order, is_active) VALUES
('default_1', 'Only USD, RMB, and EURO currencies are permitted for deposit. Coins and damaged notes are not accepted. However, Bangladeshi Taka should be deposited directly with the Accounts/Cash Department at both the Corporate Office and Headquarters.', 1, 1),
('default_2', 'As per office policy, all foreign tour advances must be cleared within seven (7) working days after return to Bangladesh. Associated bills should be submitted for adjustment to the Bill Verification Section.', 2, 1),
('default_3', 'To verify account status, check your ledger one to two working days after this email date. This confirms that the Accounts Department has accurately recorded the transaction in the system.', 3, 1),
('default_4', 'Foreign currency deposits should be made directly at the Corporate Office. If unavailable in person, arrange a trusted individual to deposit on your behalf. Sending currency via envelope or third party is strictly prohibited. Confirmation and receipt copy will be shared upon successful deposit.', 4, 1);

INSERT IGNORE INTO app_settings (setting_key, setting_value) VALUES
('app_name', 'FCDF Pro'),
('support_contact_name', 'Md. Arifur Rahman Khan'),
('support_contact_phone', '+88-01678-049106'),
('dev_admin_name', 'Sazzad Kabir'),
('dev_admin_email', 'sazzadmbstu@gmail.com'),
('dev_admin_phone', '+88-01810-076761'),
('dev_support_phone', '+88-01678-028928');

-- NOTE: The initial Administrator account is NOT seeded here with a hard-coded
-- password hash (that would be insecure and non-portable across PHP builds).
-- Instead, run install.php ONCE from the project root after importing this
-- schema (see README.md -> "Installation"). It creates/updates the admin
-- account "sazzadmbstu@gmail.com" with a securely generated password_hash()
-- for the temporary password "Walton@7130#BD", then DELETE install.php.
