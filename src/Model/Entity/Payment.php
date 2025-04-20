<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Payment Entity
 *
 * @property string $payment_id
 * @property string $order_id
 * @property float $amount
 * @property string|null $transaction_id
 * @property \Cake\I18n\DateTime $payment_date
 * @property string $payment_method
 * @property string $status
 * @property int $is_deleted
 *
 * @property \App\Model\Entity\Order $order
 */
class Payment extends Entity
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
        'order_id' => true,
        'amount' => true,
        'transaction_id' => true,
        'payment_date' => true,
        'payment_method' => true,
        'status' => true,
        'is_deleted' => true,
        'order' => true,
    ];
}
