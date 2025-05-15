<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;

/**
 * CheckAppointments command.
 */
class CheckAppointmentsCommand extends Command
{
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
            ->setDescription('Check appointments and Google Calendar settings');

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
        $io->out('Checking appointments and Google Calendar settings...');
        $io->hr();

        // Check if Appointments table exists
        $conn = ConnectionManager::get('default');
        $tableExists = $conn->execute("SHOW TABLES LIKE 'appointments'")->fetchAll() ? true : false;

        if (!$tableExists) {
            $io->error('Appointments table does not exist!');
            return 1;
        }

        // Load models
        $appointments = TableRegistry::getTableLocator()->get('Appointments');
        $users = TableRegistry::getTableLocator()->get('Users');

        // Query for confirmed appointments
        $confirmedAppointments = $appointments->find()
            ->select(['Appointments.appointment_id', 'Appointments.appointment_date', 
                     'Appointments.appointment_time', 'Appointments.status', 
                     'Appointments.is_google_synced', 'Appointments.google_calendar_event_id',
                     'Users.email'])
            ->where([
                'Appointments.status' => 'confirmed',
                'Appointments.is_deleted' => false
            ])
            ->order(['Appointments.appointment_date' => 'DESC', 'Appointments.appointment_time' => 'DESC'])
            ->limit(5)
            ->contain(['Users'])
            ->all();

        $io->out('Found ' . $confirmedAppointments->count() . ' confirmed appointments:');
        $io->hr();

        foreach ($confirmedAppointments as $appointment) {
            $io->out('Appointment ID: ' . $appointment->appointment_id);
            $io->out('Date: ' . $appointment->appointment_date->format('Y-m-d') . ' at ' . $appointment->appointment_time->format('H:i:s'));
            $io->out('Status: ' . $appointment->status);
            $io->out('Synced to Google Calendar: ' . ($appointment->is_google_synced ? 'Yes' : 'No'));
            $io->out('Google Calendar Event ID: ' . ($appointment->google_calendar_event_id ?: 'Not synced'));
            $io->out('User Email: ' . $appointment->user->email);
            $io->hr();
        }

        // Check if Google Calendar Settings table exists
        $tableExists = $conn->execute("SHOW TABLES LIKE 'google_calendar_settings'")->fetchAll() ? true : false;

        if (!$tableExists) {
            $io->error('Google Calendar Settings table does not exist!');
            return 1;
        }

        // Check Google Calendar settings
        $io->hr();
        try {
            $settings = $conn->execute("
                SELECT gcs.setting_id, gcs.user_id, gcs.calendar_id, gcs.is_active, u.email
                FROM google_calendar_settings gcs
                INNER JOIN users u ON u.user_id = gcs.user_id COLLATE utf8mb4_unicode_ci
                LIMIT 5
            ")->fetchAll('assoc');
            
            if (empty($settings)) {
                $io->warning('No Google Calendar settings found');
            } else {
                $io->out('Found ' . count($settings) . ' Google Calendar settings:');
                
                foreach ($settings as $setting) {
                    $io->out('- User: ' . $setting['email']);
                    $io->out('  Calendar ID: ' . $setting['calendar_id']);
                    $io->out('  Active: ' . ($setting['is_active'] ? 'Yes' : 'No'));
                    $io->hr();
                }
            }
        } catch (\Exception $e) {
            $io->error('Error retrieving Google Calendar settings: ' . $e->getMessage());
            
            // Fallback to direct query on google_calendar_settings without join
            try {
                $io->out('Trying alternative approach to get Google Calendar settings...');
                $settings = $conn->execute("
                    SELECT setting_id, user_id, calendar_id, is_active 
                    FROM google_calendar_settings
                    LIMIT 5
                ")->fetchAll('assoc');
                
                if (empty($settings)) {
                    $io->warning('No Google Calendar settings found (fallback query)');
                } else {
                    $io->out('Found ' . count($settings) . ' Google Calendar settings:');
                    
                    foreach ($settings as $setting) {
                        $io->out('- User ID: ' . $setting['user_id']);
                        $io->out('  Calendar ID: ' . $setting['calendar_id']);
                        $io->out('  Active: ' . ($setting['is_active'] ? 'Yes' : 'No'));
                        $io->hr();
                    }
                }
            } catch (\Exception $e2) {
                $io->error('Error with fallback query: ' . $e2->getMessage());
            }
        }

        return null;
    }
} 