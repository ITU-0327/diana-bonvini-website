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

    /**
     * Calculate the total amount of all paid payments for this request
     *
     * @return float The total amount paid
     */
    public function getTotalPaidAmount(): float
    {
        if (empty($this->writing_service_payments)) {
            return 0.0;
        }
        
        $total = 0.0;
        foreach ($this->writing_service_payments as $payment) {
            if ($payment->status === 'paid') {
                $total += (float)$payment->amount;
            }
        }
        
        return $total;
    }
    
    /**
     * Calculate the total amount of all pending payments for this request
     *
     * @return float The total pending amount
     */
    public function getTotalPendingAmount(): float
    {
        if (empty($this->writing_service_payments)) {
            return 0.0;
        }
        
        $total = 0.0;
        foreach ($this->writing_service_payments as $payment) {
            if ($payment->status === 'pending') {
                $total += (float)$payment->amount;
            }
        }
        
        return $total;
    }
    
    /**
     * Get combined total of both paid and pending payments
     *
     * @return float The total combined amount
     */
    public function getTotalAmount(): float
    {
        return $this->getTotalPaidAmount() + $this->getTotalPendingAmount();
    }
    
    /**
     * Format the total paid amount as a currency string
     *
     * @param bool $includePending Whether to include pending payments in the total
     * @return string The formatted total amount
     */
    public function getFormattedTotalPaid(bool $includePending = false): string
    {
        $total = $includePending ? $this->getTotalAmount() : $this->getTotalPaidAmount();
        if ($total <= 0) {
            return '-';
        }
        
        return '$' . number_format($total, 2);
    }
    
    /**
     * Get a summary of payment information
     *
     * @return array An array with payment counts and amounts
     */
    public function getPaymentSummary(): array
    {
        $pendingPayments = 0;
        $paidPayments = 0;
        $totalPaid = 0;
        $totalPending = 0;

        if (!empty($this->writing_service_payments)) {
            foreach ($this->writing_service_payments as $payment) {
                if ($payment->status === 'paid') {
                    $paidPayments++;
                    $totalPaid += (float)$payment->amount;
                } else {
                    $pendingPayments++;
                    $totalPending += (float)$payment->amount;
                }
            }
        }
        
        return [
            'paidCount' => $paidPayments,
            'pendingCount' => $pendingPayments,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending,
            'totalAmount' => $totalPaid + $totalPending,
            'formattedTotalPaid' => '$' . number_format($totalPaid, 2),
            'formattedTotalPending' => '$' . number_format($totalPending, 2),
            'formattedTotalAmount' => '$' . number_format($totalPaid + $totalPending, 2),
        ];
    }
}
