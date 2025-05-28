# Google Calendar Integration Setup

This document describes how to set up and use the Google Calendar integration for appointment synchronization.

## Overview

When a customer accepts a meeting slot, the appointment is automatically synchronized with the admin's Google Calendar. This creates a Google Meet link that will be sent to both the admin and customer for their virtual meeting.

## Setup for Administrators

### 1. Google API Configuration (Admin/IT Only)

The Google Calendar integration requires setting up Google API credentials:

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Navigate to "APIs & Services" > "Library"
4. Search for and enable the "Google Calendar API"
5. Navigate to "APIs & Services" > "Credentials"
6. Create an OAuth 2.0 Client ID with the following settings:
   - Application type: Web application
   - Name: Writing Service Calendar Integration
   - Authorized redirect URIs: Add your application's URI + `/admin/calendar/oauth-callback`
     - Example: `https://yourdomain.com/admin/calendar/oauth-callback`
     - For local development: `http://localhost:9030/admin/calendar/oauth-callback`
7. Download the client secret JSON file and save it
8. Update the application's configuration with these credentials in `config/app_local.php` or using environment variables:

```php
'GoogleCalendar' => [
    'clientId' => 'YOUR_CLIENT_ID',
    'clientSecret' => 'YOUR_CLIENT_SECRET',
    'redirectUri' => 'YOUR_REDIRECT_URI',
    'scopes' => [
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/calendar.events',
    ],
],
```

### 2. Administrator Account Setup

Each administrator who wants to use the Google Calendar integration needs to connect their account:

1. Log in to the application as an administrator
2. Go to "Admin" > "Settings" > "Google Calendar"
3. Click "Connect Google Calendar"
4. Follow the OAuth flow to grant the application access to your Google Calendar
5. Select which Google Calendar to use for appointments (typically your primary calendar)
6. Click "Save Settings"

## Automatic Synchronization

Appointments are automatically synchronized with Google Calendar when:

1. A customer accepts a meeting slot (immediate sync)
2. An administrator manually syncs an appointment from the admin dashboard
3. The scheduled task runs (every hour)

## Manual Synchronization

Administrators can manually synchronize appointments from the admin dashboard:

1. Go to "Admin" > "Appointments"
2. Find the appointment you want to sync
3. Click "Sync with Google Calendar"

## Command Line Tools

### For Developers/Admins

Several command line tools are available for managing the Google Calendar integration:

1. Check appointments and their sync status:
   ```
   bin/cake check_appointments
   ```

2. Create test Google Calendar settings for an admin user:
   ```
   bin/cake create_test_calendar_settings <admin_id>
   ```

3. Manually sync a specific appointment:
   ```
   bin/cake sync_appointment <appointment_id> <admin_id>
   ```

4. Bulk sync all unsynced appointments:
   ```
   bin/cake bulk_sync_appointments <admin_id>
   ```

5. Automatic sync (can be scheduled via cron):
   ```
   bin/cake auto_sync_appointments
   ```

## Troubleshooting

If appointments are not syncing with Google Calendar:

1. Check if the admin user has Google Calendar settings configured
2. Verify the Google API credentials are correct
3. Check the application logs for error messages
4. Run the `check_appointments` command to see the sync status
5. Try manual synchronization using the command line tools

## Setting Up Scheduled Sync

To ensure all appointments are synced regularly, set up a cron job:

```
# Run every hour to sync appointments
0 * * * * cd /path/to/application && bin/cake auto_sync_appointments > /dev/null 2>&1
```

Add this to your crontab by running `crontab -e` and pasting the above line (adjusting the path accordingly). 