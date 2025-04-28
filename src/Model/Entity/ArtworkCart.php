<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ArtworkCart Entity
 *
 * @property string $artwork_cart_id
 * @property string $cart_id
 * @property string $artwork_id
 * @property int $quantity
 * @property \Cake\I18n\DateTime $date_added
 * @property bool $is_deleted
 *
 * @property \App\Model\Entity\Cart $cart
 * @property \App\Model\Entity\Artwork $artwork
 */
class ArtworkCart extends Entity
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
        'cart_id' => true,
        'artwork_id' => true,
        'quantity' => true,
        'date_added' => true,
        'is_deleted' => true,
        'cart' => true,
        'artwork' => true,
    ];
}
