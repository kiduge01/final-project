-- Support chatbot agent communication and settings used by the professional UI.
CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(200) NULL,
    message_text VARCHAR(480) NOT NULL,
    recipient_type ENUM('all','members','guests','groups') NOT NULL DEFAULT 'all',
    recipient_ids JSON NULL,
    recipient_count INT UNSIGNED NOT NULL DEFAULT 0,
    sent_count INT UNSIGNED NOT NULL DEFAULT 0,
    failed_count INT UNSIGNED NOT NULL DEFAULT 0,
    channel ENUM('sms','email','both') NOT NULL DEFAULT 'sms',
    status ENUM('queued','sending','sent','partial','failed') NOT NULL DEFAULT 'queued',
    sent_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_messages_status(status),
    INDEX idx_messages_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE messages MODIFY COLUMN recipient_type ENUM('all','members','guests','groups') NOT NULL DEFAULT 'all';

SET @has_provider_message_id = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sms_logs' AND COLUMN_NAME = 'provider_message_id'
);
SET @sql = IF(@has_provider_message_id = 0,
    'ALTER TABLE sms_logs ADD COLUMN provider_message_id VARCHAR(190) NULL AFTER provider',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_sms_updated_at = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sms_logs' AND COLUMN_NAME = 'updated_at'
);
SET @sql = IF(@has_sms_updated_at = 0,
    'ALTER TABLE sms_logs ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_sms_provider_index = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sms_logs' AND INDEX_NAME = 'idx_sms_provider_message_id'
);
SET @sql = IF(@has_sms_provider_index = 0,
    'ALTER TABLE sms_logs ADD INDEX idx_sms_provider_message_id (provider_message_id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO church_settings(setting_key,setting_value) VALUES
('timezone','Africa/Dar_es_Salaam'),
('report_default_period','this_month'),
('session_timeout_minutes','60'),
('notifications_enabled','1')
ON DUPLICATE KEY UPDATE setting_value=setting_value;
