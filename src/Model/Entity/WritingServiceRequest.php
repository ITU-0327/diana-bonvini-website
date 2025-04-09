<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WritingServiceRequest Entity
 *
 * @property string $writing_service_request_id
 * @property string $service_title
 * @property string $user_id
 * @property string $service_type
 * @property string|null $notes
 * @property string|null $final_price
 * @property string $request_status
 * @property int $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 * @property string|null $document
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\RequestMessage[] $request_messages
 */
class WritingServiceRequest extends Entity
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
        'service_title' => true,
        'user_id' => true,
        'service_type' => true,
        'notes' => true,
        'final_price' => true,
        'request_status' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'document' => true,
        'user' => true,
        'request_messages' => true,
    ];
}
