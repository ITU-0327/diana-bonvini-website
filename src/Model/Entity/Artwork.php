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
        // Debug information
        $debugInfo = "Artwork ID: {$this->artwork_id}\n";
        
        // Add a cache busting query parameter
        $cacheBuster = '?v=' . (time() % 10000);
        
        // Check if local watermarked file exists
        $localPath = WWW_ROOT . 'img' . DS . 'watermarked' . DS . "{$this->artwork_id}.jpg";
        $debugInfo .= "Checking watermarked path: $localPath\n";
        
        if (file_exists($localPath)) {
            // Use the local watermarked image
            $url = "/img/watermarked/{$this->artwork_id}.jpg" . $cacheBuster;
            $debugInfo .= "Using watermarked image: $url\n";
            file_put_contents(LOGS . 'image_debug.log', $debugInfo, FILE_APPEND);
            return $url;
        }
        
        // Check if original image exists as fallback
        $originalPath = WWW_ROOT . 'img' . DS . 'Artworks' . DS . "{$this->artwork_id}.jpg";
        $debugInfo .= "Checking original path: $originalPath\n";
        
        if (file_exists($originalPath)) {
            $url = "/img/Artworks/{$this->artwork_id}.jpg" . $cacheBuster;
            $debugInfo .= "Using original image: $url\n";
            file_put_contents(LOGS . 'image_debug.log', $debugInfo, FILE_APPEND);
            return $url;
        }
        
        // If we get here, try the R2/CDN path as a last resort
        $url = "https://dianabonvini.com/cdn/{$this->artwork_id}_wm.jpg" . $cacheBuster;
        $debugInfo .= "Falling back to CDN: $url\n";
        file_put_contents(LOGS . 'image_debug.log', $debugInfo, FILE_APPEND);
        return $url;
    }
}
