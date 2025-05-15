-- Update appointments table to include more fields for Google Calendar integration
ALTER TABLE appointments 
    ADD COLUMN meeting_link VARCHAR(255) DEFAULT NULL AFTER google_calendar_event_id,
    ADD COLUMN writing_service_request_id CHAR(9) DEFAULT NULL AFTER user_id,
    ADD COLUMN description TEXT DEFAULT NULL AFTER duration,
    ADD COLUMN location VARCHAR(255) DEFAULT NULL AFTER description,
    ADD COLUMN is_google_synced BOOLEAN NOT NULL DEFAULT FALSE AFTER status;

-- Add foreign key constraint if missing (only if the tables exist)
SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'writing_service_requests');

SET @constraint_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                           WHERE TABLE_SCHEMA = DATABASE() 
                           AND TABLE_NAME = 'appointments' 
                           AND CONSTRAINT_NAME = 'fk_appointments_wsr');

SET @sql = CONCAT('SELECT IF(', @exists, ' > 0 AND ', @constraint_exists, ' = 0, 
                  "ALTER TABLE appointments ADD CONSTRAINT fk_appointments_wsr FOREIGN KEY (writing_service_request_id) REFERENCES writing_service_requests(writing_service_request_id) ON DELETE SET NULL", 
                  "SELECT ''No action needed''") INTO @action');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 