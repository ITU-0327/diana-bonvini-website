<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArtworkVariantsFixture
 */
class ArtworkVariantsFixture extends TestFixture
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
                'artwork_variant_id' => '5492e85e-f1b2-41f5-0000-000000000000',
                'artwork_id' => '5492e85e-f1b2-41f5-85cb-bfbe115b69ea',
                'dimension' => 'A3',
                'price' => 250.00,
                'is_deleted' => 0,
            ],
            [
                'artwork_variant_id' => '8424e85e-f1b2-41f5-0000-000000000000',
                'artwork_id' => '8424e85e-f1b2-41f5-85cb-bfbe115b45bc',
                'dimension' => 'A3',
                'price' => 100.00,
                'is_deleted' => 0,
            ],
            [
                'artwork_variant_id' => 'artwork-sold-0000000000000000',
                'artwork_id' => 'artwork-sold',
                'dimension' => 'A3',
                'price' => 200.00,
                'is_deleted' => 0,
            ],
            [
                'artwork_variant_id' => 'artwork-deleted-0000000000000000',
                'artwork_id' => 'artwork-deleted',
                'dimension' => 'A3',
                'price' => 100.00,
                'is_deleted' => 0,
            ],
        ];
        parent::init();
    }
}
