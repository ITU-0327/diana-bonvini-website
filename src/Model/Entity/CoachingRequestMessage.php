<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CoachingRequestMessage Entity
 *
 * @property string $coaching_request_message_id
 * @property string $coaching_service_request_id
 * @property string $user_id
 * @property string $message
 * @property bool $is_read
 * @property bool $is_deleted
 * @property \Cake\I18n\FrozenTime $created_at
 * @property \Cake\I18n\FrozenTime $updated_at
 *
 * @property \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @property \App\Model\Entity\User $user
 */
class CoachingRequestMessage extends Entity
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
        'coaching_service_request_id' => true,
        'user_id' => true,
        'message' => true,
        'is_read' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'coaching_service_request' => true,
        'user' => true,
    ];
} 