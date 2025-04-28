<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\OrdersController as BaseOrdersController;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Exception;

/**
 * Orders Controller (Admin prefix)
 *
 * Provides admin-specific operations for order management.
 * Uses dedicated admin templates.
 */
class OrdersController extends BaseOrdersController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Use admin layout
        $this->viewBuilder()->setLayout('admin');

        // By default, use the Admin/Orders templates for all actions
        $this->viewBuilder()->setTemplatePath('Admin/Orders');
    }

    /**
     * Override the beforeFilter to set authentication requirements
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        // Remove any unauthenticated actions for admin
        $this->Authentication->addUnauthenticatedActions([]);

        // Check for admin user
        $user = $this->Authentication->getIdentity();
        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error('You must be logged in as an administrator to access this area.');

            return $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        }

        return null;
    }

    /**
     * Index method - Shows all orders with admin functionality
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $this->set('title', 'Order Management');

        $query = $this->Orders->find()
            ->contain(['Users', 'ArtworkOrders', 'ArtworkOrders.Artworks'])
            ->order(['Orders.created_at' => 'DESC']);

        $orders = $this->paginate($query);

        // Calculate statistics for dashboard cards using safer methods
        $totalOrders = $this->Orders->find()->count();

        // Calculate total revenue manually
        $allOrders = $this->Orders->find()->all();
        $totalRevenue = 0;
        foreach ($allOrders as $order) {
            $totalRevenue += (float)$order->total_amount;
        }

        // Count pending orders
        $pendingOrders = $this->Orders->find()
            ->where(['order_status' => 'pending'])
            ->count();

        // Calculate average order value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $this->set(compact('orders', 'totalOrders', 'totalRevenue', 'pendingOrders', 'avgOrderValue'));
    }

    /**
     * View method - Shows detailed order information
     *
     * @param string|null $id Order id.
     * @return void Renders view.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        try {
            $order = $this->Orders->get($id, [
                'contain' => [
                    'Users',
                    'ArtworkOrders',
                    'ArtworkOrders.Artworks',
                    'Payments',
                ],
            ]);

            $this->set('title', 'Order Details: #' . $order->order_id);
            $this->set(compact('order'));
        } catch (Exception $e) {
            $this->Flash->error(__('The order could not be found.'));
            $this->redirect(['action' => 'index']);
        }
    }

    /**
     * Edit method - Allows admins to edit order details
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        if (!$id) {
            $this->Flash->error(__('Invalid order ID.'));

            return $this->redirect(['action' => 'index']);
        }

        try {
            $order = $this->Orders->get($id, [
                'contain' => ['Users', 'ArtworkOrders', 'ArtworkOrders.Artworks'],
            ]);

            if ($this->request->is(['patch', 'post', 'put'])) {
                $order = $this->Orders->patchEntity($order, $this->request->getData());

                if ($this->Orders->save($order)) {
                    $this->Flash->success(__('The order has been updated.'));

                    return $this->redirect(['action' => 'view', $id]);
                }

                $this->Flash->error(__('The order could not be updated. Please, try again.'));
            }

            $statuses = [
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'confirmed' => 'Confirmed',
            ];

            $this->set('title', 'Edit Order: #' . $order->order_id);
            $this->set(compact('order', 'statuses'));
        } catch (Exception $e) {
            $this->Flash->error(__('The order could not be found.'));

            return $this->redirect(['action' => 'index']);
        }
    }

    /**
     * Update order status method
     *
     * @return \Cake\Http\Response|null Redirects to index
     */
    public function updateStatus(): ?Response
    {
        $this->request->allowMethod(['post', 'put']);

        $orderId = $this->request->getData('order_id');
        $newStatus = $this->request->getData('status');
        $notes = $this->request->getData('notes');

        if (!$orderId || !$newStatus) {
            $this->Flash->error('Missing required data to update order status.');

            return $this->redirect(['action' => 'index']);
        }

        try {
            $order = $this->Orders->get($orderId);
            $order->order_status = $newStatus;

            if (!empty($notes)) {
                $order->order_notes = $notes; // Use existing field instead of admin_notes
            }

            if ($this->Orders->save($order)) {
                $this->Flash->success('Order status has been updated.');
            } else {
                $this->Flash->error('Unable to update order status.');
            }
        } catch (Exception $e) {
            $this->Flash->error('Order not found or could not be updated.');
        }

        return $this->redirect(['action' => 'index']);
    }
}
