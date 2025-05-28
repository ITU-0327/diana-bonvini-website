<?php
// Define the env function if it doesn't exist
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}

// Include the database configuration manually
include 'config/app_local.php';

// Get the database configuration
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

// Check if the google_calendar_settings table exists
$result = $conn->query("SHOW TABLES LIKE 'google_calendar_settings'");

if ($result->num_rows == 0) {
    echo "Creating google_calendar_settings table...\n";
    
    // Drop the table if it exists
    $conn->query("DROP TABLE IF EXISTS google_calendar_settings");
    
    // Create the table with the correct collation to match 'users' table
    $sql = "CREATE TABLE google_calendar_settings (
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
    ) ENGINE=InnoDB";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table google_calendar_settings created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
        exit(1);
    }
}

// Get admin users
$sql = "SELECT user_id, email FROM users WHERE user_type = 'admin' AND is_verified = 1 ORDER BY last_login DESC LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " admin users:\n";
    
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["user_id"] . " - Email: " . $row["email"] . "\n";
        
        // Check if user already has calendar settings
        $check = $conn->query("SELECT setting_id FROM google_calendar_settings WHERE user_id = '" . $row["user_id"] . "'");
        
        if ($check->num_rows > 0) {
            echo "User already has Google Calendar settings\n";
        } else {
            // Generate a unique ID
            $settingId = bin2hex(random_bytes(16));
            
            // Create sample token data
            $accessToken = json_encode([
                "access_token" => "sample_access_token_" . bin2hex(random_bytes(8)),
                "refresh_token" => "sample_refresh_token_" . bin2hex(random_bytes(8)),
                "expires_in" => 3600,
                "token_type" => "Bearer"
            ]);
            
            // Insert test settings for the admin
            $sql = "INSERT INTO google_calendar_settings (setting_id, user_id, calendar_id, refresh_token, access_token, is_active)
                    VALUES ('$settingId', '{$row["user_id"]}', 'primary', 'sample_refresh_token', '$accessToken', 1)";
            
            if ($conn->query($sql) === TRUE) {
                echo "Created test Google Calendar settings for {$row["email"]}\n";
            } else {
                echo "Error creating settings: " . $conn->error . "\n";
            }
        }
        
        echo "---------------------------------------------\n";
    }
} else {
    echo "No admin users found\n";
}

$conn->close();
echo "Done\n";
?> 