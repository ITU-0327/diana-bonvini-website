<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RequestMessagesFixture
 */
class RequestMessagesFixture extends TestFixture
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
                'message_id' => '2230894e-b1a2-42c4-9b18-8830297b38eb',
                'request_id' => 'c32eef78-e6b8-4914-beca-8dc8d61a428d',
                'message' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created_at' => '2025-03-30 23:27:12',
                'updated_at' => '2025-03-30 23:27:12',
                'sender_id' => 'e4e1e7fa-5df3-4838-8f2c-db373ca4ae90',
            ],
        ];
        parent::init();
    }
}
