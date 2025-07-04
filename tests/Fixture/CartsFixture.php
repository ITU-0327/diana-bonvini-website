<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CartsFixture
 */
class CartsFixture extends TestFixture
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
                'cart_id'    => 'cart-1234',
                'user_id'    => 'user-1234',
                'session_id' => 'session-1234',
                'created_at' => '2025-03-07 10:00:00',
                'updated_at' => '2025-03-07 10:00:00',
            ],
            [
                'cart_id'    => '75ce31f7-2f61-1584-a036-172eed157b8c',
                'user_id'    => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'session_id' => 'session',
                'created_at' => '2025-03-07 10:00:00',
                'updated_at' => '2025-03-07 10:00:00',
            ],
        ];
        parent::init();
    }
}
