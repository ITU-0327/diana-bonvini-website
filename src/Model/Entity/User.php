<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property string $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $password
 * @property string|null $phone_number
 * @property string|null $street_address
 * @property string|null $street_address2
 * @property string|null $suburb
 * @property string|null $state
 * @property string|null $postcode
 * @property string|null $country
 * @property string $user_type
 * @property string|null $password_reset_token
 * @property \Cake\I18n\DateTime|null $token_expiration
 * @property \Cake\I18n\DateTime|null $last_login
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'first_name' => true,
        'last_name' => true,
        'email' => true,
        'password' => true,
        'phone_number' => true,
        'street_address' => true,
        'street_address2' => true,
        'suburb' => true,
        'state' => true,
        'postcode' => true,
        'country' => true,
        'user_type' => true,
        'password_reset_token' => true,
        'token_expiration' => true,
        'last_login' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        // Allow oauth_provider for validation purposes only.
        'oauth_provider' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
    ];

    /**
     * _setPassword method
     *
     * @param string $password Password
     * @return string|null
     */
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }

        return null;
    }
}
