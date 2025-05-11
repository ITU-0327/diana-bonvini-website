# Google Calendar Integration Setup Guide

This document provides instructions for setting up the Google Calendar integration for the Writing Service Appointment System.

## Prerequisites

- Google Cloud Platform account
- Access to the application server
- Admin privileges in the application

## Step 1: Create a Google Cloud Project

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Click "New Project" and create a new project with a descriptive name (e.g., "Writing Service Calendar")
3. Wait for the project to be created, then select it from the dashboard

## Step 2: Enable the Google Calendar API

1. In your Google Cloud project, navigate to "APIs & Services" > "Library"
2. Search for "Google Calendar API" and select it
3. Click "Enable" to enable the Calendar API for your project

## Step 3: Create OAuth 2.0 Credentials

1. In your Google Cloud project, navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" and select "OAuth client ID"
3. If prompted, configure the OAuth consent screen:
   - Choose "External" user type (or "Internal" if your organization uses Google Workspace)
   - Enter a name for your app (e.g., "Writing Service Appointment System")
   - Add your domain to the "Authorized domains" section
   - Enter your email address for developer contact information
   - Set the necessary scopes:
     - `https://www.googleapis.com/auth/calendar`
     - `https://www.googleapis.com/auth/calendar.events`
   - Click "Save and Continue" through the remaining steps
   
4. Create the OAuth client ID:
   - Select "Web application" as the application type
   - Enter a name for the client (e.g., "Writing Service Calendar Client")
   - Add authorized JavaScript origins:
     - `http://localhost:8765` (for local development)
     - `https://your-domain.com` (for production)
   - Add authorized redirect URIs:
     - `http://localhost:8765/admin/google-auth/callback` (for local development)
     - `https://your-domain.com/admin/google-auth/callback` (for production)
   - Click "Create"
   
5. Note your Client ID and Client Secret, you'll need these for the next step

## Step 4: Configure API Credentials

1. Update the Google Calendar configuration in your `config/app_local.php` file:

```php
'GoogleCalendar' => [
    'clientId' => '390449927688-id7k14lnvrs3g9do9supst54nog7clgd.apps.googleusercontent.com',
    'clientSecret' => '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',
    'projectId' => 'inspired-cortex-457012-s6',
    'redirectUri' => 'http://localhost:8765/admin/google-auth/callback',
],
```

Note: For local development, we're using localhost:8765 as the base URL. For production, you would change this to your actual domain.

2. Save the file and restart your application if necessary

## Step 5: Connect Google Calendar in the Admin Interface

1. Log in to the application as an admin user
2. Navigate to "Admin" > "Google Auth" (or directly to `/admin/google-auth`)
3. Click "Connect with Google Calendar"
4. You'll be redirected to Google's authorization screen
5. Select the Google account you want to use for the calendar integration
6. Grant the requested permissions
7. You'll be redirected back to the application with a success message if authentication is successful

## Step 6: Testing the Integration

1. Navigate to "Admin" > "Calendar"
2. Try creating a new appointment
3. Verify that the appointment appears in the connected Google Calendar
4. Check that meeting links are generated correctly

## Step 7: Setting Up Appointment Reminders

The system includes a command to send automatic reminders for upcoming appointments. To schedule this:

1. Set up a daily cron job to run the reminder command:

```bash
# Run appointment reminders every day at 9:00 AM
0 9 * * * cd /path/to/application && bin/cake send_appointment_reminders
```

2. You can test the command manually with:

```bash
# Test in dry-run mode (no emails sent)
bin/cake send_appointment_reminders --dry-run

# Actually send emails
bin/cake send_appointment_reminders
```

## Troubleshooting

### Authentication Issues

- If you encounter "Invalid Client" errors, verify that your redirect URI exactly matches what's configured in Google Cloud Console
- If tokens expire unexpectedly, check your server's date and time settings
- Ensure your OAuth consent screen is properly configured and published (if using external user type)

### Calendar Syncing Issues

- If appointments don't appear in Google Calendar, check the application logs for error messages
- Verify that the Google account has write permissions for the calendar being used
- Check that the application can properly refresh access tokens

### Email Notification Issues

- If confirmation emails aren't being sent, check the email configuration in your application
- Verify that the reminder cron job is running correctly

## Additional Resources

- [Google Calendar API Documentation](https://developers.google.com/calendar)
- [OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server)
- [CakePHP Email Configuration](https://book.cakephp.org/4/en/core-libraries/email.html)

## Security Considerations

- Never commit your `.env` file or any files containing API credentials to version control
- Use environment variables for all sensitive credentials
- Regularly review and rotate API credentials
- Limit the OAuth scopes requested to only what's necessary
- Keep your Google Cloud project and API dependencies updated