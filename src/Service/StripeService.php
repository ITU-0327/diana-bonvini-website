<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

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
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCheckoutUrl(string $orderId): string
    {
        // Load the order with its associated artwork orders and artwork details.
        $ordersTable = TableRegistry::getTableLocator()->get('Orders');
        /** @var \App\Model\Entity\Order $order */
        $order = $ordersTable->get($orderId, contain: [
            'ArtworkVariantOrders' => [
                'ArtworkVariants' => ['Artworks'],
            ],
        ]);

        // build line items from the order's artwork_orders
        $lineItems = [];
        foreach ($order->artwork_variant_orders as $item) {
            $artwork = $item->artwork_variant->artwork;
            $dimension = $item->artwork_variant->dimension;
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'aud',
                    'product_data' => [
                        'name' => "$artwork->title ($dimension)",
                        'images' => [ $artwork->image_url ],
                    ],
                    'unit_amount' => (int)round($item->price * 100.0),
                ],
                'quantity' => $item->quantity,
            ];
        }

        // assemble parameters with shipping options
        $params = [
            'payment_method_types' => ['card'],
            'shipping_options' => [
                [
                    'shipping_rate_data' => [
                        'type' => 'fixed_amount',
                        'fixed_amount' => [
                            'amount' => (int)round($order->shipping_cost * 100.0),
                            'currency' => 'aud',
                        ],
                        'display_name' => 'Shipping',
                    ],
                ],
            ],
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

    /**
     * Confirm a Stripe Checkout session and update DB.
     *
     * @param string $orderId
     * @param string $sessionId
     * @return bool  True if we confirmed & updated, false otherwise
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \Exception
     */
    public function confirmCheckout(string $orderId, string $sessionId): bool
    {
        $stripeSession = Session::retrieve($sessionId);
        if ($stripeSession->payment_status !== 'paid') {
            return false;
        }

        // Get the Payment Intent ID from the session - this is what shows in the Stripe dashboard
        $paymentIntentId = $stripeSession->payment_intent;

        $paymentsTable = TableRegistry::getTableLocator()->get('Payments');
        $ordersTable = TableRegistry::getTableLocator()->get('Orders');

        $payment = $paymentsTable->find()
            ->where(['order_id' => $orderId])
            ->first();
        if (!$payment) {
            return false;
        }

        /** @var \Cake\Database\Connection $conn */
        $conn = ConnectionManager::get('default');
        $conn->begin();

        try {
            // patch payment with the actual Payment Intent ID from Stripe
            $payment = $paymentsTable->patchEntity($payment, [
                'status' => 'confirmed',
                'transaction_id' => $paymentIntentId, // This is the actual Payment Intent ID
            ]);
            if (!$paymentsTable->save($payment)) {
                throw new RuntimeException('Could not save payment');
            }

            // patch order
            $order = $ordersTable->get($orderId);
            $order = $ordersTable->patchEntity($order, [
                'order_status' => 'confirmed',
            ]);
            if (!$ordersTable->save($order)) {
                throw new RuntimeException('Could not update order');
            }

            $conn->commit();

            return true;
        } catch (Throwable) {
            $conn->rollback();

            return false;
        }
    }

    /**
     * Fetch payment details from Stripe using Payment Intent ID
     *
     * @param string $paymentIntentId
     * @return \Stripe\PaymentIntent|null
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getPaymentIntentDetails(string $paymentIntentId): ?\Stripe\PaymentIntent
    {
        try {
            return \Stripe\PaymentIntent::retrieve($paymentIntentId);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Log error or handle as needed
            return null;
        }
    }
}
