<?php
// Include the database configuration manually
include 'config/app_local.php';

$config = $return['Datasources']['default'];

// Create MySQL connection
$conn = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database']
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query for confirmed appointments
$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, 
               a.is_google_synced, a.google_calendar_event_id, u.email as user_email 
        FROM appointments a 
        LEFT JOIN users u ON a.user_id = u.user_id 
        WHERE a.status = 'confirmed' AND a.is_deleted = 0 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC 
        LIMIT 5";

$result = $conn->query($sql);

echo "Found " . $result->num_rows . " confirmed appointments:\n\n";

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Appointment ID: " . $row['appointment_id'] . "\n";
        echo "Date: " . $row['appointment_date'] . " at " . $row['appointment_time'] . "\n";
        echo "Status: " . $row['status'] . "\n";
        echo "Synced to Google Calendar: " . ($row['is_google_synced'] ? 'Yes' : 'No') . "\n";
        echo "Google Calendar Event ID: " . ($row['google_calendar_event_id'] ?: 'Not synced') . "\n";
        echo "User Email: " . $row['user_email'] . "\n";
        echo "-------------------------------------------\n";
    }
}

// Check if google_calendar_settings table exists
$sql = "SHOW TABLES LIKE 'google_calendar_settings'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "\nGoogle Calendar Settings table exists.\n";
    
    // Check Google Calendar settings
    $sql = "SELECT gs.setting_id, gs.user_id, gs.calendar_id, gs.is_active, u.email as user_email 
            FROM google_calendar_settings gs 
            LEFT JOIN users u ON gs.user_id = u.user_id 
            LIMIT 5";
            
    $result = $conn->query($sql);
    
    echo "Found " . $result->num_rows . " Google Calendar settings:\n\n";
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "Setting ID: " . $row['setting_id'] . "\n";
            echo "User ID: " . $row['user_id'] . "\n";
            echo "User Email: " . $row['user_email'] . "\n";
            echo "Calendar ID: " . $row['calendar_id'] . "\n";
            echo "Active: " . ($row['is_active'] ? 'Yes' : 'No') . "\n";
            echo "-------------------------------------------\n";
        }
    }
} else {
    echo "\nGoogle Calendar Settings table does not exist.\n";
}

$conn->close();
?> 