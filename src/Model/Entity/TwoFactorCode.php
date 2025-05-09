<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TwoFactorCode Entity
 *
 * @property string $two_factor_code_id
 * @property string $user_id
 * @property string $code
 * @property \Cake\I18n\DateTime $expires
 * @property \Cake\I18n\DateTime $created_at
 *
 * @property \App\Model\Entity\User $user
 */
class TwoFactorCode extends Entity
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
        'code' => true,
        'expires' => true,
        'created_at' => true,
        'user' => true,
    ];
}
