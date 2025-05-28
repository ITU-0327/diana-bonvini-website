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
                'artwork_id' => '5492e85e-f1b2-41f5-85cb-bfbe115b69ea',
                'title' => 'Sunset Over the Ocean',
                'description' => 'A vibrant painting capturing the dynamic colors of a sunset over the vast ocean, evoking a sense of peace and wonder.',
                'availability_status' => 'available',
                'max_copies' => 5,
                'is_deleted' => 0,
                'created_at' => '2025-03-10 09:15:43',
                'updated_at' => '2025-03-10 09:15:43',
            ],
            [
                'artwork_id' => '8424e85e-f1b2-41f5-85cb-bfbe115b45bc',
                'title' => 'Valid Art',
                'description' => 'A valid artwork',
                'availability_status' => 'available',
                'max_copies' => 5,
                'is_deleted' => 0,
                'created_at' => '2025-03-07 10:00:00',
                'updated_at' => '2025-03-07 10:00:00',
            ],
            [
                'artwork_id' => 'artwork-deleted',
                'title' => 'Deleted Art',
                'description' => 'A deleted artwork',
                'availability_status' => 'available',
                'max_copies' => 5,
                'is_deleted' => 1,
                'created_at' => '2025-03-07 10:00:00',
                'updated_at' => '2025-03-07 10:00:00',
            ],
            [
                'artwork_id' => 'artwork-sold',
                'title' => 'Sold Art',
                'description' => 'A sold artwork',
                'availability_status' => 'sold',
                'max_copies' => 5,
                'is_deleted' => 0,
                'created_at' => '2025-03-07 10:00:00',
                'updated_at' => '2025-03-07 10:00:00',
            ],
        ];
        parent::init();
    }
}
