<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class WritingServiceRequestsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'request_id' => 'ea42d088-4396-46d7-92d1-4e60c59b2ef7',
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'service_type' => 'creative_writing',
                'word_count_range' => 'under_5000',
                'notes' => 'Test writing service request for Tony.',
                'estimated_price' => 100.00,
                'final_price' => 150.00,
                'request_status' => 'pending',
                'is_deleted' => 0,
                'created_at' => '2025-03-17 12:54:09',
                'updated_at' => '2025-03-17 12:54:09',
                'document' => 'uploads/documents/test.pdf',
            ],
        ];

        parent::init();
    }
}
