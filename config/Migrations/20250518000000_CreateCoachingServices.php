<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateCoachingServices extends BaseMigration
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
        // Table: coaching_service_requests
        $this->table('coaching_service_requests', [
                'id' => false,
                'primary_key' => ['coaching_service_request_id'],
                'collation' => 'utf8mb4_0900_ai_ci',
            ])
            ->addColumn('coaching_service_request_id', 'char', [
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'null' => false,
            ])
            ->addColumn('appointment_id', 'uuid', [
                'null' => true,
            ])
            ->addColumn('service_title', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('service_type', 'string', [
                'limit' => 200,
                'null' => false,
            ])
            ->addColumn('notes', 'string', [
                'limit' => 1000,
                'null' => true,
            ])
            ->addColumn('final_price', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'null' => true,
            ])
            ->addColumn('request_status', 'enum', [
                'values' => ['pending', 'in_progress', 'completed', 'canceled', 'cancelled'],
                'default' => 'pending',
                'null' => false,
            ])
            ->addColumn('document', 'string', [
                'limit' => 255,
                'null' => true,
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
            ->addIndex(['user_id'], ['name' => 'idx_csr_user'])
            ->addIndex(['request_status'], ['name' => 'idx_csr_status'])
            ->addForeignKey('user_id', 'users', 'user_id', [
                'delete' => 'RESTRICT', 
                'update' => 'CASCADE'
            ])
            ->create();

        // Table: coaching_request_messages
        $this->table('coaching_request_messages', [
                'id' => false,
                'primary_key' => ['coaching_request_message_id'],
                'collation' => 'utf8mb4_0900_ai_ci',
            ])
            ->addColumn('coaching_request_message_id', 'uuid', [
                'null' => false,
            ])
            ->addColumn('coaching_service_request_id', 'char', [
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'null' => false,
            ])
            ->addColumn('message', 'text', [
                'null' => false,
            ])
            ->addColumn('is_read', 'boolean', [
                'default' => false,
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
            ->addIndex(['coaching_service_request_id'], ['name' => 'idx_crm_request'])
            ->addIndex(['user_id'], ['name' => 'idx_crm_user'])
            ->addForeignKey('coaching_service_request_id', 'coaching_service_requests', 'coaching_service_request_id', [
                'delete' => 'CASCADE', 
                'update' => 'CASCADE'
            ])
            ->addForeignKey('user_id', 'users', 'user_id', [
                'delete' => 'CASCADE', 
                'update' => 'CASCADE'
            ])
            ->create();

        // Table: coaching_service_payments
        $this->table('coaching_service_payments', [
                'id' => false,
                'primary_key' => ['coaching_service_payment_id'],
                'collation' => 'utf8mb4_0900_ai_ci',
            ])
            ->addColumn('coaching_service_payment_id', 'uuid', [
                'null' => false,
            ])
            ->addColumn('coaching_service_request_id', 'char', [
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'null' => false,
            ])
            ->addColumn('transaction_id', 'string', [
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
            ->addForeignKey('coaching_service_request_id', 'coaching_service_requests', 'coaching_service_request_id', [
                'delete' => 'CASCADE', 
                'update' => 'NO_ACTION'
            ])
            ->create();

        // Table: coaching_request_documents
        $this->table('coaching_request_documents', [
                'id' => false,
                'primary_key' => ['coaching_request_document_id'],
                'collation' => 'utf8mb4_0900_ai_ci',
            ])
            ->addColumn('coaching_request_document_id', 'uuid', [
                'null' => false,
            ])
            ->addColumn('coaching_service_request_id', 'char', [
                'limit' => 9,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'null' => false,
            ])
            ->addColumn('document_path', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('document_name', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('file_type', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('file_size', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('uploaded_by', 'string', [
                'default' => 'customer',
                'limit' => 50,
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
            ->addIndex(['coaching_service_request_id'], ['name' => 'idx_crd_request'])
            ->addIndex(['user_id'], ['name' => 'idx_crd_user'])
            ->addForeignKey('coaching_service_request_id', 'coaching_service_requests', 'coaching_service_request_id', [
                'delete' => 'CASCADE', 
                'update' => 'CASCADE'
            ])
            ->addForeignKey('user_id', 'users', 'user_id', [
                'delete' => 'CASCADE', 
                'update' => 'CASCADE'
            ])
            ->create();
    }
} 