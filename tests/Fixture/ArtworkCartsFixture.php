<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArtworkCartsFixture
 */
class ArtworkCartsFixture extends TestFixture
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
                'artwork_cart_id' => 'item-1',
                'cart_id'      => 'cart-1234',
                'artwork_id'   => '8424e85e-f1b2-41f5-85cb-bfbe115b45bc',
                'quantity'     => 1,
                'is_deleted'   => 0,
                'date_added'   => '2025-03-07 10:00:00',
            ],
            [
                'artwork_cart_id' => 'item-2',
                'cart_id'      => 'cart-1234',
                'artwork_id'   => 'artwork-deleted',
                'quantity'     => 1,
                'is_deleted'   => 0,
                'date_added'   => '2025-03-07 10:00:00',
            ],
            [
                'artwork_cart_id' => 'item-3',
                'cart_id'      => 'cart-1234',
                'artwork_id'   => 'artwork-sold',
                'quantity'     => 1,
                'is_deleted'   => 0,
                'date_added'   => '2025-03-07 10:00:00',
            ],
        ];
        parent::init();
    }
}
