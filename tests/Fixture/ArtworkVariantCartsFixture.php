<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArtworkVariantCartsFixture
 */
class ArtworkVariantCartsFixture extends TestFixture
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
                'artwork_variant_cart_id' => 'item-1',
                'cart_id' => 'cart-1234',
                'artwork_variant_id' => '8424e85e-f1b2-41f5-0000-000000000000',
                'quantity' => 1,
                'is_deleted' => 0,
                'date_added' => '2025-03-07 10:00:00',
            ],
            [
                'artwork_variant_cart_id' => 'item-2',
                'cart_id' => 'cart-1234',
                'artwork_variant_id' => 'artwork-deleted-0000000000000000',
                'quantity' => 1,
                'is_deleted' => 0,
                'date_added' => '2025-03-07 10:00:00',
            ],
            [
                'artwork_variant_cart_id' => 'item-3',
                'cart_id' => 'cart-1234',
                'artwork_variant_id' => 'artwork-sold-0000000000000000',
                'quantity' => 1,
                'is_deleted' => 0,
                'date_added' => '2025-03-07 10:00:00',
            ],
        ];
        parent::init();
    }
}
