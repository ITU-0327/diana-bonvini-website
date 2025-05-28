<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CoachingServiceRequest Entity
 *
 * @property string $coaching_service_request_id
 * @property string $user_id
 * @property string|null $appointment_id
 * @property string|null $service_title
 * @property string $service_type
 * @property string|null $notes
 * @property string|null $final_price
 * @property string $request_status
 * @property string|null $document
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Appointment $appointment
 * @property \App\Model\Entity\CoachingRequestMessage[] $coaching_request_messages
 * @property \App\Model\Entity\CoachingServicePayment[] $coaching_service_payments
 */
class CoachingServiceRequest extends Entity
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
        'coaching_request_messages' => true,
        'coaching_service_payments' => true,
    ];
    
    /**
     * Generate a unique 9-character ID for coaching service requests
     * Called automatically by CakePHP when the entity is created or when explicitly called
     *
     * @return string The generated ID
     */
    public function initializeCoachingServiceRequestId(): string
    {
        if (empty($this->coaching_service_request_id)) {
            // Generate a random 2-letter string for the middle part
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomLetters = $letters[rand(0, 25)] . $letters[rand(0, 25)];
            
            // Generate a 5-digit number - cast to string before str_pad
            $randomDigits = str_pad((string)rand(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // Format: C-XX12345
            $this->coaching_service_request_id = 'C-' . $randomLetters . $randomDigits;
        }
        
        return $this->coaching_service_request_id;
    }

    /**
     * Calculate the total amount of all paid payments for this request
     *
     * @return float The total amount paid
     */
    public function getTotalPaidAmount(): float
    {
        if (empty($this->coaching_service_payments)) {
            return 0.0;
        }

        $totalPaid = 0.0;

        foreach ($this->coaching_service_payments as $payment) {
            if ($payment->status === 'paid' && !$payment->is_deleted) {
                $totalPaid += (float)$payment->amount;
            }
        }

        return $totalPaid;
    }
} 