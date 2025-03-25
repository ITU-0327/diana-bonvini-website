<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Order Entity
 *
 * @property string $order_id
 * @property string $user_id
 * @property float $total_amount
 * @property string $payment_method
 * @property string $order_status
 * @property \Cake\I18n\DateTime $order_date
 * @property string $billing_first_name
 * @property string $billing_last_name
 * @property string|null $billing_company
 * @property string $billing_email
 * @property string $shipping_country
 * @property string $shipping_address1
 * @property string|null $shipping_address2
 * @property string $shipping_suburb
 * @property string $shipping_state
 * @property string $shipping_postcode
 * @property string $shipping_phone
 * @property string|null $order_notes
 * @property int $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\ArtworkOrder[] $artwork_orders
 */
class Order extends Entity
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
        'total_amount' => true,
        'payment_method' => true,
        'order_status' => true,
        'order_date' => true,
        'billing_first_name' => true,
        'billing_last_name' => true,
        'billing_company' => true,
        'billing_email' => true,
        'shipping_country' => true,
        'shipping_address1' => true,
        'shipping_address2' => true,
        'shipping_suburb' => true,
        'shipping_state' => true,
        'shipping_postcode' => true,
        'shipping_phone' => true,
        'order_notes' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
        'artwork_orders' => true,
    ];
}
