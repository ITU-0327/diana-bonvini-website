<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WritingServiceRequestsFixture
 */
class WritingServiceRequestsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'request_id' => '16ce70e8-b0dc-401d-9188-6c7734fd032b',
                'user_id' => 'a9f03146-2801-4c5f-81e0-07d2fcaa0264',
                'service_type' => 'Lorem ipsum dolor sit amet',
                'word_count_range' => 'Lorem ipsum dolor sit amet',
                'notes' => 'Lorem ipsum dolor sit amet',
                'estimated_price' => 1.5,
                'final_price' => 1.5,
                'request_status' => 'Lorem ipsum dolor sit amet',
                'is_deleted' => 1,
                'created_at' => '2025-03-17 12:49:28',
                'updated_at' => '2025-03-17 12:49:28',
            ],
        ];
        parent::init();
    }
}
