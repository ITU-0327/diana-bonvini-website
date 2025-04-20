<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\Order;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use DateTimeInterface;
use Exception;

/**
 * Order Mailer class for sending order-related emails
 */
class OrderMailer extends Mailer
{
    /**
     * Sends order confirmation email
     *
     * @param \App\Model\Entity\Order $order The order entity
     * @return bool Success
     */
    public function confirmation(Order $order): bool
    {
        try {
            // Debug log to check what's happening
            Log::debug('Starting email confirmation for order: ' . $order->order_id);

            // Make sure all needed data is available
            $customerEmail = $order->billing_email ?? null;
            if (empty($customerEmail)) {
                Log::error('No customer email available for order: ' . $order->order_id);

                return false;
            }

            Log::debug('Using email: ' . $customerEmail);

            // Extract specific properties and log them
            $customerName = ($order->billing_first_name ?? '') . ' ' . ($order->billing_last_name ?? '');
            $customerName = trim($customerName) ?: 'Valued Customer';

            // Handle date properly - convert string to proper format
            $orderDateStr = null;
            if ($order->order_date instanceof DateTimeInterface) {
                $orderDateStr = $order->order_date->format('F j, Y');
            } elseif (is_string($order->order_date)) {
                $timestamp = strtotime($order->order_date);
                if ($timestamp !== false) {
                    $orderDateStr = date('F j, Y', $timestamp);
                }
            }

            // Fallback if date processing failed
            if (empty($orderDateStr)) {
                $orderDateStr = date('F j, Y');
            }

            $orderNumber = $order->order_id ?? 'N/A';

            Log::debug('Order details: ' . json_encode([
                    'customerName' => $customerName,
                    'orderNumber' => $orderNumber,
                    'orderDate' => $orderDateStr,
                ]));

            // Get order items and prepare them for email display
            $orderItems = $order->artwork_orders ?? [];
            $this->_prepareOrderItemImages($orderItems);

            $this
                ->setTo($customerEmail)
                ->setSubject('Your Order Confirmation - Diana Bonvini')
                ->setEmailFormat('html') // Changed from 'both' to 'html' for better image handling
                ->setViewVars([
                    // Pass the full order object
                    'order' => $order,
                    // Also pass individual variables to prevent undefined errors
                    'orderItems' => $orderItems,
                    'customer_name' => $customerName,
                    'order_date' => $orderDateStr,
                    'order_number' => $orderNumber,
                    'order_total' => $order->total_amount ?? 0,
                    'shipping_address' => $this->_formatShippingAddress($order),
                    'payment_method' => 'Credit Card',
                    'delivery_method' => 'Standard Shipping',
                    'estimated_delivery' => 'To be determined',
                ]);

            Log::debug('View vars set, setting template');

            // Set template and layout
            $this->viewBuilder()
                ->setTemplate('order_confirmation')
                ->setLayout('default');

            Log::debug('Email configuration complete');

            return true;
        } catch (Exception $e) {
            Log::error('Error in OrderMailer::confirmation: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return false; // Return false instead of re-throwing
        }
    }

    /**
     * Prepare order item images for email display
     * This method adds embedded images to the email for each artwork
     *
     * @param array<\App\Model\Entity\ArtworkOrder> $orderItems The order items
     * @return void
     */
    private function _prepareOrderItemImages(array $orderItems): void
    {
        if (empty($orderItems)) {
            return;
        }

        // IMPORTANT: Use your actual production domain here instead of localhost
        $baseUrl = 'https://www.dianabonvini.com/'; // or your actual production domain

        foreach ($orderItems as $index => $item) {
            if (!isset($item->artwork)) {
                continue;
            }

            if (!empty($item->artwork->image_path)) {
                // Create absolute URL for the image
                // Remove any localhost references
                $imagePath = $item->artwork->image_path;

                // Make sure path doesn't start with a slash
                if (strpos($imagePath, '/') === 0) {
                    $imagePath = substr($imagePath, 1);
                }

                // Set absolute URL with production domain
                $item->artwork->full_image_path = $baseUrl . $imagePath;

                // For local development testing, you can embed the image directly
                // This ensures it works even when viewing the email locally
                $localPath = WWW_ROOT . $imagePath;
                if (file_exists($localPath) && is_readable($localPath)) {
                    $contentId = 'artwork' . $index;

                    // Get file contents and encode as base64
                    $fileContent = file_get_contents($localPath);
                    $extension = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
                    $mimeType = 'image/jpeg'; // Default

                    if ($extension === 'png') {
                        $mimeType = 'image/png';
                    } elseif ($extension === 'gif') {
                        $mimeType = 'image/gif';
                    } elseif ($extension === 'svg') {
                        $mimeType = 'image/svg+xml';
                    }

                    // Add as attachment with CID
                    $this->addAttachments([
                        $contentId => [
                            'data' => $fileContent,
                            'mimetype' => $mimeType,
                            'contentId' => $contentId,
                        ],
                    ]);

                    // Store CID reference
                    $item->artwork->embedded_image_cid = 'cid:' . $contentId;
                }
            }
        }
    }

    /**
     * Format shipping address from order data
     *
     * @param \App\Model\Entity\Order $order The order entity
     * @return string Formatted address
     */
    private function _formatShippingAddress(Order $order): string
    {
        try {
            $parts = [];

            if (!empty($order->shipping_address1)) {
                $parts[] = $order->shipping_address1;
            }

            if (!empty($order->shipping_address2)) {
                $parts[] = $order->shipping_address2;
            }

            $locationParts = [];
            if (!empty($order->shipping_suburb)) {
                $locationParts[] = $order->shipping_suburb;
            }

            if (!empty($order->shipping_state)) {
                $locationParts[] = $order->shipping_state;
            }

            if (!empty($order->shipping_postcode)) {
                $locationParts[] = $order->shipping_postcode;
            }

            if (!empty($locationParts)) {
                $parts[] = implode(', ', $locationParts);
            }

            if (!empty($order->shipping_country)) {
                $parts[] = $order->shipping_country;
            }

            return implode("\n", $parts);
        } catch (Exception $e) {
            Log::error('Error formatting address: ' . $e->getMessage());

            return 'Address formatting error';
        }
    }
}
