<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * Format Helper
 * 
 * Provides utility methods to format various data in a user-friendly way
 */
class FormatHelper extends Helper
{
    /**
     * Format a UUID or long ID into a more user-friendly format
     * 
     * @param string $id The raw user ID (UUID or other format)
     * @param string $prefix The prefix to add (default 'U-')
     * @param int $length The number of characters to keep (default 8)
     * @return string The formatted user ID (e.g., U-1234ABCD)
     */
    public function userId($id, $prefix = 'U-', $length = 8): string
    {
        if (empty($id)) {
            return $prefix . '00000000';
        }
        
        // Handle UUIDs with dashes
        $cleanId = str_replace('-', '', $id);
        
        // For numeric IDs, pad with zeros
        if (is_numeric($cleanId)) {
            return $prefix . str_pad($cleanId, $length, '0', STR_PAD_LEFT);
        }
        
        // Take the first X characters (default 8)
        $shortId = substr($cleanId, 0, $length);
        
        // Convert to uppercase for better readability
        $formattedId = strtoupper($shortId);
        
        // Add prefix
        return $prefix . $formattedId;
    }
    
    /**
     * Format an order ID in a user-friendly way
     * 
     * @param string $id The raw order ID
     * @return string The formatted order ID (e.g., ORD-1234ABCD)
     */
    public function orderId($id): string
    {
        return $this->userId($id, 'ORD-');
    }
} 