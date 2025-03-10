<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PaymentsFixture
 */
class PaymentsFixture extends TestFixture
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
                'payment_id' => '7c5c91aa-2bc1-42a8-abf0-b8b0e8b60100',
                'order_id' => 'cdaf5447-977a-46e5-b2f8-20e40bc20b81',
                'amount' => 1.5,
                'payment_date' => '2025-03-10 09:17:31',
                'payment_method' => 'Lorem ipsum dolor sit amet',
                'status' => 'Lorem ipsum dolor sit amet',
                'is_deleted' => 1,
            ],
        ];
        parent::init();
    }
}
