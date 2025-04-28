<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RequestMessage Entity
 *
 * @property string $request_message_id
 * @property string $writing_service_request_id
 * @property string $user_id
 * @property string $message
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\WritingServiceRequest $writing_service_request
 * @property \App\Model\Entity\User $user
 */
class RequestMessage extends Entity
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
        'writing_service_request_id' => true,
        'user_id' => true,
        'message' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'writing_service_request' => true,
        'user' => true,
    ];
}
