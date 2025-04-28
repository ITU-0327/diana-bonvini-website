<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ArtworkOrder Entity
 *
 * @property string $artwork_order_id
 * @property string $order_id
 * @property string $artwork_id
 * @property int $quantity
 * @property float $price
 * @property float $subtotal
 * @property bool $is_deleted
 *
 * @property \App\Model\Entity\Order $order
 * @property \App\Model\Entity\Artwork $artwork
 */
class ArtworkOrder extends Entity
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
        'artwork_id' => true,
        'quantity' => true,
        'price' => true,
        'subtotal' => true,
        'is_deleted' => true,
        'order' => true,
        'artwork' => true,
    ];
}
