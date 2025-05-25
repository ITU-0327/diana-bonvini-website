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
    'clientId' => '262189815866-vg63735th3sgsr57ns33mr259t0sacop.apps.googleusercontent.com',
    'clientSecret' => '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',
    'projectId' => 'dianbonvini',
    'redirectUri' => 'http://localhost:8765/admin/google-auth/callback',
],
```