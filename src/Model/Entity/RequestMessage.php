<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RequestMessage Entity
 *
 * @property string $message_id
 * @property string $request_id
 * @property string $sender_type
 * @property string $message
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\WritingServiceRequest $request
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
        'request_id' => true,
        'sender_type' => true,
        'message' => true,
        'created_at' => true,
        'updated_at' => true,
        'request' => true,
    ];
}
