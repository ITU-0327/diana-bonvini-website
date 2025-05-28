<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;

/**
 * FixCalendarCollation command.
 */
class FixCalendarCollationCommand extends Command
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
        $parser->setDescription('Fix collation issues with Google Calendar Settings and Users tables');
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
        $io->out('Fixing collation issues with Google Calendar Settings and Users tables...');
        $io->hr();
        
        // Get the database connection
        $connection = ConnectionManager::get('default');
        
        // Create a new google_calendar_settings table with the correct collation
        $this->createGoogleCalendarSettingsTable($connection, $io);
        
        // List all admin users
        $io->hr();
        $io->out('Listing all verified admin users:');
        
        try {
            $admins = $connection->execute("
                SELECT user_id, email, first_name, last_name 
                FROM users
                WHERE user_type = 'admin' AND is_verified = 1
                ORDER BY last_login DESC
                LIMIT 5
            ")->fetchAll('assoc');
            
            if (empty($admins)) {
                $io->out('No verified admin users found');
            } else {
                foreach ($admins as $admin) {
                    $io->out('- ' . $admin['first_name'] . ' ' . $admin['last_name'] . ' (' . $admin['email'] . ')');
                    $io->out('  User ID: ' . $admin['user_id']);
                }
            }
        } catch (\Exception $e) {
            $io->error('Failed to list admin users: ' . $e->getMessage());
        }
        
        return 0;
    }
    
    /**
     * Create a fresh google_calendar_settings table with the correct collation
     *
     * @param \Cake\Datasource\ConnectionManager $connection Database connection
     * @param \Cake\Console\ConsoleIo $io Console IO for output
     * @return bool Success flag
     */
    protected function createGoogleCalendarSettingsTable($connection, $io)
    {
        $io->out('Checking Google Calendar Settings table...');
        
        // Check if the table exists
        $tableExists = $connection->execute("SHOW TABLES LIKE 'google_calendar_settings'")->fetchAll();
        
        if (!empty($tableExists)) {
            $io->out('Dropping existing Google Calendar Settings table...');
            
            try {
                // First disable foreign key checks
                $connection->execute("SET FOREIGN_KEY_CHECKS = 0");
                
                // Drop the table
                $connection->execute("DROP TABLE IF EXISTS google_calendar_settings");
                
                // Re-enable foreign key checks
                $connection->execute("SET FOREIGN_KEY_CHECKS = 1");
                
                $io->success('Dropped existing Google Calendar Settings table');
            } catch (\Exception $e) {
                $io->error('Failed to drop Google Calendar Settings table: ' . $e->getMessage());
                return false;
            }
        }
        
        $io->out('Creating Google Calendar Settings table with correct collation...');
        
        try {
            // Disable foreign key checks
            $connection->execute("SET FOREIGN_KEY_CHECKS = 0");
            
            // Create the table with the correct collation
            $connection->execute("
                CREATE TABLE google_calendar_settings (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            ");
            
            // Re-enable foreign key checks
            $connection->execute("SET FOREIGN_KEY_CHECKS = 1");
            
            $io->success('Google Calendar Settings table created successfully with matching collation');
            return true;
        } catch (\Exception $e) {
            $io->error('Failed to create Google Calendar Settings table: ' . $e->getMessage());
            
            // Make sure foreign key checks are re-enabled
            try {
                $connection->execute("SET FOREIGN_KEY_CHECKS = 1");
            } catch (\Exception $e2) {
                // Ignore exceptions from this command
            }
            
            return false;
        }
    }
}