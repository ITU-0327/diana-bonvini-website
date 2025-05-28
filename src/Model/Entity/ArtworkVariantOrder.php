<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ArtworkVariantOrder Entity
 *
 * @property string $artwork_variant_order_id
 * @property string $artwork_variant_id
 * @property string $order_id
 * @property int $quantity
 * @property float $price
 * @property float $subtotal
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\ArtworkVariant $artwork_variant
 * @property \App\Model\Entity\Order $order
 */
class ArtworkVariantOrder extends Entity
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
        'artwork_variant_id' => true,
        'order_id' => true,
        'quantity' => true,
        'price' => true,
        'subtotal' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'artwork_variant' => true,
        'order' => true,
    ];
}
