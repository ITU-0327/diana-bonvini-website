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
                'request_id' => 'ea42d088-4396-46d7-92d1-4e60c59b2ef7',
                'user_id' => 'acaf89c5-f7a8-4ee3-b92e-77cd1462159e',
                'service_type' => 'Lorem ipsum dolor sit amet',
                'word_count_range' => 'Lorem ipsum dolor sit amet',
                'notes' => 'Lorem ipsum dolor sit amet',
                'estimated_price' => 1.5,
                'final_price' => 1.5,
                'request_status' => 'Lorem ipsum dolor sit amet',
                'is_deleted' => 1,
                'created_at' => '2025-03-17 12:54:09',
                'updated_at' => '2025-03-17 12:54:09',
            ],
        ];
        parent::init();
    }
}
