<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\GoogleCalendarService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;

/**
 * AutoSyncAppointments command.
 * 
 * This command can be scheduled to run via cron to automatically sync all unsynced
 * confirmed appointments with Google Calendar.
 * 
 * Example cron entry (hourly):
 * 0 * * * * cd /opt/homebrew/var/www/team123-app_fit3048 && bin/cake auto_sync_appointments > /dev/null 2>&1
 */
class AutoSyncAppointmentsCommand extends Command
{
    /**
     * @var \App\Service\GoogleCalendarService
     */
    protected GoogleCalendarService $googleCalendarService;
    
    /**
     * Constructor to initialize the Google Calendar service
     */
    public function __construct()
    {
        parent::__construct();
        $this->googleCalendarService = new GoogleCalendarService();
    }
    
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Automatically sync unsynced confirmed appointments with Google Calendar')
            ->addOption('force', [
                'help' => 'Force resync of all appointments, even those already synced',
                'boolean' => true,
                'default' => false,
            ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $force = $args->getOption('force');
        
        $io->out('Auto-syncing unsynced appointments with Google Calendar...');
        $io->hr();
        
        // Load models
        $appointments = TableRegistry::getTableLocator()->get('Appointments');
        $users = TableRegistry::getTableLocator()->get('Users');
        
        // Find an admin user with Google Calendar settings
        $adminUsers = $users->find()
            ->where(['user_type' => 'admin', 'is_verified' => true])
            ->order(['last_login' => 'DESC'])
            ->limit(5)
            ->all();
        
        if ($adminUsers->isEmpty()) {
            $io->error('No admin users found to use for syncing');
            return 1;
        }
        
        // Try each admin user until one works
        $adminId = null;
        foreach ($adminUsers as $admin) {
            // Test if this admin has Google Calendar settings
            $io->out('Testing admin user: ' . $admin->email);
            
            // Try to create test settings if needed
            $testCommand = "bin/cake create_test_calendar_settings {$admin->user_id}";
            $io->out('Executing: ' . $testCommand);
            
            exec($testCommand, $output, $returnCode);
            
            // If we can get a test appointment to sync, use this admin
            $testAppointment = $appointments->find()
                ->where(['status' => 'confirmed', 'Appointments.is_deleted' => false])
                ->order(['appointment_date' => 'ASC'])
                ->first();
            
            if (!$testAppointment) {
                $io->warning('No appointments found to test sync');
                continue;
            }
            
            $eventId = $this->googleCalendarService->createAppointmentEvent($testAppointment, $admin->user_id);
            
            if ($eventId) {
                $io->success('Successfully synced a test appointment with admin: ' . $admin->email);
                $adminId = $admin->user_id;
                break;
            }
        }
        
        if (!$adminId) {
            $io->error('Could not find a working admin user for Google Calendar sync');
            return 1;
        }
        
        $io->out('Using admin: ' . $users->get($adminId)->email . ' (ID: ' . $adminId . ')');
        
        // Build query to get appointments to sync
        $query = $appointments->find()
            ->where([
                'Appointments.status' => 'confirmed',
                'Appointments.is_deleted' => false,
            ]);
        
        // Only get unsynced appointments unless force is specified
        if (!$force) {
            $query->where([
                'OR' => [
                    'Appointments.is_google_synced' => false,
                    'Appointments.is_google_synced IS NULL',
                    'Appointments.google_calendar_event_id IS NULL',
                    'Appointments.meeting_link IS NULL',
                ]
            ]);
        }
        
        // Order by date
        $query->order(['appointment_date' => 'ASC', 'appointment_time' => 'ASC']);
        
        // Get the appointments
        $appointmentsToSync = $query->contain(['Users'])->all();
        
        if ($appointmentsToSync->isEmpty()) {
            $io->out('No appointments found that need to be synced');
            return 0;
        }
        
        $io->out('Found ' . $appointmentsToSync->count() . ' appointments to sync');
        $io->hr();
        
        // Process each appointment
        $counter = 0;
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($appointmentsToSync as $appointment) {
            $counter++;
            $io->out("[{$counter}/{$appointmentsToSync->count()}] Processing appointment {$appointment->appointment_id}");
            $io->out('Date: ' . $appointment->appointment_date->format('Y-m-d') . ' at ' . $appointment->appointment_time->format('H:i:s'));
            $io->out('Customer: ' . ($appointment->user ? $appointment->user->email : 'Unknown'));
            
            if ($appointment->is_google_synced && !empty($appointment->google_calendar_event_id) && !$force) {
                $io->out('Appointment is already synced with Google Calendar. Skipping.');
                $io->hr();
                continue;
            }
            
            try {
                $io->out('Creating Google Calendar event...');
                
                // Create the event
                $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $adminId);
                
                if (!$eventId) {
                    $io->error('Failed to create Google Calendar event');
                    $io->hr();
                    $errorCount++;
                    continue;
                }
                
                $io->success('Created Google Calendar event: ' . $eventId);
                
                // Get the Google Meet link if not already set
                if (empty($appointment->meeting_link)) {
                    $io->out('Getting Google Meet link...');
                    
                    $event = $this->googleCalendarService->getEvent($eventId, $adminId);
                    $meetLink = $this->googleCalendarService->getMeetLink($event);
                    
                    if ($meetLink) {
                        $io->out('Google Meet link: ' . $meetLink);
                        $appointment->meeting_link = $meetLink;
                    } else {
                        $io->warning('No Google Meet link found, creating fallback');
                        $fallbackMeetLink = "https://meet.google.com/lookup/" . substr(md5($appointment->appointment_id . time()), 0, 10);
                        $appointment->meeting_link = $fallbackMeetLink;
                        $io->out('Fallback Meet link: ' . $fallbackMeetLink);
                    }
                } else {
                    $io->out('Appointment already has a meeting link: ' . $appointment->meeting_link);
                }
                
                // Update the appointment
                $appointment->google_calendar_event_id = $eventId;
                $appointment->is_google_synced = true;
                
                if ($appointments->save($appointment)) {
                    $io->success('Appointment updated successfully');
                    $successCount++;
                } else {
                    $io->error('Failed to save appointment: ' . json_encode($appointment->getErrors()));
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $io->error('Error syncing appointment: ' . $e->getMessage());
                $errorCount++;
            }
            
            $io->hr();
        }
        
        $io->success('Finished processing ' . $appointmentsToSync->count() . ' appointments. Success: ' . $successCount . ', Errors: ' . $errorCount);
        return 0;
    }
} 