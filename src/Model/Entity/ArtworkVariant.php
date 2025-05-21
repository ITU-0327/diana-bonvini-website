<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ArtworkVariant Entity
 *
 * @property string $artwork_variant_id
 * @property string $artwork_id
 * @property string $dimension
 * @property float $price
 * @property string $print_type
 * @property bool $is_deleted
 *
 * @property \App\Model\Entity\Artwork $artwork
 */
class ArtworkVariant extends Entity
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
        'artwork_id' => true,
        'dimension' => true,
        'price' => true,
        'print_type' => true,
        'is_deleted' => true,
        'artwork' => true,
    ];
}
