<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CoachingServicePayment Entity
 *
 * @property int $coaching_service_payment_id
 * @property string $coaching_service_request_id
 * @property string $amount
 * @property string|null $transaction_id
 * @property \Cake\I18n\DateTime $payment_date
 * @property string $payment_method
 * @property string $status
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 */
class CoachingServicePayment extends Entity
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
        'coaching_service_payment_id' => true,
        'coaching_service_request_id' => true,
        'amount' => true,
        'transaction_id' => true,
        'payment_date' => true,
        'payment_method' => true,
        'status' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'coaching_service_request' => true,
    ];
} 