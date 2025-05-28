<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateRequestDocuments extends BaseMigration
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
        $table = $this->table('request_documents', [
            'id' => false,
            'primary_key' => ['request_document_id']
        ]);
        
        // Add columns
        $table->addColumn('request_document_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        
        $table->addColumn('writing_service_request_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        
        $table->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        
        $table->addColumn('document_path', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        
        $table->addColumn('document_name', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => false,
        ]);
        
        $table->addColumn('file_type', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => false,
        ]);
        
        $table->addColumn('file_size', 'integer', [
            'default' => 0,
            'null' => false,
        ]);
        
        $table->addColumn('uploaded_by', 'string', [
            'default' => 'customer',
            'limit' => 50,
            'null' => false,
        ]);
        
        $table->addColumn('is_deleted', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        
        $table->addColumn('created_at', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        
        $table->addColumn('updated_at', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        
        // Add indexes
        $table->addPrimaryKey(['request_document_id']);
        $table->addIndex('writing_service_request_id');
        $table->addIndex('user_id');
        
        $table->create();
    }
}
