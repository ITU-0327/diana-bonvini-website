<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMissingFieldsToAppointments extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('appointments');
        
        // Check if columns exist before adding them
        if (!$table->hasColumn('location')) {
            $table->addColumn('location', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ]);
        }
        
        if (!$table->hasColumn('description')) {
            $table->addColumn('description', 'text', [
                'default' => null,
                'null' => true,
            ]);
        }
        
        if (!$table->hasColumn('meeting_link')) {
            $table->addColumn('meeting_link', 'string', [
                'default' => null,
                'limit' => 500,
                'null' => true,
            ]);
        }
        
        if (!$table->hasColumn('is_google_synced')) {
            $table->addColumn('is_google_synced', 'boolean', [
                'default' => false,
                'null' => false,
            ]);
        }
        
        if (!$table->hasColumn('writing_service_request_id')) {
            $table->addColumn('writing_service_request_id', 'uuid', [
                'default' => null,
                'null' => true,
            ]);
        }
        
        // Add indexes if they don't exist
        if (!$table->hasIndex(['writing_service_request_id'])) {
            $table->addIndex([
                'writing_service_request_id',
            ], [
                'name' => 'idx_appointments_writing_service_request',
                'unique' => false,
            ]);
        }
        
        if (!$table->hasIndex(['is_google_synced'])) {
            $table->addIndex([
                'is_google_synced',
            ], [
                'name' => 'idx_appointments_google_synced',
                'unique' => false,
            ]);
        }
        
        $table->update();
    }
}
