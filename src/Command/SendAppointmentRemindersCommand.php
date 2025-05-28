<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\FrozenDate;
use Cake\Mailer\MailerAwareTrait;

/**
 * SendAppointmentReminders command.
 *
 * Command to send email reminders for appointments scheduled for the next day.
 * Should be run once daily via cron job.
 */
class SendAppointmentRemindersCommand extends Command
{
    use MailerAwareTrait;
    
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Sends reminder emails for appointments scheduled for tomorrow.');
        
        $parser->addOption('dry-run', [
            'help' => 'Run in dry-run mode (does not send actual emails)',
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
        $io->out('Starting appointment reminder process...');
        
        // Get tomorrow's date
        $tomorrow = new FrozenDate('tomorrow');
        $io->info('Checking appointments for: ' . $tomorrow->format('Y-m-d'));
        
        // Load appointments table
        $appointments = $this->fetchTable('Appointments');
        
        // Find all appointments scheduled for tomorrow that are confirmed or pending
        $tomorrowAppointments = $appointments->find()
            ->contain(['Users', 'WritingServiceRequests'])
            ->where([
                'appointment_date' => $tomorrow->format('Y-m-d'),
                'status IN' => ['confirmed', 'pending'],
                'is_deleted' => false,
            ])
            ->order(['appointment_time' => 'ASC'])
            ->all();
        
        $count = $tomorrowAppointments->count();
        $io->info("Found {$count} appointments scheduled for tomorrow.");
        
        if ($count === 0) {
            $io->success('No appointments to send reminders for. Exiting.');
            return self::CODE_SUCCESS;
        }
        
        $dryRun = (bool)$args->getOption('dry-run');
        if ($dryRun) {
            $io->warning('Running in DRY-RUN mode. No emails will be sent.');
        }
        
        $io->hr();
        $emailsSent = 0;
        $emailsFailed = 0;
        
        foreach ($tomorrowAppointments as $appointment) {
            $io->out(sprintf(
                'Sending reminder for appointment at %s with %s',
                $appointment->appointment_time->format('g:i A'),
                $appointment->user->first_name . ' ' . $appointment->user->last_name
            ));
            
            if (!$dryRun) {
                try {
                    $this->getMailer('Appointment')->send('appointmentReminder', [$appointment]);
                    $emailsSent++;
                    $io->success('Reminder sent successfully.');
                } catch (\Exception $e) {
                    $emailsFailed++;
                    $io->error('Failed to send reminder: ' . $e->getMessage());
                    $this->log('Failed to send appointment reminder: ' . $e->getMessage(), 'error');
                }
            } else {
                $io->success('[DRY-RUN] Would have sent reminder.');
                $emailsSent++;
            }
        }
        
        $io->hr();
        $io->out("Process completed.");
        $io->success("Sent {$emailsSent} reminders successfully.");
        
        if ($emailsFailed > 0) {
            $io->error("Failed to send {$emailsFailed} reminders. Check logs for details.");
            return self::CODE_ERROR;
        }
        
        return self::CODE_SUCCESS;
    }
}