<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TrustedDevicesFixture
 */
class TrustedDevicesFixture extends TestFixture
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
                'trusted_device_id' => 'e10e1f5a-587a-4916-baa8-6e29a7f4c77e',
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'device_id' => 'test-device-xyz',
                'expires' => '3025-05-09 05:51:31',
                'created_at' => '2025-05-09 05:51:31',
            ],
        ];
        parent::init();
    }
}
