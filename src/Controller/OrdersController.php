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
     * Place Order method
     *
     * Processes the order and saves it to the database.
     *
     * @return \Cake\Http\Response|null Redirects to the confirmation page.
     */
    public function placeOrder(): ?Response
    {
        $this->request->allowMethod(['post']);

        // Build the base order data from the request.
        $data = $this->request->getData();
        if ($this->Authentication->getIdentity()) {
            /** @var \App\Model\Entity\User $user */
            $user = $this->Authentication->getIdentity();
            $data['user_id'] = $user->user_id;
        }

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

        // Complete the order data.
        $data['total_amount']   = (string)$total;
        $data['artwork_orders'] = $orderItems;
        $data['order_status']   = 'pending';
        $data['order_date']     = date('Y-m-d H:i:s');

        // Patch the entity including associated data.
        $order = $this->Orders->newEntity($data, [
            'associated' => ['ArtworkOrders'],
        ]);

        // Begin transaction and save.
        $connection = $this->Orders->getConnection();
        $connection->begin();
        if ($this->Orders->save($order, ['associated' => ['ArtworkOrders']])) {
            // Optionally clear the cart.
            $this->fetchTable('Carts')->delete($cart);

            // Create a payment record.
            $paymentsTable = $this->fetchTable('Payments');
            $paymentData = [
                'order_id'       => $order->order_id,
                'amount'         => $order->total_amount,
                'payment_date'   => date('Y-m-d H:i:s'),
                'payment_method' => 'bank transfer',
                'status'         => 'pending',
            ];
            $payment = $paymentsTable->newEntity($paymentData);
            if (!$paymentsTable->save($payment)) {
                $connection->rollback();
                $this->Flash->error(__('There was an error placing your order. Please try again. (Payment)'));

                return $this->redirect(['action' => 'checkout']);
            }

            $connection->commit();
            $this->Flash->success(__('Your order has been placed successfully.'));

            return $this->redirect(['action' => 'confirmation', $order->order_id]);
        } else {
            $connection->rollback();
            $this->Flash->error(__('There was an error placing your order. Please try again.'));

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

        $order = $this->Orders->get($orderId, [
            'contain' => [
                'ArtworkOrders' => ['Artworks'],
                'Payments',
            ],
        ]);

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
            ->contain(['Users']);
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
        $order = $this->Orders->get($id, contain: ['Users']);
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
        $order = $this->Orders->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $order = $this->Orders->patchEntity($order, $this->request->getData());
            if ($this->Orders->save($order)) {
                $this->Flash->success(__('The order has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The order could not be saved. Please, try again.'));
        }
        $users = $this->Orders->Users->find('list', limit: 200)->all();
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
