<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * GoogleCalendarSetting Entity
 *
 * @property string $setting_id
 * @property string $user_id
 * @property string $calendar_id
 * @property string|null $refresh_token
 * @property string|null $access_token
 * @property \Cake\I18n\DateTime|null $token_expires
 * @property bool $is_active
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\User $user
 */
class GoogleCalendarSetting extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'calendar_id' => true,
        'refresh_token' => true,
        'access_token' => true,
        'token_expires' => true,
        'is_active' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'refresh_token',
        'access_token',
    ];
}