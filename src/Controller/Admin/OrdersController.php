<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController as BaseAdminController;
use Cake\Http\Response;
use Exception;

/**
 * Orders Controller (Admin prefix)
 *
 * Provides admin-specific operations for order management.
 * Uses dedicated admin templates.
 *
 * @property \App\Model\Table\OrdersTable $Orders
 */
class OrdersController extends BaseAdminController
{
    /**
     * Index method - Shows all orders with admin functionality
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $this->set('title', 'Order Management');

        $query = $this->Orders->find()
            ->contain(['Users', 'ArtworkVariantOrders', 'ArtworkVariantOrders.ArtworkVariants', 'ArtworkVariantOrders.ArtworkVariants.Artworks'])
            ->orderBy(['Orders.created_at' => 'DESC']);

        $orders = $this->paginate($query);

        // Calculate statistics for dashboard cards using safer methods
        $totalOrders = $this->Orders->find()->count();

        // Calculate total revenue manually
        /** @var array<\App\Model\Entity\Order> $allOrders */
        $allOrders = $this->Orders->find()->all();
        $totalRevenue = 0.0;
        foreach ($allOrders as $order) {
            $totalRevenue += $order->total_amount;
        }

        $this->set(compact('orders', 'totalOrders', 'totalRevenue'));
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
            $order = $this->Orders->get($id, contain: [
                'Users',
                'ArtworkVariantOrders',
                'ArtworkVariantOrders.ArtworkVariants',
                'ArtworkVariantOrders.ArtworkVariants.Artworks',
                'Payments',
            ]);

            $this->set('title', 'Order Details: #' . $order->order_id);
            $this->set(compact('order'));
        } catch (Exception) {
            $this->Flash->error(__('The order could not be found.'));
            $this->redirect(['action' => 'index']);
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
        } catch (Exception) {
            $this->Flash->error('Order not found or could not be updated.');
        }

        return $this->redirect(['action' => 'index']);
    }
}
