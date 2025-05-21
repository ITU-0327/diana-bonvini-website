<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\OrderMailer;
use App\Model\Entity\ArtworkVariantCart;
use App\Model\Entity\ArtworkVariantOrder;
use App\Model\Entity\Cart;
use App\Model\Entity\Order;
use App\Service\ShippingService;
use App\Service\StripeService;
use Cake\Http\Response;
use Exception;

/**
 * Orders Controller
 *
 * @property \App\Model\Table\OrdersTable $Orders
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class OrdersController extends AppController
{
    /**
     * Checkout method
     *
     * Displays the checkout page with cart details and order summary.
     *
     * @return \Cake\Http\Response|null Renders view.
     */
    public function checkout(ShippingService $shippingService): ?Response
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        $cart = $this->_getCart();
        if (!$cart) {
            $this->Flash->error('No items in your cart.');

            return $this->redirect(['controller' => 'Carts', 'action' => 'index']);
        }

        $pendingId = $this->request->getQuery('OrderId');
        if ($pendingId) {
            $this->Flash->info(__('Please review your information and try again.'));

            $order = $this->Orders->get(
                $pendingId,
                contain: [
                    'ArtworkVariantOrders' => [
                        'ArtworkVariants' => ['Artworks'],
                    ],
                    'Users',
                ],
            );
            $total = $this->_calculateOrderTotal($order);
        } else {
            $order = $this->Orders->newEmptyEntity();
            $total = $this->_calculateCartTotal($cart);
        }

        // Calculate shipping fee if shipping information is available
        $shippingFee = 0;
        if ($order->shipping_state && $order->shipping_country) {
            $shippingFee = $shippingService->calculateShippingFee(
                $order->shipping_state,
                $order->shipping_country,
            );
        }

        $this->set(compact('cart', 'total', 'order', 'user', 'pendingId', 'shippingFee'));

        return null;
    }

    /**
     * Place Order method
     *
     * Processes the order and saves it to the database.
     * After a successful save, creates a Stripe Checkout session and immediately redirects the customer
     * to Stripe's hosted payment page.
     *
     * @return \Cake\Http\Response|null Redirects to Stripe's payment page.
     */
    public function placeOrder(StripeService $stripeService, ShippingService $shippingService): ?Response
    {
        $this->request->allowMethod(['post']);

        // Build the base order data from the request.
        $data = $this->request->getData();
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();
        $data['user_id'] = $user->user_id;

        // Calculate shipping fee
        $data['shipping_cost'] = $shippingService->calculateShippingFee(
            $data['shipping_state'],
            $data['shipping_country'],
        );

        // See if we're updating an in-flight order
        $pendingId = !empty($data['order_id']) ? (string)$data['order_id'] : null;
        if ($pendingId) {
            // load the existing pending order (and its artwork_orders)
            $order = $this->Orders->get(
                $pendingId,
                contain: ['ArtworkVariantOrders' => ['ArtworkVariants']],
            );
            // Remove existing artwork orders to prevent duplication on re-checkout
            $this->Orders->ArtworkVariantOrders->deleteAll(['order_id' => $order->order_id]);
        } else {
            // brand-new checkout
            $order = $this->Orders->newEmptyEntity();
        }

        // Get the current user's cart.
        $cart = $this->_getCart();

        if (!$cart || empty($cart->artwork_variant_carts)) {
            $this->Flash->error(__('Your cart is empty.'));

            return $this->redirect(['controller' => 'Carts', 'action' => 'index']);
        }

        // Build artwork orders from cart items.
        $total = $this->_calculateCartTotal($cart);
        $orderItems = [];
        foreach ($cart->artwork_variant_carts as $cartItem) {
            $variant = $cartItem->artwork_variant;
            $orderItems[] = [
                'artwork_variant_id' => $variant->artwork_variant_id,
                'quantity' => $cartItem->quantity,
                'price' => $variant->price,
                'subtotal' => $variant->price * (float)$cartItem->quantity,
            ];
        }

        // Complete the order data.
        $data['total_amount'] = $total + $data['shipping_cost'];
        $data['artwork_variant_orders'] = $orderItems;
        $data['order_status'] = 'pending';
        $data['order_date'] = date('Y-m-d H:i:s');

        // Patch the order entity including associated artwork orders.
        $order = $this->Orders->patchEntity(
            $order,
            $data,
            ['associated' => ['ArtworkVariantOrders']],
        );

        // Begin a transaction and save the order.
        $connection = $this->Orders->getConnection();
        $connection->begin();

        if ($this->Orders->save($order, ['associated' => ['ArtworkVariantOrders']])) {
            // Create a payment record.
            $paymentsTable = $this->fetchTable('Payments');
            $existingPayment = $paymentsTable->find()
                ->where(['order_id' => $order->order_id])
                ->first();

            $paymentData = [
                'order_id' => $order->order_id,
                'amount' => $order->total_amount,
                'payment_date' => date('Y-m-d H:i:s'),
                'payment_method' => 'stripe',
                'status' => 'pending',
            ];

            $payment = $existingPayment
                ? $paymentsTable->patchEntity($existingPayment, $paymentData)
                : $paymentsTable->newEntity($paymentData);

            if (!$paymentsTable->save($payment)) {
                $connection->rollback();
                $this->Flash->error(__('There was an error processing your payment. Please try again.'));
                $this->set(compact('order', 'cart', 'user'));

                return $this->render('checkout');
            }

            // Important: Don't delete the cart here!
            // We'll use the success webhook or confirmation to delete the cart after payment is confirmed
            $connection->commit();

            try {
                return $this->redirect($stripeService->createCheckoutUrl($order->order_id));
            } catch (Exception $e) {
                $this->Flash->error(__('Payment processor error: ') . $e->getMessage());
                $this->set(compact('order', 'cart', 'user'));

                return $this->render('checkout');
            }
        }

        $connection->rollback();
        $this->Flash->error(__('There were errors in your order submission. Please correct them and try again.'));
        $this->set(compact('order', 'cart', 'user'));

        return $this->render('checkout');
    }

    /**
     * Confirmation method
     *
     * Displays the order confirmation page and handles cart cleanup after successful payment.
     * Also sends a confirmation email when payment is successful.
     *
     * @param string $orderId Order id.
     * @return \Cake\Http\Response|null Renders view.
     * @throws \Exception
     */
    public function confirmation(string $orderId, StripeService $stripeService): ?Response
    {
        // Load the order with complete artwork variant data
        $order = $this->Orders->get($orderId, contain: [
            'ArtworkVariantOrders' => [
                'ArtworkVariants' => ['Artworks'],
            ],
            'Payments',
        ]);

        // If Stripe just redirected back with a session_id, try to confirm payment
        $sessionId = $this->request->getQuery('session_id');
        if ($sessionId && $order->payment->status != 'confirmed') {
            if ($stripeService->confirmCheckout($orderId, $sessionId)) {
                $this->Flash->success(__('Payment confirmed!'));

                // Reload the order with complete artwork variant data
                $order = $this->Orders->get($orderId, contain: [
                    'ArtworkVariantOrders' => [
                        'ArtworkVariants' => ['Artworks'],
                    ],
                    'Payments',
                ]);
            } else {
                $this->Flash->error(__('Could not confirm payment.'));
            }
        }

        if ($order->payment->status == 'confirmed') {
            // Mark any sold-out artworks
            $this->updateArtworkAvailability($order);
            $this->cleanupCart();
            $this->sendConfirmationEmail($order);
        }

        // Check and log artwork variant data for debugging
        if (!empty($order->artwork_variant_orders)) {
            foreach ($order->artwork_variant_orders as $item) {
                if (empty($item->artwork_variant->dimension)) {
                    $this->log('Missing dimension for variant: ' . $item->artwork_variant->artwork_variant_id, 'debug');

                    // Try to fetch the variant directly
                    /** @var \App\Model\Table\ArtworkVariantsTable $variantsTable */
                    $variantsTable = $this->fetchTable('ArtworkVariants');
                    $variant = $variantsTable->get($item->artwork_variant->artwork_variant_id);

                    // Update the dimension if it's missing
                    $item->artwork_variant->dimension = $variant->dimension;
                }
            }
        }

        $this->set(compact('order'));

        return null;
    }

    /**
     * Loop through ordered items and mark artworks as sold if max copies reached.
     *
     * @param \App\Model\Entity\Order $order The order entity with variants loaded.
     * @return void
     */
    private function updateArtworkAvailability(Order $order): void
    {
        $artworksTable = $this->fetchTable('Artworks');
        foreach ($order->artwork_variant_orders as $item) {
            $artwork = $item->artwork_variant->artwork;
            // Count sold quantity across confirmed/completed orders
            $soldCount = (int)$this->Orders->ArtworkVariantOrders->find()
                ->select(['sum' => 'SUM(ArtworkVariantOrders.quantity)'])
                ->matching('ArtworkVariants', function ($q) use ($artwork) {
                    return $q->where(['ArtworkVariants.artwork_id' => $artwork->artwork_id]);
                })
                ->matching('Orders', function ($q) {
                    return $q->where([
                        'Orders.order_status IN' => ['confirmed', 'completed'],
                        'Orders.is_deleted' => false,
                    ]);
                })
                ->first()
                ->get('sum');
            // If sold out and still marked available, update status
            if ($soldCount >= $artwork->max_copies && $artwork->availability_status === 'available') {
                /** @var \App\Model\Entity\Artwork $toUpdate **/
                $toUpdate = $artworksTable->get($artwork->artwork_id);
                $toUpdate->availability_status = 'sold';
                $artworksTable->save($toUpdate);
            }
        }
    }

    /**
     * Index method
     *
     * @return void Renders view.
     */
    public function index(): void
    {
        $query = $this->Orders->find()
            ->contain(['Users', 'Payments']);
        $orders = $this->paginate($query);
        $this->set(compact('orders'));
    }

    /**
     * View method
     *
     * @param string|null $id Order id.
     * @return void Renders view.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $order = $this->Orders->get($id, contain: ['Users', 'Payments']);
        $this->set(compact('order'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $order = $this->Orders->get($id);
        if ($this->Orders->delete($order)) {
            $this->Flash->success(__('The order has been deleted.'));
        } else {
            $this->Flash->error(__('The order could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Get the cart for the current user.
     *
     * @return \App\Model\Entity\Cart|null The cart entity or null if not found.
     */
    private function _getCart(): ?Cart
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        $conditions = $user
            ? ['user_id' => $user->user_id]
            : ['session_id' => $this->request->getSession()->id()];

        return $this->fetchTable('Carts')->find()
            ->contain([
                'ArtworkVariantCarts' => function ($q) {
                    return $q->where(['ArtworkVariantCarts.is_deleted' => false]);
                },
                'ArtworkVariantCarts.ArtworkVariants' => function ($q) {
                    return $q->where(['ArtworkVariants.is_deleted' => false]);
                },
                'ArtworkVariantCarts.ArtworkVariants.Artworks' => function ($q) {
                    return $q->where([
                        'Artworks.is_deleted' => false,
                        'Artworks.availability_status' => 'available',
                    ]);
                },
            ])
            ->where($conditions)
            ->first();
    }

    /**
     * Calculate the total for a Cart entity
     *
     * @param \App\Model\Entity\Cart $cart
     * @return float
     */
    private function _calculateCartTotal(Cart $cart): float
    {
        return array_reduce(
            $cart->artwork_variant_carts,
            /**
             * @param float $sum
             * @param \App\Model\Entity\ArtworkVariantCart $item
             * @return float
             */
            function (float $sum, ArtworkVariantCart $item): float {
                // artwork->price * quantity
                $price = $item->artwork_variant->price ?? 0.0;
                $quantity = (float)$item->quantity;

                return $sum + ($price * $quantity);
            },
            0.0,
        );
    }

    /**
     * Calculate the total for an Order entity
     *
     * @param \App\Model\Entity\Order $order
     * @return float
     */
    private function _calculateOrderTotal(Order $order): float
    {
        return array_reduce(
            $order->artwork_variant_orders,
            /**
             * @param float $sum
             * @param \App\Model\Entity\ArtworkVariantOrder $item
             * @return float
             */
            function (float $sum, ArtworkVariantOrder $item): float {
                $line = $item->subtotal ?? $item->price * (float)$item->quantity;

                return $sum + $line;
            },
            0.0,
        );
    }

    /**
     * Cleans up the cart after a successful order.
     *
     * @throws \Exception
     */
    private function cleanupCart(): void
    {
        $cart = $this->_getCart();
        if (!$cart) {
            return;
        }

        try {
            $cartsTable = $this->fetchTable('Carts');
            $cartsTable->delete($cart);
        } catch (Exception $e) {
            $this->log('Cart delete error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Sends the order confirmation email (if a billing email is present).
     *
     * @param \App\Model\Entity\Order $order
     * @throws \Exception
     */
    private function sendConfirmationEmail(Order $order): void
    {
        $email = $order->billing_email;
        if (!$email) {
            return;
        }

        // Make sure we have all needed data for the email
        if (
            empty($order->artwork_variant_orders) ||
            empty($order->artwork_variant_orders[0]->artwork_variant)
        ) {
            // Reload the order with all related data if not already loaded
            $order = $this->Orders->get($order->order_id, [
                'contain' => [
                    'ArtworkVariantOrders.ArtworkVariants.Artworks',
                    'Payments',
                ],
            ]);
        }

        try {
            $mailer = new OrderMailer('default');
            $mailer->confirmation($order);
            $mailer->deliver();
        } catch (Exception $e) {
            $this->log('Failed to send confirmation email: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * AJAX endpoint to calculate shipping fee based on country and state.
     *
     * @param \App\Service\ShippingService $shippingService
     * @return \Cake\Http\Response JSON response containing the shipping fee
     */
    public function shippingFee(ShippingService $shippingService): Response
    {
        $this->request->allowMethod(['get']);

        $state = $this->request->getQuery('shipping_state');
        $country = $this->request->getQuery('shipping_country');
        $fee = $shippingService->calculateShippingFee($state, $country);
        $payload = (string)json_encode(['shippingFee' => $fee]);

        return $this->response
            ->withType('application/json')
            ->withStringBody($payload);
    }
}
