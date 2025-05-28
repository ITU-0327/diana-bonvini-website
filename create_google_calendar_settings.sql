-- First check if the table already exists
SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'google_calendar_settings');

-- Only create the table if it doesn't exist
SET @sql = IF(@exists = 0, 
  'CREATE TABLE google_calendar_settings (
    setting_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    calendar_id VARCHAR(255) NOT NULL,
    refresh_token TEXT,
    access_token TEXT,
    token_expires DATETIME,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gcs_user (user_id),
    CONSTRAINT fk_gcs_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
  ) ENGINE=InnoDB',
  'SELECT "Google Calendar Settings table already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 