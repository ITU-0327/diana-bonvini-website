<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TrustedDevice Entity
 *
 * @property string $trusted_device_id
 * @property string $user_id
 * @property string $device_id
 * @property \Cake\I18n\DateTime $expires
 * @property \Cake\I18n\DateTime $created_at
 *
 * @property \App\Model\Entity\User $user
 */
class TrustedDevice extends Entity
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
        'device_id' => true,
        'expires' => true,
        'created_at' => true,
        'user' => true,
    ];
}
