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
 * SyncAppointment command.
 */
class SyncAppointmentCommand extends Command
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
            ->setDescription('Sync an appointment with Google Calendar')
            ->addArgument('appointment_id', [
                'help' => 'Appointment ID to sync',
                'required' => true,
            ])
            ->addArgument('admin_id', [
                'help' => 'Admin user ID to use for Google Calendar',
                'required' => true,
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
        $appointmentId = $args->getArgument('appointment_id');
        $adminId = $args->getArgument('admin_id');
        
        $io->out('Syncing appointment ID: ' . $appointmentId . ' with admin ID: ' . $adminId);
        $io->hr();
        
        // Load models
        $appointments = TableRegistry::getTableLocator()->get('Appointments');
        $users = TableRegistry::getTableLocator()->get('Users');
        $googleCalendarSettings = TableRegistry::getTableLocator()->get('GoogleCalendarSettings');
        
        // Get appointment
        try {
            $appointment = $appointments->get($appointmentId, [
                'contain' => ['Users'],
            ]);
            
            $io->out('Appointment details:');
            $io->out('Date: ' . $appointment->appointment_date->format('Y-m-d'));
            $io->out('Time: ' . $appointment->appointment_time->format('H:i:s'));
            $io->out('Status: ' . $appointment->status);
            $io->out('Customer: ' . $appointment->user->email);
            $io->hr();
        } catch (\Exception $e) {
            $io->error('Error loading appointment: ' . $e->getMessage());
            return 1;
        }
        
        // Get admin user
        try {
            $adminUser = $users->get($adminId);
            
            if ($adminUser->user_type !== 'admin') {
                $io->error('User is not an admin: ' . $adminUser->email);
                return 1;
            }
            
            $io->out('Admin user: ' . $adminUser->email);
        } catch (\Exception $e) {
            $io->error('Error loading admin user: ' . $e->getMessage());
            return 1;
        }
        
        // Check if admin has Google Calendar settings
        $settings = $googleCalendarSettings->find()
            ->where(['user_id' => $adminId, 'is_active' => true])
            ->first();
        
        if (!$settings) {
            $io->error('Admin does not have Google Calendar settings');
            return 1;
        }
        
        $io->out('Admin has Google Calendar settings. Calendar ID: ' . $settings->calendar_id);
        $io->hr();
        
        // Create or update event in Google Calendar
        if ($appointment->is_google_synced && !empty($appointment->google_calendar_event_id)) {
            $io->out('Appointment is already synced with Google Calendar. Event ID: ' . $appointment->google_calendar_event_id);
            $io->out('Updating existing event...');
            
            // Update existing event
            $result = $this->googleCalendarService->updateAppointmentEvent($appointment, $adminId);
            
            if ($result) {
                $io->success('Appointment updated in Google Calendar');
            } else {
                $io->error('Failed to update appointment in Google Calendar');
                return 1;
            }
        } else {
            $io->out('Creating new Google Calendar event...');
            
            // Create new event
            $eventId = $this->googleCalendarService->createAppointmentEvent($appointment, $adminId);
            
            if ($eventId) {
                $io->success('Created Google Calendar event with ID: ' . $eventId);
                
                // Get Google Meet link
                $io->out('Retrieving Google Meet link...');
                $event = $this->googleCalendarService->getEvent($eventId, $adminId);
                $meetLink = $this->googleCalendarService->getMeetLink($event);
                
                if ($meetLink) {
                    $io->out('Google Meet link: ' . $meetLink);
                } else {
                    $io->warning('No Google Meet link retrieved, creating fallback link');
                    $meetLink = "https://meet.google.com/lookup/" . substr(md5($appointment->appointment_id . time()), 0, 10);
                }
                
                // Update appointment with event ID and meet link
                $appointment->google_calendar_event_id = $eventId;
                $appointment->is_google_synced = true;
                $appointment->meeting_link = $meetLink;
                
                if ($appointments->save($appointment)) {
                    $io->success('Appointment updated with Google Calendar data');
                } else {
                    $io->error('Failed to update appointment with Google Calendar data: ' . json_encode($appointment->getErrors()));
                    return 1;
                }
            } else {
                $io->error('Failed to create Google Calendar event');
                return 1;
            }
        }
        
        return 0;
    }
} 