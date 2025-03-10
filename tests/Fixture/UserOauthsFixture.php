<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UserOauthsFixture
 */
class UserOauthsFixture extends TestFixture
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
                'oauth_id' => '44ffd9c9-19f0-4bf5-913d-45e7965c5eca',
                'user_id' => '384523bd-783f-454a-bb5d-fd42f491e2fa',
                'provider' => 'Lorem ipsum dolor sit amet',
                'provider_user_id' => 'Lorem ipsum dolor sit amet',
                'access_token' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'refresh_token' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'token_expires_at' => '2025-03-10 09:12:41',
                'is_deleted' => 1,
                'created_at' => '2025-03-10 09:12:41',
                'updated_at' => '2025-03-10 09:12:41',
            ],
        ];
        parent::init();
    }
}
