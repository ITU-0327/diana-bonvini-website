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
                'cart_id' => '48021e76-988e-4d6f-be86-c73aa07ca9ce',
                'user_id' => '08ab59d6-1af5-4516-8eea-978ee54a7d3e',
                'session_id' => 'Lorem ipsum dolor sit amet',
                'created_at' => '2025-03-16 21:28:56',
                'updated_at' => '2025-03-16 21:28:56',
            ],
        ];
        parent::init();
    }
}
