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
                'title' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'image_path' => 'Lorem ipsum dolor sit amet',
                'price' => 1.5,
                'availability_status' => 'Lorem ipsum dolor sit amet',
                'is_deleted' => 1,
                'created_at' => '2025-03-10 09:15:43',
                'updated_at' => '2025-03-10 09:15:43',
            ],
        ];
        parent::init();
    }
}
