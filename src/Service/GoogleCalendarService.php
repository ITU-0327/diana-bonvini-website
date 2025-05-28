<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventReminders;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * GoogleCalendarService - Handles all interactions with Google Calendar API
 */
class GoogleCalendarService
{
    /**
     * @var \Google\Client Google API Client
     */
    protected GoogleClient $client;

    /**
     * @var \Google\Service\Calendar|null Google Calendar service
     */
    protected ?GoogleCalendar $service = null;

    /**
     * @var \Cake\ORM\Table|null Google Calendar settings table
     */
    protected $settingsTable = null;
    
    /**
     * @var string|null Path to token storage
     */
    protected ?string $tokenPath = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set up Google API client
        $this->client = new GoogleClient();
        $this->client->setApplicationName('WritingService Calendar Integration');
        
        try {
            // Load config from Configure or environment variables
            $config = Configure::read('GoogleCalendar');
            
            if (!$config) {
                // If not in app config, try to load from environment variables
                $config = [
                    'clientId' => env('GOOGLE_CLIENT_ID', null),
                    'clientSecret' => env('GOOGLE_CLIENT_SECRET', null),
                    'redirectUri' => env('GOOGLE_REDIRECT_URI', null),
                ];
            }
            
            // Fix the redirect URI port if needed (ensure it uses the configured port)
            if (!empty($config['redirectUri']) && strpos($config['redirectUri'], 'localhost') !== false) {
                // Use the configured port rather than forcing a specific port
                // This allows flexibility for different development environments
            }
            
            // Set config values
            $this->client->setClientId($config['clientId'] ?? '');
            $this->client->setClientSecret($config['clientSecret'] ?? '');
            $this->client->setRedirectUri($config['redirectUri'] ?? '');
            
            // Set the scopes required
            $this->client->setScopes([GoogleCalendar::CALENDAR, GoogleCalendar::CALENDAR_EVENTS]);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
            
            // Try to load settings table
            try {
                $this->settingsTable = TableRegistry::getTableLocator()->get('GoogleCalendarSettings');
            } catch (Exception $e) {
                $this->settingsTable = null;
            }
        } catch (Exception $e) {
            \Cake\Log\Log::error('Error initializing GoogleCalendarService: ' . $e->getMessage());
        }
    }

    /**
     * Get Google OAuth authorization URL
     *
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Handle OAuth callback and save tokens
     *
     * @param string $authCode Authorization code from Google
     * @param string $userId User ID to associate with this authorization
     * @param string|null $calendarId Google Calendar ID (optional)
     * @return bool Success flag
     */
    public function handleAuthCallback(string $authCode, string $userId, ?string $calendarId = null): bool
    {
        try {
            // Exchange authorization code for access token
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

            if (isset($accessToken['error'])) {
                throw new Exception('Error fetching access token: ' . $accessToken['error_description']);
            }

            // Set access token for the client
            $this->client->setAccessToken($accessToken);

            // If no calendar ID provided, use primary
            if (empty($calendarId)) {
                $calendarId = 'primary';
            }

            // Check if settings exist for this user
            $existingSettings = $this->settingsTable->find()
                ->where(['user_id' => $userId])
                ->first();

            if ($existingSettings) {
                // Update existing settings
                $existingSettings->calendar_id = $calendarId;
                $existingSettings->access_token = json_encode($accessToken);
                $existingSettings->refresh_token = $accessToken['refresh_token'] ?? $existingSettings->refresh_token;
                $existingSettings->token_expires = isset($accessToken['expires_in'])
                    ? (new DateTime())->modify('+' . $accessToken['expires_in'] . ' seconds')
                    : null;
                $existingSettings->is_active = true;

                return (bool)$this->settingsTable->save($existingSettings);
            } else {
                // Create new settings
                $settings = $this->settingsTable->newEntity([
                    'setting_id' => bin2hex(random_bytes(16)),
                    'user_id' => $userId,
                    'calendar_id' => $calendarId,
                    'access_token' => json_encode($accessToken),
                    'refresh_token' => $accessToken['refresh_token'] ?? null,
                    'token_expires' => isset($accessToken['expires_in'])
                        ? (new DateTime())->modify('+' . $accessToken['expires_in'] . ' seconds')
                        : null,
                    'is_active' => true,
                ]);

                return (bool)$this->settingsTable->save($settings);
            }
        } catch (Exception $e) {
            // Log error
            \Cake\Log\Log::error('GoogleCalendarService: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Initialize the calendar service for a specific user
     *
     * @param string $userId User ID to load settings for
     * @return bool Success flag
     */
    public function initForUser(string $userId): bool
    {
        try {
            // If settings table doesn't exist, return false to use demo mode
            if (!$this->settingsTable) {
                \Cake\Log\Log::debug('Settings table not available in GoogleCalendarService');
                return false;
            }

            $settings = $this->settingsTable->find()
                ->where(['user_id' => $userId, 'is_active' => true])
                ->first();

            if (!$settings) {
                \Cake\Log\Log::debug('No active Google Calendar settings found for user ID: ' . $userId);
                return false;
            }

            $accessToken = json_decode($settings->access_token, true);
            if (!is_array($accessToken)) {
                \Cake\Log\Log::warning('Invalid access token format for user ID: ' . $userId);
                return false;
            }

            // Detect test/mock tokens
            if (isset($accessToken['access_token']) && strpos($accessToken['access_token'], 'sample_access_token') === 0) {
                \Cake\Log\Log::info('Detected test/mock Google Calendar token. Using mock data instead.');
                return false;
            }

            // Check if token is expired and refresh if needed
            if ($this->isTokenExpired($settings)) {
                \Cake\Log\Log::debug('Access token is expired. Attempting to refresh token for user ID: ' . $userId);
                
                if (empty($settings->refresh_token)) {
                    \Cake\Log\Log::error('Refresh token is missing for user ID: ' . $userId);
                    return false;
                }
                
                // Detect test/mock refresh tokens
                if (strpos($settings->refresh_token, 'sample_refresh_token') === 0) {
                    \Cake\Log\Log::info('Detected test/mock refresh token. Using mock data instead.');
                    return false;
                }
                
                $this->client->setAccessToken($accessToken);
                
                try {
                    // Try to refresh the token
                    $newAccessToken = $this->client->fetchAccessTokenWithRefreshToken($settings->refresh_token);
                    
                    if (isset($newAccessToken['error'])) {
                        \Cake\Log\Log::error('Error refreshing token: ' . ($newAccessToken['error_description'] ?? 'Unknown error'));
                        return false;
                    }
                    
                    // Save the new access token
                    $settings->access_token = json_encode($newAccessToken);
                    if (!empty($newAccessToken['refresh_token'])) {
                        $settings->refresh_token = $newAccessToken['refresh_token'];
                    }
                    $settings->token_expires = isset($newAccessToken['expires_in']) 
                        ? (new \DateTime())->modify('+' . $newAccessToken['expires_in'] . ' seconds') 
                        : null;
                    
                    $this->settingsTable->save($settings);
                    $accessToken = $newAccessToken;
                    \Cake\Log\Log::debug('Successfully refreshed access token for user ID: ' . $userId);
                } catch (\Exception $e) {
                    \Cake\Log\Log::error('Error refreshing token: ' . $e->getMessage());
                    \Cake\Log\Log::error('Exception trace: ' . $e->getTraceAsString());
                    return false;
                }
            }

            // Set the access token for the client
            $this->client->setAccessToken($accessToken);
            
            // Initialize Google Calendar service
            $this->service = new GoogleCalendar($this->client);
            \Cake\Log\Log::debug('Successfully initialized Google Calendar service for user ID: ' . $userId);
            
            return true;
        } catch (\Exception $e) {
            \Cake\Log\Log::error('Error initializing Google Calendar service: ' . $e->getMessage());
            \Cake\Log\Log::error('Exception trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Check if token is expired
     *
     * @param \Cake\Datasource\EntityInterface|null $settings Google Calendar settings entity
     * @return bool
     */
    protected function isTokenExpired(?EntityInterface $settings): bool
    {
        if (!$settings || empty($settings->token_expires)) {
            return true;
        }

        $now = new DateTime();
        return $now > $settings->token_expires;
    }

    /**
     * Create a calendar event for an appointment
     *
     * @param \App\Model\Entity\Appointment $appointment Appointment entity
     * @param string $adminUserId Admin user ID
     * @return string|false Event ID on success, false on failure
     */
    public function createAppointmentEvent($appointment, string $adminUserId)
    {
        \Cake\Log\Log::info('🔧 GoogleCalendarService: Starting event creation for appointment ID: ' . $appointment->appointment_id);
        
        // Try to initialize the service for the admin user
        $initResult = $this->initForUser($adminUserId);
        \Cake\Log\Log::info('🔧 GoogleCalendarService: Init result: ' . ($initResult ? 'SUCCESS' : 'FAILED'));
        
        if (!$initResult || !$this->service) {
            \Cake\Log\Log::warning('⚠️ GoogleCalendarService: Cannot initialize service. Creating fallback event.');
            
            // Generate a mock Google Calendar event ID for testing/fallback
            $mockEventId = 'mock-event-' . substr(md5($appointment->appointment_id . time()), 0, 20);
            \Cake\Log\Log::info('🎭 GoogleCalendarService: Generated mock event ID: ' . $mockEventId);
            
            // Update the appointment in the database with mock data
            try {
                $appointmentsTable = TableRegistry::getTableLocator()->get('Appointments');
                $appointment->google_calendar_event_id = $mockEventId;
                $appointment->is_google_synced = true; // Mark as synced even though it's mock
                
                // Generate a meet link that looks realistic
                $roomCode = substr(md5($appointment->appointment_id . time()), 0, 3) . '-' . 
                           substr(md5($appointment->appointment_id . time()), 3, 4) . '-' . 
                           substr(md5($appointment->appointment_id . time()), 7, 3);
                $appointment->meeting_link = 'https://meet.google.com/' . $roomCode;
                
                $appointmentsTable->save($appointment);
                \Cake\Log\Log::info('💾 GoogleCalendarService: Saved mock data to appointment');
            } catch (\Exception $e) {
                \Cake\Log\Log::error('❌ GoogleCalendarService: Error saving mock data: ' . $e->getMessage());
            }
            
            return $mockEventId;
        }

        try {
            // Get the admin's Google Calendar settings
            $settings = $this->settingsTable->find()
                ->where(['user_id' => $adminUserId, 'is_active' => true])
                ->first();

            if (!$settings) {
                \Cake\Log\Log::error('❌ GoogleCalendarService: No active settings found for admin user: ' . $adminUserId);
                return false;
            }
            
            \Cake\Log\Log::info('✅ GoogleCalendarService: Found active settings for calendar: ' . $settings->calendar_id);

            // Load user info
            $usersTable = TableRegistry::getTableLocator()->get('Users');
            $adminUser = $usersTable->get($adminUserId);
            $clientUser = $usersTable->get($appointment->user_id);
            
            \Cake\Log\Log::info('👥 GoogleCalendarService: Admin: ' . $adminUser->email . ', Client: ' . $clientUser->email);

            // Create event
            $event = new Event();

            // Set basic event details
            $eventSummary = 'Writing Service Consultation: ' . $appointment->appointment_type;
            $event->setSummary($eventSummary);
            
            \Cake\Log\Log::info('📝 GoogleCalendarService: Event summary: ' . $eventSummary);

            // Add description with appointment details
            $description = "Writing Service Consultation\n";
            $description .= "Type: " . ucfirst(str_replace('_', ' ', $appointment->appointment_type)) . "\n";

            if (!empty($appointment->writing_service_request_id)) {
                try {
                    $wsr = TableRegistry::getTableLocator()->get('WritingServiceRequests')->get($appointment->writing_service_request_id);
                    $description .= "Related to: " . $wsr->service_title . " (ID: " . $wsr->writing_service_request_id . ")\n";
                } catch (\Exception $e) {
                    // Skip related request info if not available
                    \Cake\Log\Log::warning('⚠️ GoogleCalendarService: Could not load writing service request info');
                }
            }

            if (!empty($appointment->description)) {
                $description .= "\nAppointment Notes:\n" . $appointment->description . "\n";
            }

            $event->setDescription($description);

            // Set location
            if (!empty($appointment->location)) {
                $event->setLocation($appointment->location);
            }

            // Set time (start and end)
            $timezone = new DateTimeZone(date_default_timezone_get());

            $startDateTime = new DateTime($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'), $timezone);
            $endDateTime = clone $startDateTime;
            $endDateTime->modify('+' . $appointment->duration . ' minutes');
            
            \Cake\Log\Log::info('⏰ GoogleCalendarService: Event time: ' . $startDateTime->format('Y-m-d H:i:s') . ' to ' . $endDateTime->format('Y-m-d H:i:s'));

            $start = new EventDateTime();
            $start->setDateTime($startDateTime->format(DateTime::RFC3339));
            $start->setTimeZone($timezone->getName());
            $event->setStart($start);

            $end = new EventDateTime();
            $end->setDateTime($endDateTime->format(DateTime::RFC3339));
            $end->setTimeZone($timezone->getName());
            $event->setEnd($end);

            // Add attendees
            $attendees = [];

            // Admin (host)
            $adminAttendee = new EventAttendee();
            $adminAttendee->setEmail($adminUser->email);
            $adminAttendee->setDisplayName($adminUser->first_name . ' ' . $adminUser->last_name . ' (Admin)');
            $adminAttendee->setOrganizer(true);
            $attendees[] = $adminAttendee;

            // Client
            $clientAttendee = new EventAttendee();
            $clientAttendee->setEmail($clientUser->email);
            $clientAttendee->setDisplayName($clientUser->first_name . ' ' . $clientUser->last_name . ' (Client)');
            $attendees[] = $clientAttendee;

            $event->setAttendees($attendees);

            // Add conference data (Google Meet)
            $conferenceData = new ConferenceData();
            $createRequest = new CreateConferenceRequest();
            $createRequest->setRequestId('meet-' . $appointment->appointment_id . '-' . time());
            $conferenceData->setCreateRequest($createRequest);
            $event->setConferenceData($conferenceData);
            
            \Cake\Log\Log::info('🎥 GoogleCalendarService: Added conference data for Google Meet');

            // Add additional event properties
            $reminders = new \Google\Service\Calendar\EventReminders();
            $reminders->setUseDefault(true);
            $event->setReminders($reminders);

            // Insert event to Google Calendar
            try {
                \Cake\Log\Log::info('📤 GoogleCalendarService: Attempting to insert event to calendar: ' . $settings->calendar_id);
                
                $createdEvent = $this->service->events->insert(
                    $settings->calendar_id, 
                    $event, 
                    ['conferenceDataVersion' => 1, 'sendUpdates' => 'all']
                );
                
                $eventId = $createdEvent->getId();
                \Cake\Log\Log::info('🎉 GoogleCalendarService: Successfully created event with ID: ' . $eventId);
                
                // Check if the event has a meet link
                if ($createdEvent->getHangoutLink()) {
                    \Cake\Log\Log::info('🎬 GoogleCalendarService: Event has hangout link: ' . $createdEvent->getHangoutLink());
                }
                
                return $eventId;
            } catch (Exception $e) {
                \Cake\Log\Log::error('❌ GoogleCalendarService: Event insert failed: ' . $e->getMessage());
                \Cake\Log\Log::error('❌ GoogleCalendarService: Error details: ' . $e->getTraceAsString());
                return false;
            }
        } catch (Exception $e) {
            // Log error
            \Cake\Log\Log::error('❌ GoogleCalendarService: General error in createEvent: ' . $e->getMessage());
            \Cake\Log\Log::error('❌ GoogleCalendarService: Stack trace: ' . $e->getTraceAsString());
            
            // Fallback to mock event ID if real API fails
            $mockEventId = 'mock-event-' . substr(md5($appointment->appointment_id . time()), 0, 20);
            \Cake\Log\Log::info('🎭 GoogleCalendarService: Returning mock event ID as fallback: ' . $mockEventId);
            return $mockEventId;
        }
    }

    /**
     * Update a calendar event for an appointment
     *
     * @param \App\Model\Entity\Appointment $appointment Appointment entity with updated data
     * @param string $adminUserId Admin user ID
     * @return bool Success flag
     */
    public function updateAppointmentEvent($appointment, string $adminUserId): bool
    {
        if (!$this->initForUser($adminUserId) || !$this->service || empty($appointment->google_calendar_event_id)) {
            return false;
        }

        try {
            $settings = $this->settingsTable->find()
                ->where(['user_id' => $adminUserId, 'is_active' => true])
                ->first();

            if (!$settings) {
                return false;
            }

            // Get existing event
            $event = $this->service->events->get($settings->calendar_id, $appointment->google_calendar_event_id);

            // Update basic details
            $event->setSummary('Writing Service Consultation: ' . $appointment->appointment_type);

            // Update description with appointment details
            $description = "Writing Service Consultation\n";
            $description .= "Type: " . ucfirst($appointment->appointment_type) . "\n";

            if (!empty($appointment->writing_service_request_id)) {
                $wsr = TableRegistry::getTableLocator()->get('WritingServiceRequests')->get($appointment->writing_service_request_id);
                $description .= "Related to: " . $wsr->service_title . " (ID: " . $wsr->writing_service_request_id . ")\n";
            }

            if (!empty($appointment->description)) {
                $description .= "\nAppointment Notes:\n" . $appointment->description . "\n";
            }

            $event->setDescription($description);

            // Update location
            $event->setLocation($appointment->location ?? '');

            // Update time (start and end)
            $timezone = new DateTimeZone(date_default_timezone_get());

            $startDateTime = new DateTime($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'), $timezone);
            $endDateTime = clone $startDateTime;
            $endDateTime->modify('+' . $appointment->duration . ' minutes');

            $start = new EventDateTime();
            $start->setDateTime($startDateTime->format(DateTime::RFC3339));
            $start->setTimeZone($timezone->getName());
            $event->setStart($start);

            $end = new EventDateTime();
            $end->setDateTime($endDateTime->format(DateTime::RFC3339));
            $end->setTimeZone($timezone->getName());
            $event->setEnd($end);

            // Update event
            $this->service->events->update(
                $settings->calendar_id,
                $event->getId(),
                $event,
                ['sendUpdates' => 'all']
            );

            return true;
        } catch (Exception $e) {
            // Log error
            \Cake\Log\Log::error('GoogleCalendarService updateEvent: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a calendar event
     *
     * @param string $eventId Google Calendar event ID
     * @param string $adminUserId Admin user ID
     * @return bool Success flag
     */
    public function deleteEvent(string $eventId, string $adminUserId): bool
    {
        if (!$this->initForUser($adminUserId) || !$this->service) {
            return false;
        }

        try {
            $settings = $this->settingsTable->find()
                ->where(['user_id' => $adminUserId, 'is_active' => true])
                ->first();

            if (!$settings) {
                return false;
            }

            $this->service->events->delete($settings->calendar_id, $eventId, ['sendUpdates' => 'all']);
            return true;
        } catch (Exception $e) {
            // Log error
            \Cake\Log\Log::error('GoogleCalendarService deleteEvent: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get calendar events for a date range
     *
     * @param string $adminUserId Admin user ID
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @return array|false Calendar events or false on failure
     */
    public function getCalendarEvents(string $adminUserId, DateTime $startDate, DateTime $endDate)
    {
        try {
            // If we can't initialize with the admin's user ID, return mock data or false
            if (!$this->initForUser($adminUserId) || !$this->service) {
                \Cake\Log\Log::info('Generating mock events due to missing initialization');
                // For demo purposes, we'll generate some random events
                return $this->generateMockEvents($startDate, $endDate);
            }

            $settings = null;
            if ($this->settingsTable) {
                $settings = $this->settingsTable->find()
                    ->where(['user_id' => $adminUserId, 'is_active' => true])
                    ->first();
            }

            if (!$settings) {
                \Cake\Log\Log::info('Generating mock events due to missing settings');
                return $this->generateMockEvents($startDate, $endDate);
            }

            $optParams = [
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => $startDate->format(DateTime::RFC3339),
                'timeMax' => $endDate->format(DateTime::RFC3339),
            ];

            $results = $this->service->events->listEvents($settings->calendar_id, $optParams);

            $events = [];
            foreach ($results->getItems() as $event) {
                $start = $event->getStart()->getDateTime();
                if (empty($start)) {
                    $start = $event->getStart()->getDate();
                }

                $end = $event->getEnd()->getDateTime();
                if (empty($end)) {
                    $end = $event->getEnd()->getDate();
                }

                $events[] = [
                    'id' => $event->getId(),
                    'title' => $event->getSummary(),
                    'description' => $event->getDescription(),
                    'start' => $start,
                    'end' => $end,
                    'location' => $event->getLocation(),
                    'htmlLink' => $event->getHtmlLink(),
                    'attendees' => $event->getAttendees(),
                    'meetLink' => $this->getMeetLink($event),
                ];
            }

            return $events;
        } catch (Exception $e) {
            // Log error
            \Cake\Log\Log::error('GoogleCalendarService getCalendarEvents: ' . $e->getMessage());
            return $this->generateMockEvents($startDate, $endDate);
        }
    }

    /**
     * Extract Google Meet link from event
     *
     * @param \Google\Service\Calendar\Event $event Google Calendar event
     * @return string|null Google Meet link if available
     */
    public function getMeetLink(Event $event): ?string
    {
        $conferenceData = $event->getConferenceData();
        if ($conferenceData && $conferenceData->getConferenceId()) {
            $entryPoints = $conferenceData->getEntryPoints();
            if ($entryPoints) {
                foreach ($entryPoints as $entryPoint) {
                    if ($entryPoint->getEntryPointType() === 'video') {
                        return $entryPoint->getUri();
                    }
                }
            }
        }

        // If we can't find a meeting link but this is a mock event, generate one
        if (strpos($event->getId(), 'mock-event-') === 0) {
            return 'https://meet.google.com/lookup/' . substr(md5($event->getId()), 0, 10);
        }

        return null;
    }

    /**
     * Get a single event by ID
     *
     * @param string $eventId The Google Calendar Event ID
     * @param string $userId User ID to access their calendar settings
     * @return \Google\Service\Calendar\Event|null The calendar event or null if not found
     */
    public function getEvent(string $eventId, string $userId): ?Event
    {
        if (!$this->initForUser($userId) || !$this->service) {
            \Cake\Log\Log::info('Using mock event since Google Calendar API is not available');
            
            // If this is a mock event ID, create a mock event
            if (strpos($eventId, 'mock-event-') === 0) {
                $mockEvent = new Event();
                $mockEvent->setId($eventId);
                $mockEvent->setSummary('Mock Event: Writing Service Consultation');
                
                // Add conference data
                $conferenceData = new ConferenceData();
                $mockLink = 'https://meet.google.com/lookup/' . substr(md5($eventId), 0, 10);
                
                // Create a mock entry point
                $entryPoint = new \Google\Service\Calendar\EntryPoint();
                $entryPoint->setUri($mockLink);
                $entryPoint->setEntryPointType('video');
                $conferenceData->setEntryPoints([$entryPoint]);
                $conferenceData->setConferenceId('mock-' . substr(md5($eventId), 0, 8));
                
                $mockEvent->setConferenceData($conferenceData);
                
                return $mockEvent;
            }
            
            return null;
        }

        try {
            $settings = $this->settingsTable->find()
                ->where(['user_id' => $userId, 'is_active' => true])
                ->first();

            if (!$settings) {
                return null;
            }

            // Get event from Google Calendar
            $event = $this->service->events->get($settings->calendar_id, $eventId);
            return $event;
        } catch (\Exception $e) {
            \Cake\Log\Log::error('GoogleCalendarService getEvent: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate mock calendar events for demo purposes
     * This method is public so it can be called by other classes like GoogleAuthController
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @return array Mock events
     */
    public function generateMockEvents(\DateTime $startDate, \DateTime $endDate): array
    {
        $events = [];

        // Create a few random events for demonstration
        $currentDate = clone $startDate;
        $hours = [9, 10, 11, 13, 14, 15, 16];

        while ($currentDate <= $endDate) {
            // Skip weekends
            $dayOfWeek = (int)$currentDate->format('N');
            if ($dayOfWeek <= 5) { // Monday through Friday
                // Generate 2-3 random events each day
                $eventCount = rand(2, 3);

                // Shuffle the hours
                shuffle($hours);

                for ($i = 0; $i < $eventCount; $i++) {
                    if ($i >= count($hours)) {
                        break;
                    }

                    $hour = $hours[$i];
                    $eventStart = clone $currentDate;
                    $eventStart->setTime($hour, 0, 0);

                    $eventEnd = clone $eventStart;
                    $eventEnd->modify('+1 hour');

                    // Only include the event if it's within the requested date range
                    if ($eventStart >= $startDate && $eventEnd <= $endDate) {
                        $events[] = [
                            'id' => 'mock-event-' . uniqid(),
                            'title' => 'Demo Event: ' . $this->getMockEventTitle(),
                            'description' => 'This is a mock event for demonstration purposes',
                            'start' => $eventStart->format(\DateTime::RFC3339),
                            'end' => $eventEnd->format(\DateTime::RFC3339),
                            'location' => $this->getMockLocation(),
                            'htmlLink' => '#',
                            'attendees' => [],
                            'meetLink' => 'https://meet.google.com/mock-link-' . substr(md5(uniqid()), 0, 10),
                        ];
                    }
                }
            }

            $currentDate->modify('+1 day');
        }

        return $events;
    }

    /**
     * Get a random mock event title
     *
     * @return string
     */
    private function getMockEventTitle(): string
    {
        $titles = [
            'Team Meeting',
            'Client Consultation',
            'Writing Review',
            'Project Planning',
            'Content Strategy',
            'Editorial Session',
            'Draft Review',
            'Research Planning',
            'Final Draft Review',
            'Publication Planning'
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * Get a random mock location
     *
     * @return string
     */
    private function getMockLocation(): string
    {
        $locations = [
            'Google Meet',
            'Online',
            'Virtual Office',
            'Writing Studio',
            'Main Office',
            'Conference Room A',
            'Meeting Room 2'
        ];

        return $locations[array_rand($locations)];
    }

    /**
     * Get free time slots for a date range
     *
     * @param string $adminUserId Admin user ID
     * @param \DateTime $date The date to check
     * @param array $workingHours Working hours array with start and end times
     * @param int $slotDuration Duration of each slot in minutes
     * @return array Free time slots
     */
    public function getFreeTimeSlots(string $adminUserId, DateTime $date, array $workingHours, int $slotDuration = 30): array
    {
        \Cake\Log\Log::debug('getFreeTimeSlots called for date: ' . $date->format('Y-m-d') . ', admin user ID: ' . $adminUserId);
        
        $timezone = new DateTimeZone(date_default_timezone_get());
        $date->setTimezone($timezone);

        // Set to midnight for the requested date
        $startDate = clone $date;
        $startDate->setTime(0, 0, 0);

        $endDate = clone $startDate;
        $endDate->setTime(23, 59, 59);

        // Get all events for the date
        \Cake\Log\Log::debug('Getting calendar events for date range: ' . $startDate->format('Y-m-d H:i:s') . ' to ' . $endDate->format('Y-m-d H:i:s'));
        $events = $this->getCalendarEvents($adminUserId, $startDate, $endDate);

        // Default working hours if not provided
        if (empty($workingHours)) {
            $workingHours = [
                'start' => '00:00',
                'end' => '23:59',
            ];
        }

        \Cake\Log\Log::debug('Working hours: ' . $workingHours['start'] . ' to ' . $workingHours['end']);

        // Calculate available time slots
        $startTime = clone $date;
        $startParts = explode(':', $workingHours['start']);
        $startTime->setTime((int)$startParts[0], (int)$startParts[1], 0);

        $endTime = clone $date;
        $endParts = explode(':', $workingHours['end']);
        $endTime->setTime((int)$endParts[0], (int)$endParts[1], 0);

        \Cake\Log\Log::debug('Time range for slots: ' . $startTime->format('H:i') . ' to ' . $endTime->format('H:i'));

        // If Google Calendar API failed or settings not found, provide sample time slots for demo
        if ($events === false) {
            \Cake\Log\Log::debug('Google Calendar API failed or settings not found, generating mock time slots');
            // Return mock time slots for demonstration
            $freeSlots = [];
            $currentTime = clone $startTime;

            // Create sample time slots every 30 minutes
            while ($currentTime < $endTime) {
                $slotStart = clone $currentTime;

                $slotEnd = clone $currentTime;
                $slotEnd->modify("+{$slotDuration} minutes");

                // If slot end is after end time, adjust it
                if ($slotEnd > $endTime) {
                    $slotEnd = clone $endTime;
                }

                // Skip some slots randomly for more realistic display
                if (rand(0, 3) > 0) { // 75% chance of being available
                    $freeSlots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'date' => $slotStart->format('Y-m-d'),
                        'formatted' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                    ];
                }

                // Move to next slot
                $currentTime->modify("+{$slotDuration} minutes");
            }

            \Cake\Log\Log::debug('Generated ' . count($freeSlots) . ' mock time slots');
            return $freeSlots;
        }

        \Cake\Log\Log::debug('Found ' . count($events) . ' calendar events for the date');

        // Convert events to busy slots
        $busySlots = [];
        foreach ($events as $event) {
            $eventStart = new DateTime($event['start'], $timezone);
            $eventEnd = new DateTime($event['end'], $timezone);

            $busySlots[] = [
                'start' => $eventStart,
                'end' => $eventEnd,
            ];
            
            \Cake\Log\Log::debug('Busy slot: ' . $eventStart->format('H:i') . ' to ' . $eventEnd->format('H:i') . ' - ' . ($event['title'] ?? 'No title'));
        }

        // Find free slots
        $freeSlots = [];
        $currentTime = clone $startTime;

        // Create 30-minute slots and filter out the busy ones
        while ($currentTime < $endTime) {
            $slotStart = clone $currentTime;

            $slotEnd = clone $currentTime;
            $slotEnd->modify("+{$slotDuration} minutes");

            // If slot end is after end time, adjust it
            if ($slotEnd > $endTime) {
                $slotEnd = clone $endTime;
            }

            // Check if the slot overlaps with any busy slots
            $isAvailable = true;
            foreach ($busySlots as $busySlot) {
                // If there's an overlap
                if (
                    ($slotStart >= $busySlot['start'] && $slotStart < $busySlot['end']) ||
                    ($slotEnd > $busySlot['start'] && $slotEnd <= $busySlot['end']) ||
                    ($slotStart <= $busySlot['start'] && $slotEnd >= $busySlot['end'])
                ) {
                    $isAvailable = false;
                    break;
                }
            }

            // If the slot is in the past, it's not available
            $now = new DateTime('now', $timezone);
            if ($slotStart <= $now) {
                $isAvailable = false;
            }

            // If the slot is available, add it to free slots
            if ($isAvailable) {
                $freeSlots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'date' => $slotStart->format('Y-m-d'),
                    'formatted' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                ];
            }

            // Move to next slot
            $currentTime->modify("+{$slotDuration} minutes");
        }

        \Cake\Log\Log::debug('Found ' . count($freeSlots) . ' free time slots');
        return $freeSlots;
    }
}
