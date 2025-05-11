<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Appointment Entity
 *
 * @property string $appointment_id
 * @property string $user_id
 * @property string|null $writing_service_request_id
 * @property string $appointment_type
 * @property \Cake\I18n\Date $appointment_date
 * @property \Cake\I18n\Time $appointment_time
 * @property int $duration
 * @property string|null $description
 * @property string|null $location
 * @property string $status
 * @property bool $is_google_synced
 * @property string|null $google_calendar_event_id
 * @property string|null $meeting_link
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\WritingServiceRequest|null $writing_service_request
 */
class Appointment extends Entity
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
        'writing_service_request_id' => true,
        'appointment_type' => true,
        'appointment_date' => true,
        'appointment_time' => true,
        'duration' => true,
        'description' => true,
        'location' => true,
        'status' => true,
        'is_google_synced' => true,
        'google_calendar_event_id' => true,
        'meeting_link' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
        'writing_service_request' => true,
    ];
}
