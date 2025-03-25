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
                'request_id' => 'da244786-9cc6-4732-a38b-a69754ef317b',
                'user_id' => '154b8e54-e04a-496e-9d09-36ac92561da3',
                'service_type' => 'editing',
                'word_count_range' => '50000_plus',
                'notes' => 'Test writing service request for Tony.',
                'estimated_price' => 75000.00,
                'final_price' => null,
                'request_status' => 'pending',
                'is_deleted' => 0,
                'created_at' => '2025-03-20 11:52:06',
                'updated_at' => '2025-03-20 11:52:06',
                'document' => 'uploads/documents/1742471525_Meeting_Minutes___Project_Planning_and_Development__07_03_2025_.pdf',
            ],
            [
                'request_id' => '9b80b144-4efe-4639-9af0-ae968ac8586a',
                'user_id' => '154b8e54-e04a-496e-9d09-36ac92561da3',
                'service_type' => 'creative_writing',
                'word_count_range' => '5000_20000',
                'notes' => 'Test writing service request for Tony.',
                'estimated_price' => 40000.00,
                'final_price' => null,
                'request_status' => 'pending',
                'is_deleted' => 0,
                'created_at' => '2025-03-17 14:18:02',
                'updated_at' => '2025-03-24 05:13:35',
                'document' => null,
            ],
            [
                'request_id' => '7fa24a1e-8750-4639-aa58-b899c9c0ec49',
                'user_id' => '23fb5234-758b-4850-a7c2-061407318914',
                'service_type' => 'creative_writing',
                'word_count_range' => 'under_5000',
                'notes' => 'fsaf',
                'estimated_price' => 10000.00,
                'final_price' => null,
                'request_status' => 'pending',
                'is_deleted' => 0,
                'created_at' => '2025-03-19 21:30:08',
                'updated_at' => '2025-03-19 21:30:08',
                'document' => null,
            ],
        ];

        parent::init();
    }
}
