<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WritingServiceRequest Entity
 *
 * @property string $request_id
 * @property string $user_id
 * @property string $service_type
 * @property string $word_count_range
 * @property string|null $notes
 * @property string|null $estimated_price
 * @property string|null $final_price
 * @property string $request_status
 * @property int $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\User $user
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
        'user_id' => true,
        'service_type' => true,
        'word_count_range' => true,
        'notes' => true,
        'estimated_price' => true,
        'final_price' => true,
        'request_status' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
        'document' => true,
        '*' => true,
    ];
}
