<?php
// Load the CakePHP bootstrap to get access to the database configuration
require dirname(__FILE__) . '/config/bootstrap.php';

use Cake\Datasource\ConnectionManager;

// Get database connection
$connection = ConnectionManager::get('default');

// Query for confirmed appointments
$appointments = $connection->execute(
    "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, a.is_google_synced, a.google_calendar_event_id, u.email as user_email 
     FROM appointments a 
     LEFT JOIN users u ON a.user_id = u.user_id 
     WHERE a.status = 'confirmed' AND a.is_deleted = 0 
     ORDER BY a.appointment_date DESC, a.appointment_time DESC 
     LIMIT 5"
)->fetchAll('assoc');

echo "Found " . count($appointments) . " confirmed appointments:\n\n";

foreach ($appointments as $appointment) {
    echo "Appointment ID: " . $appointment['appointment_id'] . "\n";
    echo "Date: " . $appointment['appointment_date'] . " at " . $appointment['appointment_time'] . "\n";
    echo "Status: " . $appointment['status'] . "\n";
    echo "Synced to Google Calendar: " . ($appointment['is_google_synced'] ? 'Yes' : 'No') . "\n";
    echo "Google Calendar Event ID: " . ($appointment['google_calendar_event_id'] ?: 'Not synced') . "\n";
    echo "User Email: " . $appointment['user_email'] . "\n";
    echo "-------------------------------------------\n";
}

// Check Google Calendar settings
$settings = $connection->execute(
    "SELECT gs.setting_id, gs.user_id, gs.calendar_id, gs.is_active, u.email as user_email 
     FROM google_calendar_settings gs 
     LEFT JOIN users u ON gs.user_id = u.user_id 
     LIMIT 5"
)->fetchAll('assoc');

echo "\nFound " . count($settings) . " Google Calendar settings:\n\n";

foreach ($settings as $setting) {
    echo "Setting ID: " . $setting['setting_id'] . "\n";
    echo "User ID: " . $setting['user_id'] . "\n";
    echo "User Email: " . $setting['user_email'] . "\n";
    echo "Calendar ID: " . $setting['calendar_id'] . "\n";
    echo "Active: " . ($setting['is_active'] ? 'Yes' : 'No') . "\n";
    echo "-------------------------------------------\n";
}
?> 