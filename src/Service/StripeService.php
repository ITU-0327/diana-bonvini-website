<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Order;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripeService
{
    /**
     * StripeService constructor.
     *
     * @param string|null $secretKey Optional secret key for Stripe API
     */
    public function __construct(?string $secretKey = null)
    {
        // Automatically set the API key at instantiation
        Stripe::setApiKey($secretKey ?? Configure::read('Stripe.secret'));
    }

    /**
     * Build & return a Stripe Checkout Session URL for a saved Order.
     *
     * @param string $orderId
     * @return string
     * @throws ApiErrorException
     */
    public function createCheckoutUrl(string $orderId): string
    {
        // Load the order with its associated artwork orders and artwork details.
        $ordersTable = TableRegistry::getTableLocator()->get('Orders');
        /** @var Order $order */
        $order = $ordersTable->get($orderId, contain: ['ArtworkOrders' => ['Artworks']]);

        // build line items from the order's artwork_orders
        $lineItems = [];
        foreach ($order->artwork_orders as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'aud',
                    'product_data' => ['name' => $item->artwork->title],
                    'unit_amount' => (int)round($item->price * 100.0),
                ],
                'quantity' => $item->quantity,
            ];
        }

        // assemble parameters
        $params = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems ?: [[
                'price_data' => [
                    'currency' => 'aud',
                    'product_data' => ['name' => 'Order #' . $order->order_id],
                    'unit_amount' => (int)round($order->total_amount * 100.0),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            // On successful payment, redirect to your order confirmation page.
            'success_url' => Router::url(
                ['controller' => 'Orders', 'action' => 'confirmation', $order->order_id],
                true,
            ) . '?session_id={CHECKOUT_SESSION_ID}',
            // On cancellation, redirect to checkout with the order ID
            'cancel_url' => Router::url(
                ['controller' => 'Orders', 'action' => 'checkout', '?' => ['OrderId' => $order->order_id]],
                true,
            ),
        ];

        // create the session
        $session = Session::create($params);

        if (empty($session->url)) {
            throw new RuntimeException('Stripe did not return a checkout URL');
        }

        return $session->url;
    }
}
