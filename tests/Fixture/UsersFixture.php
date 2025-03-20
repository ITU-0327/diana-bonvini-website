<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $hasher = new DefaultPasswordHasher();
        $this->records = [
            [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'first_name' => 'Tony',
                'last_name' => 'Hsieh',
                'email' => 'tony.hsieh@example.com',
                'password' => $hasher->hash('password'),
                'phone_number' => '1234567890',
                'address' => '123 Main St',
                'user_type' => 'customer',
                'last_login' => '2025-03-10 09:12:26',
                'is_deleted' => 0,
                'created_at' => '2025-03-10 09:12:26',
                'updated_at' => '2025-03-10 09:12:26',
            ],
            [
                'user_id' => '45he31f7-8g55-5847-a036-158eed559e5c',
                'first_name' => 'Soft',
                'last_name' => 'Deleted',
                'email' => 'soft.deleted@example.com',
                'password' => $hasher->hash('SecureP@ssw0rd'),
                'phone_number' => '1234567890',
                'address' => '123 Main St',
                'user_type' => 'customer',
                'last_login' => '2025-03-10 09:12:26',
                'is_deleted' => 1,
                'created_at' => '2025-03-10 09:12:26',
                'updated_at' => '2025-03-10 09:12:26',
            ],
            [
                'user_id' => '84ab31f7-8g55-4584-a036-158eed555b6a',
                'first_name' => 'Valid',
                'last_name' => 'User',
                'email' => 'valid.user@example.com',
                'password' => $hasher->hash('SecureP@ssw0rd'),
                'phone_number' => '1234567890',
                'address' => '123 Main St',
                'user_type' => 'customer',
                'last_login' => '2025-03-10 09:12:26',
                'password_reset_token' => 'valid-reset-token',
                'token_expiration' => '2026-1-1 09:12:26',
                'is_deleted' => 0,
                'created_at' => '2025-03-10 09:12:26',
                'updated_at' => '2025-03-10 09:12:26',
            ],
            [
                'user_id'    => 'user-1234',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john@example.com',
                'password'   => 'password',
                'phone_number' => '1234567890',
                'address'    => '123 Main St',
                'user_type'  => 'customer',
                'last_login' => null,
                'is_deleted' => 0,
                'created_at' => '2025-03-07 10:00:00',
                'updated_at' => '2025-03-07 10:00:00',
            ],
        ];
        parent::init();
    }
}
