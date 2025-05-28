<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCoachingServicePaymentsTable extends BaseMigration
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
        $table = $this->table('coaching_service_payments', [
            'id' => 'coaching_service_payment_id',
            'primary_key' => ['coaching_service_payment_id'],
        ]);

        $table
            ->addColumn('coaching_service_request_id', 'string', [
                'default' => null,
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('amount', 'decimal', [
                'default' => null,
                'precision' => 10,
                'scale' => 2,
                'null' => false,
            ])
            ->addColumn('transaction_id', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('payment_date', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('payment_method', 'string', [
                'default' => 'stripe',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'default' => 'pending',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('is_deleted', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
            ])
            ->addIndex(['coaching_service_request_id'], ['name' => 'idx_csp_request'])
            ->addIndex(['transaction_id'], ['name' => 'idx_csp_transaction'])
            ->addForeignKey(
                'coaching_service_request_id',
                'coaching_service_requests',
                'coaching_service_request_id',
                [
                    'update' => 'NO_ACTION',
                    'delete' => 'CASCADE'
                ]
            )
            ->create();
    }
} 