<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\Order;
use Cake\Mailer\Mailer;

/**
 * Order Mailer class for sending order-related emails
 */
class OrderMailer extends Mailer
{
    /**
     * Sends order confirmation email
     *
     * @param \App\Model\Entity\Order $order The order entity
     * @return void
     */
    public function confirmation(Order $order): void
    {
        $customerName = trim("$order->billing_first_name $order->billing_last_name") ?: 'Valued Customer';
        $orderDate = $order->order_date
            ? $order->order_date->format('F j, Y')
            : date('F j, Y');

        $this
            ->setTo($order->billing_email)
            ->setSubject('Your Order Confirmation - Diana Bonvini')
            ->setEmailFormat('html')
            ->setViewVars([
                'order' => $order,
                'customerName' => $customerName,
                'orderDate' => $orderDate,
                'shippingAddress' => $this->_formatShippingAddress($order),
                'deliveryMethod' => 'Standard Shipping',
                'estimatedDelivery' => 'To be determined',
            ])
            ->viewBuilder()
            ->setTemplate('order_confirmation')
            ->setLayout('default');
    }

    /**
     * Format shipping address from order data
     *
     * @param \App\Model\Entity\Order $order The order entity
     * @return string Formatted address
     */
    private function _formatShippingAddress(Order $order): string
    {
        $parts = array_filter([
            $order->shipping_address1,
            $order->shipping_address2,
            implode(', ', array_filter([
                $order->shipping_suburb,
                $order->shipping_state,
                $order->shipping_postcode,
            ])),
            $order->shipping_country,
        ]);

        return implode("\n", $parts);
    }
}
