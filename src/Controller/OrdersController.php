<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Routing\Router;
use Exception;
use Stripe\Checkout\Session;
use Stripe\Stripe;

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
    public function checkout(): ?Response
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        $userId = $user?->user_id;
        $sessionId = $this->request->getSession()->id();

        // Build conditions based on whether the user is logged in or using a session.
        $conditions = $userId !== null
            ? ['user_id' => $userId]
            : ['session_id' => $sessionId];

        // Retrieve the cart with its artwork items.
        /** @var \App\Model\Entity\Cart $cart */
        $cart = $this->fetchTable('Carts')->find()
            ->contain(['ArtworkCarts' => ['Artworks']])
            ->where($conditions)
            ->first();

        if (!$cart) {
            $this->Flash->error('No items in your cart.');

            return $this->redirect(['controller' => 'Carts', 'action' => 'index']);
        }

        // Calculate the total amount.
        $total = 0.0;
        foreach ($cart->artwork_carts as $item) {
            if (isset($item->artwork)) {
                $total += $item->artwork->price * (float)$item->quantity;
            }
        }

        // Create a new Order entity.
        $order = $this->Orders->newEmptyEntity();

        // Pass the cart, total, and order entity to the view.
        $this->set(compact('cart', 'total', 'order', 'user'));

        return null;
    }

    /**
     * Handles return from Stripe payment page when a user cancels.
     * This method serves as a landing point when returning from Stripe.
     *
     * @param string|null $orderId Order id to resume checkout for.
     * @return \Cake\Http\Response|null Redirects or renders view.
     */

    /**
     * Handles return from Stripe payment page when a user cancels.
     * This method serves as a landing point when returning from Stripe.
     *
     * @param string|null $orderId Order id to resume checkout for.
     * @return \Cake\Http\Response|null Redirects or renders view.
     */

    /**
     * Handles return from Stripe payment page when a user cancels.
     * This method serves as a landing point when returning from Stripe.
     *
     * @param string|null $orderId Order id to resume checkout for.
     * @return \Cake\Http\Response|null Redirects or renders view.
     */
    public function resumeCheckout(?string $orderId = null): ?Response
    {
        // If an order ID is provided, try to load that specific order
        if ($orderId) {
            try {
                $order = $this->Orders->get($orderId, [
                    'contain' => [
                        'ArtworkOrders' => ['Artworks'],
                        'Users',
                    ],
                ]);

                // We'll create a fresh order entity for the form submission
                $newOrder = $this->Orders->newEmptyEntity();

                // Pre-populate with billing details from the existing order
                $newOrder->billing_first_name = $order->billing_first_name ?? null;
                $newOrder->billing_last_name = $order->billing_last_name ?? null;
                $newOrder->billing_company = $order->billing_company ?? null;
                $newOrder->billing_email = $order->billing_email ?? null;
                $newOrder->shipping_country = $order->shipping_country ?? null;
                $newOrder->shipping_address1 = $order->shipping_address1 ?? null;
                $newOrder->shipping_address2 = $order->shipping_address2 ?? null;
                $newOrder->shipping_suburb = $order->shipping_suburb ?? null;
                $newOrder->shipping_state = $order->shipping_state ?? null;
                $newOrder->shipping_postcode = $order->shipping_postcode ?? null;
                $newOrder->shipping_phone = $order->shipping_phone ?? null;
                $newOrder->order_notes = $order->order_notes ?? null;

                $this->Flash->info(__('Please review your information and try again.'));

                // Calculate total for the view
                $total = 0.0;
                foreach ($order->artwork_orders as $item) {
                    $total += $item->price * (float)$item->quantity;
                }

                /** @var \App\Model\Entity\User|null $user */
                $user = $this->Authentication->getIdentity();

                // First check if the cart_id is stored in the session
                $cartId = $this->request->getSession()->read('Checkout.cart_id');

                // If we have a cart ID, attempt to load that cart
                $cartsTable = $this->fetchTable('Carts');
                $cart = null;

                if ($cartId) {
                    try {
                        $cart = $cartsTable->find()
                            ->contain(['ArtworkCarts' => ['Artworks']])
                            ->where(['cart_id' => $cartId])
                            ->first();
                    } catch (Exception $e) {
                        // Log the error but continue with cart recreation
                        $this->log('Error loading existing cart: ' . $e->getMessage(), 'error');
                    }
                }

                // If no cart was found or loaded, we'll create a new one with items from the order
                if (!$cart) {
                    // We'll recreate a cart-like structure for the view
                    // First, check if a cart already exists for this user/session
                    $userId = $user?->user_id;
                    $sessionId = $this->request->getSession()->id();

                    // Build conditions based on whether the user is logged in or using a session.
                    $conditions = $userId !== null
                        ? ['user_id' => $userId]
                        : ['session_id' => $sessionId];

                    $cart = $cartsTable->find()
                        ->where($conditions)
                        ->first();

                    // If no cart exists, create a new one with items from the order
                    if (!$cart) {
                        $cart = $cartsTable->newEntity([
                            'user_id' => $userId,
                            'session_id' => $sessionId,
                            'created' => date('Y-m-d H:i:s'),
                            'modified' => date('Y-m-d H:i:s'),
                        ]);

                        if ($cartsTable->save($cart)) {
                            // Store the cart ID in session
                            $this->request->getSession()->write('Checkout.cart_id', $cart->cart_id);

                            // Now add the items from the order to the cart
                            $artworkCartsTable = $this->fetchTable('ArtworkCarts');
                            $cart->artwork_carts = [];

                            foreach ($order->artwork_orders as $item) {
                                $artworkCart = $artworkCartsTable->newEntity([
                                    'cart_id' => $cart->cart_id,
                                    'artwork_id' => $item->artwork_id,
                                    'quantity' => $item->quantity,
                                    'created' => date('Y-m-d H:i:s'),
                                    'modified' => date('Y-m-d H:i:s'),
                                ]);

                                if ($artworkCartsTable->save($artworkCart)) {
                                    $artworkCart->artwork = $item->artwork;
                                    $cart->artwork_carts[] = $artworkCart;
                                }
                            }
                        }
                    } else {
                        // Cart exists but we need to load its artwork items
                        $cart = $cartsTable->get($cart->cart_id, [
                            'contain' => ['ArtworkCarts' => ['Artworks']],
                        ]);

                        // If the cart is empty (no artwork_carts), add the items from the order
                        if (empty($cart->artwork_carts)) {
                            $artworkCartsTable = $this->fetchTable('ArtworkCarts');
                            $cart->artwork_carts = [];

                            foreach ($order->artwork_orders as $item) {
                                $artworkCart = $artworkCartsTable->newEntity([
                                    'cart_id' => $cart->cart_id,
                                    'artwork_id' => $item->artwork_id,
                                    'quantity' => $item->quantity,
                                    'created' => date('Y-m-d H:i:s'),
                                    'modified' => date('Y-m-d H:i:s'),
                                ]);

                                if ($artworkCartsTable->save($artworkCart)) {
                                    $artworkCart->artwork = $item->artwork;
                                    $cart->artwork_carts[] = $artworkCart;
                                }
                            }
                        }
                    }
                }

                // Pass the data to the view
                $this->set(compact('cart', 'total', 'order', 'user', 'newOrder'));

                // Return the checkout view with our recreated data
                return $this->render('checkout');
            } catch (Exception $e) {
                // If we couldn't load the order or there was another issue
                $this->Flash->error(__('There was an error resuming your checkout. Please start again.'));
                $this->log('Error in resumeCheckout: ' . $e->getMessage(), 'error');

                return $this->redirect(['action' => 'checkout']);
            }
        }

        // Otherwise, just redirect to the standard checkout page
        return $this->redirect(['action' => 'checkout']);
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

    /**
     * Place Order method
     *
     * Processes the order and saves it to the database.
     * After a successful save, creates a Stripe Checkout session and immediately redirects the customer
     * to Stripe's hosted payment page.
     *
     * @return \Cake\Http\Response|null Redirects to Stripe's payment page.
     */
    public function placeOrder(): ?Response
    {
        $this->request->allowMethod(['post']);

        // Build the base order data from the request.
        $data = $this->request->getData();
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();
        $data['user_id'] = $user->user_id;

        // Get the current user's cart.
        /** @var \App\Model\Entity\Cart $cart */
        $cart = $this->fetchTable('Carts')->find()
            ->contain(['ArtworkCarts' => ['Artworks']])
            ->where(['user_id' => $data['user_id']])
            ->first();

        if (!$cart || empty($cart->artwork_carts)) {
            $this->Flash->error(__('Your cart is empty.'));

            return $this->redirect(['controller' => 'Carts', 'action' => 'index']);
        }

        // Build artwork orders from cart items.
        $total = 0.0;
        $orderItems = [];
        foreach ($cart->artwork_carts as $cartItem) {
            if (isset($cartItem->artwork)) {
                $quantity  = $cartItem->quantity;
                $price     = $cartItem->artwork->price;
                $lineTotal = (float)$quantity * $price;
                $total    += $lineTotal;
                $orderItems[] = [
                    'artwork_id' => $cartItem->artwork->artwork_id,
                    'quantity'   => $quantity,
                    'price'      => $price,
                    'subtotal'   => $lineTotal,
                ];
            }
        }

        // Complete the order data.
        $data['total_amount']   = (string)$total;
        $data['artwork_orders'] = $orderItems;
        $data['order_status']   = 'pending';
        $data['order_date']     = date('Y-m-d H:i:s');

        // Patch the order entity including associated artwork orders.
        $order = $this->Orders->newEntity($data, [
            'associated' => ['ArtworkOrders'],
        ]);

        // Begin a transaction and save the order.
        $connection = $this->Orders->getConnection();
        $connection->begin();

        if ($this->Orders->save($order, ['associated' => ['ArtworkOrders']])) {
            // Create a payment record.
            $paymentsTable = $this->fetchTable('Payments');
            $paymentData = [
                'order_id'       => $order->order_id,
                'amount'         => $order->total_amount,
                'payment_date'   => date('Y-m-d H:i:s'),
                'payment_method' => 'stripe',
                'status'         => 'pending',
            ];
            $payment = $paymentsTable->newEntity($paymentData);

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
                // Create a Stripe Checkout session and immediately redirect the customer.
                $stripeUrl = $this->_createStripeSessionUrl($order->order_id);
                // Store the cart ID in the session so we can retrieve it if needed
                $this->request->getSession()->write('Checkout.cart_id', $cart->cart_id);

                return $this->redirect($stripeUrl);
            } catch (Exception $e) {
                $this->Flash->error(__('There was an error connecting to our payment processor. Please try again.'));
                $this->set(compact('order', 'cart', 'user'));

                return $this->render('checkout');
            }
        } else {
            $connection->rollback();
            $this->Flash->error(__('There were errors in your order submission. Please correct them and try again.'));
            $this->set(compact('order', 'cart', 'user'));

            return $this->render('checkout');
        }
    }

    /**
     * Private method to create a Stripe Checkout session URL based on the order details.
     *
     * @param string $orderId The ID of the order.
     * @return string The URL of the Stripe Checkout session.
     */
    private function _createStripeSessionUrl(string $orderId): string
    {
        // Load the order with its associated artwork orders and artwork details.
        $ordersTable = $this->getTableLocator()->get('Orders');
        $order = $ordersTable->get($orderId, [
            'contain' => ['ArtworkOrders' => ['Artworks']],
        ]);

        $lineItems = [];
        if (!empty($order->artwork_orders)) {
            foreach ($order->artwork_orders as $artworkOrder) {
                // Convert price in dollars to cents.
                $unitAmount = round((float)$artworkOrder->price * 100);
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'aud',
                        'product_data' => [
                            'name' => $artworkOrder->artwork->title,
                        ],
                        'unit_amount' => $unitAmount,
                    ],
                    'quantity' => $artworkOrder->quantity,
                ];
            }
        } else {
            // Fallback if no artwork orders exist.
            $amount = round((float)$order->total_amount * 100);
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'aud',
                    'product_data' => [
                        'name' => 'Order Payment - Order #' . $orderId,
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ];
        }

        // Set your Stripe secret key.
        Stripe::setApiKey(Configure::read('Stripe.secret'));

        // Create the Stripe Checkout session.
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            // On successful payment, redirect to your order confirmation page.
            'success_url' => Router::url(
                ['controller' => 'Orders', 'action' => 'confirmation', $orderId],
                true,
            ) . '?session_id={CHECKOUT_SESSION_ID}',
            // On cancellation, redirect to resumeCheckout with the order ID
            'cancel_url' => Router::url(
                ['controller' => 'Orders', 'action' => 'resumeCheckout', $orderId],
                true,
            ),
        ]);

        return $session->url;
    }

    /**
     * Confirmation method
     *
     * Displays the order confirmation page and handles cart cleanup after successful payment.
     *
     * @param string|null $orderId Order id.
     * @return \Cake\Http\Response|null Renders view.
     */
    public function confirmation(?string $orderId = null): ?Response
    {
        if (!$orderId) {
            $this->Flash->error(__('Invalid order.'));

            return $this->redirect(['action' => 'index']);
        }

        $order = $this->Orders->find()
            ->contain(['ArtworkOrders' => ['Artworks'], 'Payments'])
            ->where(['Orders.order_id' => $orderId])
            ->first();

        if (!$order) {
            $this->Flash->error(__('Order not found.'));

            return $this->redirect(['action' => 'index']);
        }

        // Check if we have a successful payment
        $hasSuccessfulPayment = false;
        if (!empty($order->payments)) {
            foreach ($order->payments as $payment) {
                if ($payment->status === 'completed' || $payment->status === 'succeeded') {
                    $hasSuccessfulPayment = true;
                    break;
                }
            }
        }

        // If we have a Stripe session ID in the query param, update payment status
        $sessionId = $this->request->getQuery('session_id');
        if ($sessionId && !$hasSuccessfulPayment) {
            try {
                Stripe::setApiKey(Configure::read('Stripe.secret'));
                $session = Session::retrieve($sessionId);

                if ($session && $session->payment_status === 'paid') {
                    // Update payment status
                    $paymentsTable = $this->fetchTable('Payments');
                    $payment = $paymentsTable->find()
                        ->where(['order_id' => $orderId])
                        ->first();

                    if ($payment) {
                        $payment->status = 'completed';
                        $payment->transaction_id = $sessionId;
                        $paymentsTable->save($payment);
                        $hasSuccessfulPayment = true;

                        // Update order status
                        $order->order_status = 'confirmed';
                        $this->Orders->save($order);
                    }
                }
            } catch (Exception $e) {
                // Log the error but continue showing the confirmation page
                $this->log('Stripe session verification error: ' . $e->getMessage(), 'error');
            }
        }

        // Now, clean up the cart if payment was successful
        if ($hasSuccessfulPayment) {
            // Get the cart ID from session if available
            $cartId = $this->request->getSession()->read('Checkout.cart_id');

            if ($cartId) {
                try {
                    $cartsTable = $this->fetchTable('Carts');
                    $cart = $cartsTable->get($cartId);
                    $cartsTable->delete($cart);

                    // Clear the cart ID from session
                    $this->request->getSession()->delete('Checkout.cart_id');
                } catch (Exception $e) {
                    // Just log the error but continue showing the confirmation page
                    $this->log('Cart cleanup error: ' . $e->getMessage(), 'error');
                }
            }

            // Set a success message
            $this->Flash->success(__('Your payment was successful! Your order has been placed.'));
        }

        $this->set(compact('order', 'hasSuccessfulPayment'));

        return null;
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
        $order = $this->Orders->get($id, ['contain' => ['Users', 'Payments']]);
        $this->set(compact('order'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $order = $this->Orders->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $order = $this->Orders->patchEntity($order, $this->request->getData());
            if ($this->Orders->save($order)) {
                $this->Flash->success(__('The order has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The order could not be saved. Please, try again.'));
        }
        $users = $this->Orders->Users->find('list', ['limit' => 200])->all();
        $this->set(compact('order', 'users'));
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
}
