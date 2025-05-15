-- Fix collation issue between google_calendar_settings and users tables
ALTER TABLE google_calendar_settings MODIFY user_id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

-- Verify if google_calendar_settings table has any records
SELECT COUNT(*) FROM google_calendar_settings;

-- Check admin users that need Google Calendar settings
SELECT user_id, email, first_name, last_name 
FROM users 
WHERE user_type = 'admin' AND is_verified = 1 
ORDER BY last_login DESC 
LIMIT 5; 