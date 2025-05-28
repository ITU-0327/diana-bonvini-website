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
 * BulkSyncAppointments command.
 */
class BulkSyncAppointmentsCommand extends Command
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
            ->setDescription('Bulk sync all unsynced confirmed appointments with Google Calendar')
            ->addArgument('admin_id', [
                'help' => 'Admin user ID to use for Google Calendar',
                'required' => true,
            ])
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
        $adminId = $args->getArgument('admin_id');
        $force = $args->getOption('force');
        
        if (empty($adminId)) {
            $io->error('Admin ID is required. Use "bin/cake list_users --type=admin" to find an admin ID.');
            return 1;
        }
        
        $io->out('Bulk syncing appointments with Google Calendar...');
        $io->hr();
        
        // Load models
        $appointments = TableRegistry::getTableLocator()->get('Appointments');
        $users = TableRegistry::getTableLocator()->get('Users');
        
        // Get admin user info
        try {
            $adminUser = $users->get($adminId);
            
            if ($adminUser->user_type !== 'admin') {
                $io->error('The specified user is not an admin: ' . $adminUser->email);
                return 1;
            }
            
            $io->out('Using admin: ' . $adminUser->email . ' (ID: ' . $adminId . ')');
        } catch (\Exception $e) {
            $io->error('Error loading admin user: ' . $e->getMessage());
            return 1;
        }
        
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
                }
            } catch (\Exception $e) {
                $io->error('Error syncing appointment: ' . $e->getMessage());
            }
            
            $io->hr();
        }
        
        $io->success('Finished processing ' . $appointmentsToSync->count() . ' appointments. Successfully synced: ' . $successCount);
        return 0;
    }
} 