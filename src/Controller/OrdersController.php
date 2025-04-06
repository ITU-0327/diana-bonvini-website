<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

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

        $conditions = $userId !== null
            ? ['user_id' => $userId]
            : ['session_id' => $sessionId];

        /** @var \App\Model\Entity\Cart $cart */
        $cart = $this->fetchTable('Carts')->find()
            ->contain(['ArtworkCarts' => ['Artworks']])
            ->where($conditions)
            ->first();

        if (!$cart) {
            $this->Flash->error('No items in your cart.');
            return $this->redirect(['controller' => 'Carts', 'action' => 'index']);
        }

        $total = 0.0;
        foreach ($cart->artwork_carts as $item) {
            if (isset($item->artwork)) {
                $total += $item->artwork->price * (float)$item->quantity;
            }
        }

        $order = $this->Orders->newEmptyEntity();
        $this->set(compact('cart', 'total', 'order', 'user'));
        return null;
    }

    /**
     * Place Order method
     *
     * Processes the order and saves it to the database.
     *
     * @return \Cake\Http\Response|null Redirects to the confirmation page.
     */
    public function placeOrder(): ?Response
    {
        $this->request->allowMethod(['post']);
        $data = $this->request->getData();

        if ($this->Authentication->getIdentity()) {
            /** @var \App\Model\Entity\User $user */
            $user = $this->Authentication->getIdentity();
            $data['user_id'] = $user->user_id;
        }

        // Provide a default shipping_state if missing.
        if (empty($data['shipping_state'])) {
            $data['shipping_state'] = 'NSW';
        }
        // Mark is_deleted as 0 by default.
        $data['is_deleted'] = '0';

        // Retrieve the cart and ensure it's not empty.
        /** @var \App\Model\Entity\Cart $cart */
        $cart = $this->fetchTable('Carts')->find()
            ->contain(['ArtworkCarts' => ['Artworks']])
            ->where(['user_id' => $data['user_id']])
            ->first();

        if (!$cart || empty($cart->artwork_carts)) {
            $this->Flash->error(__('Your cart is empty.'));
            return $this->redirect(['controller' => 'Carts', 'action' => 'index']);
        }

        // Build Artwork Orders
        $total = 0.0;
        $orderItems = [];
        foreach ($cart->artwork_carts as $cartItem) {
            if (isset($cartItem->artwork)) {
                $quantity   = $cartItem->quantity;
                $price      = $cartItem->artwork->price;
                $lineTotal  = (float)$quantity * $price;
                $total     += $lineTotal;
                $orderItems[] = [
                    'artwork_id' => $cartItem->artwork->artwork_id,
                    'quantity'   => $quantity,
                    'price'      => $price,
                    'subtotal'   => $lineTotal,
                ];
            }
        }

        // Prepare order data
        $data['total_amount']   = (string)$total;
        $data['artwork_orders'] = $orderItems;
        $data['order_status']   = 'pending';
        $data['order_date']     = date('Y-m-d H:i:s');

        // Create and patch order entity
        $order = $this->Orders->newEntity($data, [
            'associated' => ['ArtworkOrders'],
        ]);

        // Begin transaction and attempt save
        $connection = $this->Orders->getConnection();
        $connection->begin();
        if ($this->Orders->save($order, ['associated' => ['ArtworkOrders']])) {
            // Remove the cart now that we've placed an order
            $this->fetchTable('Carts')->delete($cart);

            // Create payment
            $paymentsTable = $this->fetchTable('Payments');
            $paymentData = [
                'order_id'       => $order->order_id,
                'amount'         => $order->total_amount,
                'payment_date'   => date('Y-m-d H:i:s'),
                'payment_method' => 'bank transfer',
                'status'         => 'pending',
                'is_deleted'     => '0'
            ];
            $payment = $paymentsTable->newEntity($paymentData);
            if (!$paymentsTable->save($payment)) {
                // Roll back and show payment error
                $connection->rollback();
                $this->Flash->error(__('There was an issue with the payment. Please try again.'));
                return $this->redirect(['action' => 'checkout']);
            }

            // Commit and confirm success
            $connection->commit();
            $this->Flash->success(__('Your order has been placed successfully.'));
            return $this->redirect(['action' => 'confirmation', $order->order_id]);
        } else {
            // Build a more user-friendly set of messages
            $friendlyErrors = $this->buildFriendlyErrorMessage($order);
            $connection->rollback();
            $this->Flash->error($friendlyErrors);
            return $this->redirect(['action' => 'checkout']);
        }
    }


    /**
     * Confirmation method
     *
     * Displays the order confirmation page.
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
            ->contain(['ArtworkOrders' => ['Artworks'], 'Payment'])
            ->where(['Orders.order_id' => $orderId])
            ->first();

        $this->set(compact('order'));
        return null;
    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $query = $this->Orders->find()
            ->contain(['Users', 'Payment']);
        $orders = $this->paginate($query);
        $this->set(compact('orders'));
    }

    /**
     * View method
     *
     * @param string|null $id Order id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $order = $this->Orders->get($id, ['contain' => ['Users', 'Payment']]);
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
        $order = $this->Orders->get($id, ['contain' => []]);
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
     * Build a short, user-friendly error message from the entity validation errors.
     *
     * @param \App\Model\Entity\Order $order The order entity with errors.
     * @return string A single string with simplified error messages.
     */
    protected function buildFriendlyErrorMessage(\App\Model\Entity\Order $order): string
    {
        // If the entity has no errors, return a generic message
        if (!$order->hasErrors()) {
            return __('There was an error placing your order. Please try again.');
        }

        $messages = [];
        $errors = $order->getErrors();

        // Map known fields to simpler messages
        $fieldMap = [
            'shipping_postcode' => 'Invalid shipping postcode. Please enter up to 5 digits.',
            'shipping_phone'    => 'Invalid phone number. Please enter up to 15 digits.',
            'billing_first_name'=> 'First name is required.',
            'billing_last_name' => 'Last name is required.',
            // etc...
        ];

        // Collect error messages for known fields
        foreach ($errors as $field => $fieldErrors) {
            if (isset($fieldMap[$field])) {
                $messages[] = $fieldMap[$field];
            } else {
                // For unknown fields, just show the default error text
                $messages = array_merge($messages, array_values($fieldErrors));
            }
        }

        // Check for ArtworkOrder errors
        if (!empty($order->artwork_orders)) {
            foreach ($order->artwork_orders as $index => $artworkOrder) {
                if ($artworkOrder->hasErrors()) {
                    $messages[] = "There's an issue with item #{$index}.";
                }
            }
        }

        // Combine all messages into a single user-friendly message
        return implode(' ', $messages);
    }

}
