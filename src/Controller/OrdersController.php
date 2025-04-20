<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\OrderMailer;
use App\Model\Entity\ArtworkCart;
use App\Model\Entity\ArtworkOrder;
use App\Model\Entity\Cart;
use App\Model\Entity\Order;
use App\Service\StripeService;
use Cake\Core\Configure;
use Cake\Http\Response;
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

        $cart = $this->_getCart();
        if (!$cart) {
            $this->Flash->error('No items in your cart.');

            return $this->redirect(['controller' => 'Carts', 'action' => 'index']);
        }

        $resumeOrderId = $this->request->getQuery('OrderId');
        if ($resumeOrderId) {
            $this->Flash->info(__('Please review your information and try again.'));

            $order = $this->Orders->get(
                $resumeOrderId,
                contain: ['ArtworkOrders' => ['Artworks'], 'Users'],
            );
            $total = $this->_calculateOrderTotal($order);
        } else {
            $order = $this->Orders->newEmptyEntity();
            $total = $this->_calculateCartTotal($cart);
        }

        $this->set(compact('cart', 'total', 'order', 'user'));

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
    public function placeOrder(StripeService $stripeService): ?Response
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
                $url = $stripeService->createCheckoutUrl($order->order_id);
                $this->request->getSession()->write('Checkout.cart_id', $cart->cart_id);

                return $this->redirect($url);
            } catch (Exception $e) {
                $this->Flash->error(__('Payment processor error: ') . $e->getMessage());
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
     * Confirmation method
     *
     * Displays the order confirmation page and handles cart cleanup after successful payment.
     * Also sends a confirmation email when payment is successful.
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

            // Send a confirmation email when payment is successful
            $customerEmail = $order->billing_email ?? null;

            if ($customerEmail) {
                try {
                    // Send order confirmation email
                    $this->sendEmail(
                        OrderMailer::class,
                        'confirmation',
                        [$order],
                        $customerEmail,
                    );
                } catch (Exception $e) {
                    // Log the error but continue with order confirmation
                    $this->log('Failed to send order confirmation email: ' . $e->getMessage(), 'error');
                }
            }
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
            ? ['user_id'    => $user->user_id]
            : ['session_id' => $this->request->getSession()->id()];

        return $this->fetchTable('Carts')->find()
            ->contain(['ArtworkCarts.Artworks'])
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
            $cart->artwork_carts,
            /**
             * @param float $sum
             * @param \App\Model\Entity\ArtworkCart $item
             * @return float
             */
            function (float $sum, ArtworkCart $item): float {
                // artwork->price * quantity
                $price    = $item->artwork->price ?? 0.0;
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
            $order->artwork_orders,
            /**
             * @param float $sum
             * @param \App\Model\Entity\ArtworkOrder $item
             * @return float
             */
            function (float $sum, ArtworkOrder $item): float {
                $line = $item->subtotal ?? $item->price * (float)$item->quantity;

                return $sum + $line;
            },
            0.0,
        );
    }
}
