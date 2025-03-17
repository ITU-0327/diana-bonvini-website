<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArtworksFixture
 */
class ArtworksFixture extends TestFixture
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
                'artwork_id'           => '5492e85e-f1b2-41f5-85cb-bfbe115b69ea',
                'title'                => 'Sunset Over the Ocean',
                'description'          => 'A vibrant painting capturing the dynamic colors of a sunset over the vast ocean, evoking a sense of peace and wonder.',
                'image_path'           => 'uploads/artworks/sunset_over_the_ocean.png',
                'price'                => 250.00,
                'availability_status'  => 'available',
                'is_deleted'           => 0,
                'created_at'           => '2025-03-10 09:15:43',
                'updated_at'           => '2025-03-10 09:15:43',
            ],
            [
                'artwork_id'           => '8424e85e-f1b2-41f5-85cb-bfbe115b45bc',
                'title'                => 'Sunset Over the Ocean',
                'description'          => 'A vibrant painting capturing the dynamic colors of a sunset over the vast ocean, evoking a sense of peace and wonder.',
                'image_path'           => 'uploads/artworks/sunset_over_the_ocean.png',
                'price'                => 560.00,
                'availability_status'  => 'available',
                'is_deleted'           => 0,
                'created_at'           => '2025-03-10 09:15:43',
                'updated_at'           => '2025-03-10 09:15:43',
            ],
            [
                'artwork_id'           => 'a0f92c2a-2bb2-4d3e-8d33-0c654c9c1a6b',
                'title'                => 'Mystical Forest',
                'description'          => 'An enchanting depiction of a dense forest shrouded in mist and mystery, designed to inspire awe and introspection.',
                'image_path'           => 'uploads/artworks/mystical_forest.png',
                'price'                => 325.50,
                'availability_status'  => 'sold',
                'is_deleted'           => 0,
                'created_at'           => '2025-03-11 14:22:00',
                'updated_at'           => '2025-03-11 14:22:00',
            ],
            [
                'artwork_id'           => 'b7c65c2a-2bb2-4d3e-8d33-0c845c9c1a5c',
                'title'                => 'Mystical Forest',
                'description'          => 'An enchanting depiction of a dense forest shrouded in mist and mystery, designed to inspire awe and introspection.',
                'image_path'           => 'uploads/artworks/mystical_forest.png',
                'price'                => 452.50,
                'availability_status'  => 'available',
                'is_deleted'           => 1,
                'created_at'           => '2025-03-11 14:22:00',
                'updated_at'           => '2025-03-11 14:22:00',
            ],
        ];
        parent::init();
    }
}
