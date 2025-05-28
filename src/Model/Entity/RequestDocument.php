<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RequestDocument Entity
 *
 * @property string $request_document_id
 * @property string $writing_service_request_id
 * @property string $user_id
 * @property string $document_path
 * @property string $document_name
 * @property string $file_type
 * @property int $file_size
 * @property string $uploaded_by
 * @property bool $is_deleted
 * @property \Cake\I18n\DateTime $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 *
 * @property \App\Model\Entity\WritingServiceRequest $writing_service_request
 * @property \App\Model\Entity\User $user
 */
class RequestDocument extends Entity
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
        'request_document_id' => true,
        'writing_service_request_id' => true,
        'user_id' => true,
        'document_path' => true,
        'document_name' => true,
        'file_type' => true,
        'file_size' => true,
        'uploaded_by' => true,
        'is_deleted' => true,
        'created_at' => true,
        'updated_at' => true,
        'writing_service_request' => true,
        'user' => true,
    ];
    
    /**
     * Auto-populate timestamps before saving
     */
    protected function _setCreatedAt($created_at)
    {
        if ($created_at === null) {
            return new \Cake\I18n\DateTime();
        }
        return $created_at;
    }
    
    /**
     * Auto-generate UUID for request_document_id if not set
     */
    protected function _setRequestDocumentId($id)
    {
        if (empty($id)) {
            return \Cake\Utility\Text::uuid();
        }
        return $id;
    }
    
    /**
     * Get file extension as virtual field
     */
    protected function _getFileExtension()
    {
        return pathinfo($this->document_name, PATHINFO_EXTENSION);
    }
    
    /**
     * Get formatted file size as virtual field
     */
    protected function _getFormattedSize()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 1) . ' ' . $units[$pow];
    }
}
