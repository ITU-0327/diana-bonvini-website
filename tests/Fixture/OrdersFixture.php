<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OrdersFixture
 */
class OrdersFixture extends TestFixture
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
                'order_id' => 'f0c33e96-1d01-472d-b1e1-b00951b56ee9',
                'user_id' => '245d52dd-1f57-4bdb-9cc2-003e4f69b840',
                'total_amount' => 1.5,
                'payment_method' => 'Lorem ipsum dolor sit amet',
                'order_status' => 'Lorem ipsum dolor sit amet',
                'order_date' => '2025-03-10 09:15:58',
                'is_deleted' => 1,
                'created_at' => '2025-03-10 09:15:58',
                'updated_at' => '2025-03-10 09:15:58',
            ],
        ];
        parent::init();
    }
}
