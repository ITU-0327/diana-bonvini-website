<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ArtworkVariantCart Entity
 *
 * @property string $artwork_variant_cart_id
 * @property string $artwork_variant_id
 * @property string $cart_id
 * @property int $quantity
 * @property \Cake\I18n\DateTime $date_added
 *
 * @property \App\Model\Entity\ArtworkVariant $artwork_variant
 * @property \App\Model\Entity\Cart $cart
 */
class ArtworkVariantCart extends Entity
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
        'cart_id' => true,
        'quantity' => true,
        'date_added' => true,
        'artwork_variant' => true,
        'cart' => true,
    ];
}
