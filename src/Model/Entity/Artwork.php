<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Artwork Entity
 *
 * @property string $artwork_id
 * @property string $title
 * @property string|null $description
 * @property string $image_url
 * @property float $price
 * @property string $availability_status
 * @property int $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 */
class Artwork extends Entity
{
    protected array $_virtual = ['image_url'];

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
        'title' => true,
        'description' => true,
        'price' => true,
        'availability_status' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
    ];

    /**
     * Virtual property for the public URL of the watermarked image in R2.
     *
     * @return string
     */
    protected function _getImageUrl(): string
    {
        $endpoint = 'https://dianabonvini.com/cdn';
        $key = "{$this->artwork_id}_wm.jpg";

        return "$endpoint/$key";
    }
}
