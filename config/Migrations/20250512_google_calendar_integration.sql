-- Add Google Calendar settings table
CREATE TABLE google_calendar_settings (
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
) ENGINE=InnoDB;

-- Update appointments table to include more fields for Google Calendar integration
ALTER TABLE appointments 
    ADD COLUMN meeting_link VARCHAR(255) DEFAULT NULL AFTER google_calendar_event_id,
    ADD COLUMN writing_service_request_id CHAR(9) DEFAULT NULL AFTER user_id,
    ADD COLUMN description TEXT DEFAULT NULL AFTER duration,
    ADD COLUMN location VARCHAR(255) DEFAULT NULL AFTER description,
    ADD COLUMN is_google_synced BOOLEAN NOT NULL DEFAULT FALSE AFTER status,
    ADD CONSTRAINT fk_appointments_wsr FOREIGN KEY (writing_service_request_id) 
    REFERENCES writing_service_requests(writing_service_request_id) ON DELETE SET NULL;