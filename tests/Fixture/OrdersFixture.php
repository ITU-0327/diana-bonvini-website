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
                'order_status' => 'Lorem ipsum dolor sit amet',
                'order_date' => '2025-03-25 05:01:14',
                'billing_first_name' => 'Lorem ipsum dolor sit amet',
                'billing_last_name' => 'Lorem ipsum dolor sit amet',
                'billing_company' => 'Lorem ipsum dolor sit amet',
                'billing_email' => 'Lorem ipsum dolor sit amet',
                'shipping_country' => '',
                'shipping_address1' => 'Lorem ipsum dolor sit amet',
                'shipping_address2' => 'Lorem ipsum dolor sit amet',
                'shipping_suburb' => 'Lorem ipsum dolor sit amet',
                'shipping_state' => 'Lorem ipsum dolor sit amet',
                'shipping_postcode' => 'Lorem ipsum dolor ',
                'shipping_phone' => 'Lorem ipsum dolor sit amet',
                'order_notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'is_deleted' => 1,
                'created_at' => '2025-03-25 05:01:14',
                'updated_at' => '2025-03-25 05:01:14',
            ],
        ];
        parent::init();
    }
}
