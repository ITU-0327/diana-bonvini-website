<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Service\GoogleCalendarService;
use Cake\Event\EventInterface;
use Exception;
use App\Model\Table\GoogleCalendarSettingsTable;
use Cake\Database\Connection;
use Cake\Database\StatementInterface;

/**
 * GoogleAuth Controller
 *
 * Handles Google OAuth authorization for calendar integration
 *
 * @property \App\Model\Table\GoogleCalendarSettingsTable $GoogleCalendarSettings
 */
class GoogleAuthController extends AppController
{
    /**
     * @var \App\Service\GoogleCalendarService
     */
    protected GoogleCalendarService $googleCalendarService;

    /**
     * @var \App\Model\Table\GoogleCalendarSettingsTable
     */
    protected GoogleCalendarSettingsTable $GoogleCalendarSettings;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Try to load the GoogleCalendarSettings table, but handle the case if it doesn't exist
        try {
            $this->GoogleCalendarSettings = $this->getTableLocator()->get('GoogleCalendarSettings');
        } catch (\Exception $e) {
            // Create the table if it doesn't exist
            $this->createGoogleCalendarSettingsTable();

            // Try to load it again
            try {
                $this->GoogleCalendarSettings = $this->getTableLocator()->get('GoogleCalendarSettings');
            } catch (\Exception $e) {
                // If we still can't load it, log the error and continue
                \Cake\Log\Log::error('Failed to load GoogleCalendarSettings table: ' . $e->getMessage());
            }
        }

        $this->googleCalendarService = new GoogleCalendarService();

        // Use admin layout
        $this->viewBuilder()->setLayout('admin');
    }

    /**
     * Create the GoogleCalendarSettings table if it doesn't exist
     *
     * @return void
     */
    protected function createGoogleCalendarSettingsTable(): void
    {
        try {
            /** @var Connection $connection */
            $connection = \Cake\Datasource\ConnectionManager::get('default');
            // Check if the table already exists
            /** @var StatementInterface $stmt */
            $stmt = $connection->execute("SHOW TABLES LIKE 'google_calendar_settings'");
            $tableExists = $stmt->rowCount() > 0;

            if (!$tableExists) {
                // Create the table using the SQL from the migration
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
                ) ENGINE=InnoDB;";

                $connection->execute($sql);

                // Register the table with the TableRegistry
                \Cake\ORM\TableRegistry::getTableLocator()->clear();
            }
        } catch (\Exception $e) {
            \Cake\Log\Log::error('Failed to create GoogleCalendarSettings table: ' . $e->getMessage());
        }
    }

    /**
     * BeforeFilter callback.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Ensure user is authenticated and is an admin
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error(__('You must be logged in as an administrator to access this area.'));

            $event->setResult($this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]));
        }
    }

    /**
     * Index page - shows auth status and connection options
     *
     * @return void
     */
    public function index()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        // Get current Google Calendar settings for this user
        $settings = $this->GoogleCalendarSettings->find()
            ->where(['user_id' => $user->user_id, 'is_active' => true])
            ->first();

        $isConnected = !empty($settings);
        $authUrl = $this->googleCalendarService->getAuthUrl();

        $this->set(compact('isConnected', 'authUrl', 'settings'));
    }

    /**
     * Handle OAuth callback from Google
     *
     * @return \Cake\Http\Response|null
     */
    public function callback()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $code = $this->request->getQuery('code');
        $error = $this->request->getQuery('error');

        if (!empty($error)) {
            $this->Flash->error(__('Google authorization failed: {0}', $error));

            return $this->redirect(['action' => 'index']);
        }

        if (empty($code)) {
            $this->Flash->error(__('Invalid authorization code.'));

            return $this->redirect(['action' => 'index']);
        }

        try {
            $success = $this->googleCalendarService->handleAuthCallback($code, $user->user_id);

            if ($success) {
                $this->Flash->success(__('Google Calendar successfully connected.'));
            } else {
                $this->Flash->error(__('Failed to connect Google Calendar.'));
            }
        } catch (Exception $e) {
            $this->Flash->error(__('Error connecting to Google Calendar: {0}', $e->getMessage()));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Disconnect Google Calendar
     *
     * @return \Cake\Http\Response|null
     */
    public function disconnect()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $settings = $this->GoogleCalendarSettings->find()
            ->where(['user_id' => $user->user_id, 'is_active' => true])
            ->first();

        if ($settings) {
            $settings->is_active = false;
            if ($this->GoogleCalendarSettings->save($settings)) {
                $this->Flash->success(__('Google Calendar disconnected successfully.'));
            } else {
                $this->Flash->error(__('Failed to disconnect Google Calendar.'));
            }
        } else {
            $this->Flash->error(__('No active Google Calendar connection found.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    /**
     * View Google Calendar
     *
     * @return \Cake\Http\Response|null
     */
    public function viewCalendar()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        // Try to get current Google Calendar settings for this user
        try {
            if (isset($this->GoogleCalendarSettings)) {
                $settings = $this->GoogleCalendarSettings->find()
                    ->where(['user_id' => $user->user_id, 'is_active' => true])
                    ->first();
            } else {
                $settings = null;
            }
        } catch (\Exception $e) {
            $settings = null;
        }

        $isConnected = !empty($settings);
        $useDemoMode = !$isConnected;

        // Get current date for calendar view
        $month = (int)$this->request->getQuery('month', date('n'));
        $year = (int)$this->request->getQuery('year', date('Y'));

        // Ensure valid month/year values
        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }
        if ($year < date('Y') || $year > date('Y') + 2) {
            $year = (int)date('Y');
        }

        // Get current date and time
        $today = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
        $startDate = new \DateTime("$year-$month-01", new \DateTimeZone(date_default_timezone_get()));
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        // Get calendar events from Google or use mock data
        if ($isConnected) {
            try {
                $events = $this->googleCalendarService->getCalendarEvents($user->user_id, $startDate, $endDate);

                // If no events were returned (could be false or empty array), use mock data
                if ($events === false || empty($events)) {
                    $events = $this->getMockCalendarEvents($startDate, $endDate);
                    $this->Flash->info(__('No events found in your Google Calendar for this period. Showing sample events.'));
                }
            } catch (\Exception $e) {
                // Log the error and use mock data
                \Cake\Log\Log::error('Error getting calendar events: ' . $e->getMessage());
                $events = $this->getMockCalendarEvents($startDate, $endDate);
                $this->Flash->warning(__('Error retrieving events from Google Calendar. Showing sample events.'));
            }
        } else {
            // Use mock data for demo mode
            $events = $this->getMockCalendarEvents($startDate, $endDate);
            $this->Flash->info(__('Using demo mode for calendar. To connect your real Google Calendar, go to the <a href="/admin/google-auth">Google Calendar Integration</a> page.'), ['escape' => false]);
        }

        // Format events for calendar view
        $calendarEvents = $this->formatCalendarEvents($events);

        // Set variables for the view
        $this->set(compact('settings', 'isConnected', 'useDemoMode', 'month', 'year', 'today', 'calendarEvents'));
    }

    /**
     * Generate mock calendar events for demo purposes
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @return array Mock events
     */
    protected function getMockCalendarEvents(\DateTime $startDate, \DateTime $endDate): array
    {
        // Use the generateMockEvents method from GoogleCalendarService
        return $this->googleCalendarService->generateMockEvents($startDate, $endDate);
    }

    /**
     * Format calendar events for the calendar view
     *
     * @param array|false $events Calendar events
     * @return array Formatted events
     */
    protected function formatCalendarEvents($events): array
    {
        if (empty($events) || $events === false) {
            return [];
        }

        $formattedEvents = [];

        foreach ($events as $event) {
            $startDate = new \DateTime($event['start']);
            $endDate = new \DateTime($event['end']);

            $formattedEvents[] = [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $startDate->format('Y-m-d H:i:s'),
                'end' => $endDate->format('Y-m-d H:i:s'),
                'allDay' => false,
                'backgroundColor' => '#3788d8',
                'borderColor' => '#3788d8',
                'textColor' => '#ffffff',
                'url' => $event['htmlLink'] ?? null,
                'extendedProps' => [
                    'description' => $event['description'] ?? '',
                    'location' => $event['location'] ?? '',
                    'meetLink' => $event['meetLink'] ?? '',
                ]
            ];
        }

        return $formattedEvents;
    }
}
