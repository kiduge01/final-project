-- Messages table to group broadcast sends
CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(200) NULL,
    message_text VARCHAR(480) NOT NULL,
    recipient_type ENUM('all', 'members', 'groups') NOT NULL DEFAULT 'all',
    recipient_ids JSON NULL COMMENT 'Array of member_ids or group_ids when not "all"',
    recipient_count INT UNSIGNED NOT NULL DEFAULT 0,
    sent_count INT UNSIGNED NOT NULL DEFAULT 0,
    failed_count INT UNSIGNED NOT NULL DEFAULT 0,
    channel ENUM('sms', 'email', 'both') NOT NULL DEFAULT 'sms',
    status ENUM('queued', 'sending', 'sent', 'partial', 'failed') NOT NULL DEFAULT 'queued',
    sent_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_sent_by FOREIGN KEY (sent_by) REFERENCES users(id),
    INDEX idx_messages_status (status),
    INDEX idx_messages_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add message_id column to sms_logs to link individual SMS to a broadcast
ALTER TABLE sms_logs ADD COLUMN message_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE sms_logs ADD CONSTRAINT fk_sms_logs_message FOREIGN KEY (message_id) REFERENCES messages(id);
ALTER TABLE sms_logs ADD INDEX idx_sms_logs_message_id (message_id);
