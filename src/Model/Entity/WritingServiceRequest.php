<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WritingServiceRequest Entity
 *
 * @property string $writing_service_request_id
 * @property string $user_id
 * @property string|null $appointment_id
 * @property string|null $service_title
 * @property string $service_type
 * @property string|null $notes
 * @property float $final_price
 * @property string $request_status
 * @property string|null $document
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Appointment $appointment
 * @property \App\Model\Entity\RequestMessage[] $request_messages
 * @property \App\Model\Entity\WritingServicePayment[] $writing_service_payments
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
        'appointment_id' => true,
        'service_title' => true,
        'service_type' => true,
        'notes' => true,
        'final_price' => true,
        'request_status' => true,
        'document' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
        'request_messages' => true,
        'writing_service_payments' => true,
    ];
    
    /**
     * Generate a unique 9-character ID for writing service requests
     * Called automatically by CakePHP when the entity is created or when explicitly called
     *
     * @return string The generated ID
     */
    public function initializeWritingServiceRequestId(): string
    {
        if (empty($this->writing_service_request_id)) {
            // Generate a random 2-letter string for the middle part
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomLetters = $letters[rand(0, 25)] . $letters[rand(0, 25)];
            
            // Generate a 5-digit number - cast to string before str_pad
            $randomDigits = str_pad((string)rand(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // Format: R-XX12345
            $this->writing_service_request_id = 'R-' . $randomLetters . $randomDigits;
        }
        
        return $this->writing_service_request_id;
    }
}
