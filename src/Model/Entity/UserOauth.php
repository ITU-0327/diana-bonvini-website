<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UserOauth Entity
 *
 * @property string $oauth_id
 * @property string $user_id
 * @property string $provider
 * @property string $provider_user_id
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property \Cake\I18n\DateTime|null $token_expires_at
 * @property int $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\User $user
 */
class UserOauth extends Entity
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
        'user_id' => true,
        'provider' => true,
        'provider_user_id' => true,
        'access_token' => true,
        'refresh_token' => true,
        'token_expires_at' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
    ];
}
