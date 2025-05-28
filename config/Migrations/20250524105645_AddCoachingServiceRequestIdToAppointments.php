<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCoachingServiceRequestIdToAppointments extends BaseMigration
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
        $table->addColumn('coaching_service_request_id', 'uuid', [
            'default' => null,
            'null' => true,
        ]);
        $table->addIndex([
            'coaching_service_request_id',
        ], [
            'name' => 'idx_appointments_coaching_service_request',
            'unique' => false,
        ]);
        $table->update();
    }
}
