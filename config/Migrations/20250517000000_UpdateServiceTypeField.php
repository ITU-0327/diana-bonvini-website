<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class UpdateServiceTypeField extends BaseMigration
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
        $table = $this->table('writing_service_requests');
        
        // Change service_type from ENUM to VARCHAR(200)
        $table->changeColumn('service_type', 'string', [
            'limit' => 200,
            'null' => false,
        ]);
        
        $table->update();
    }
} 