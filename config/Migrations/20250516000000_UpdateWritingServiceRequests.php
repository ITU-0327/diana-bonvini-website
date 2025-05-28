<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class UpdateWritingServiceRequests extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('writing_service_requests');
        
        // Update final_price to be explicitly nullable and add a comment
        // explaining that it's deprecated in favor of calculating from payments
        $table->changeColumn('final_price', 'decimal', [
            'null' => true,
            'default' => null,
            'precision' => 10,
            'scale' => 2,
            'comment' => 'DEPRECATED: Use writing_service_payments to calculate total instead',
        ]);
        
        $table->update();
    }
} 