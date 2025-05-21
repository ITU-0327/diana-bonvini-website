<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * Artwork Entity
 *
 * @property string $artwork_id
 * @property string $title
 * @property string|null $description
 * @property string $image_url
 * @property int $stock
 * @property string $availability_status
 * @property int $max_copies
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime $updated_at
 *
 * @property \App\Model\Entity\ArtworkVariant[] $artwork_variants
 */
class Artwork extends Entity
{
    protected array $_virtual = ['image_url', 'stock'];

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
        'availability_status' => true,
        'max_copies' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'artwork_variants' => true,
    ];

    /**
     * Virtual property for the public URL of the watermarked image.
     * First checks if the local file exists, otherwise tries to use R2/CDN.
     *
     * @return string
     */
    protected function _getImageUrl(): string
    {
        $endpoint = 'https://dianabonvini.com/cdn';
        $key = "{$this->artwork_id}_wm.jpg";

        return "$endpoint/$key";
    }

    /**
     * Virtual property for the available stock (max copies minus sold quantity).
     *
     * @return int
     */
    protected function _getStock(): int
    {
        // If entity has no ID yet (e.g., new artwork), skip stock calculation
        if (empty($this->artwork_id)) {
            return 0;
        }
        // Calculate total sold across confirmed/completed orders for this artwork
        $avOrders = TableRegistry::getTableLocator()
            ->get('ArtworkVariantOrders');
        $soldQuery = $avOrders->find()
            ->select(['sum' => 'SUM(ArtworkVariantOrders.quantity)'])
            ->matching('Orders', function ($q) {
                return $q->where(['Orders.order_status IN' => ['confirmed', 'completed']]);
            })
            ->matching('ArtworkVariants', function ($q) {
                return $q->where(['ArtworkVariants.artwork_id' => $this->artwork_id]);
            });
        $soldResult = $soldQuery->first();
        $sold = $soldResult ? (int)$soldResult->get('sum') : 0;
        $available = $this->max_copies - $sold;

        return max($available, 0);
    }
}
