<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Orders Controller
 *
 * @property \App\Model\Table\OrdersTable $Orders
 * @method \App\Model\Entity\Order[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdersController extends AppController
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

        // Load components
        $this->loadComponent('Flash');
        $this->loadComponent('Paginator');

        // Check admin auth
        $this->checkAdminAuth();
    }

    /**
     * Index method - list all orders
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        // Set up pagination
        $this->paginate = [
            'contain' => ['ArtworkOrders.Artworks'], // Assuming that's the association
            'order' => ['Orders.created' => 'DESC'],
            'limit' => 20,
        ];

        $orders = $this->paginate($this->Orders);

        $this->set(compact('orders'));
        $this->set('title', 'All Orders');
    }

    /**
     * View method - displays details for a specific order
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $order = $this->Orders->get($id, [
            'contain' => ['ArtworkOrders.Artworks', 'Payments'], // Assuming these associations exist
        ]);

        $this->set(compact('order'));
        $this->set('title', 'Order Details #' . $order->id);
    }

    /**
     * Add method - create a new order
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $order = $this->Orders->newEmptyEntity();

        if ($this->request->is('post')) {
            $order = $this->Orders->patchEntity($order, $this->request->getData());

            if ($this->Orders->save($order)) {
                $this->Flash->success(__('The order has been saved.'));

                return $this->redirect(['action' => 'view', $order->id]);
            }

            $this->Flash->error(__('The order could not be saved. Please, try again.'));
        }

        // Get data for select options (if needed)
        $artworks = $this->Orders->ArtworkOrders->Artworks->find('list')->all();

        $this->set(compact('order', 'artworks'));
        $this->set('title', 'Create New Order');
    }

    /**
     * Edit method - edit an existing order
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $order = $this->Orders->get($id, [
            'contain' => ['ArtworkOrders.Artworks'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $order = $this->Orders->patchEntity($order, $this->request->getData());

            if ($this->Orders->save($order)) {
                $this->Flash->success(__('The order has been updated.'));

                return $this->redirect(['action' => 'view', $order->id]);
            }

            $this->Flash->error(__('The order could not be updated. Please, try again.'));
        }

        // Get data for select options (if needed)
        $artworks = $this->Orders->ArtworkOrders->Artworks->find('list')->all();

        $this->set(compact('order', 'artworks'));
        $this->set('title', 'Edit Order #' . $order->id);
    }

    /**
     * Delete method - delete an order
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
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
     * Pending method - shows all pending orders
     *
     * @return \Cake\Http\Response|null|void
     */
    public function pending()
    {
        // Set up pagination for pending orders
        $this->paginate = [
            'contain' => ['ArtworkOrders.Artworks'],
            'conditions' => ['Orders.status' => 'pending'],
            'order' => ['Orders.created' => 'DESC'],
            'limit' => 20,
        ];

        $orders = $this->paginate($this->Orders);

        $this->set(compact('orders'));
        $this->set('title', 'Pending Orders');
    }

    /**
     * Processing method - shows all processing orders
     *
     * @return \Cake\Http\Response|null|void
     */
    public function processing()
    {
        // Set up pagination for processing orders
        $this->paginate = [
            'contain' => ['ArtworkOrders.Artworks'],
            'conditions' => ['Orders.status' => 'processing'],
            'order' => ['Orders.created' => 'DESC'],
            'limit' => 20,
        ];

        $orders = $this->paginate($this->Orders);

        $this->set(compact('orders'));
        $this->set('title', 'Processing Orders');
    }

    /**
     * Shipped method - shows all shipped orders
     *
     * @return \Cake\Http\Response|null|void
     */
    public function shipped()
    {
        // Set up pagination for shipped orders
        $this->paginate = [
            'contain' => ['ArtworkOrders.Artworks'],
            'conditions' => ['Orders.status' => 'shipped'],
            'order' => ['Orders.created' => 'DESC'],
            'limit' => 20,
        ];

        $orders = $this->paginate($this->Orders);

        $this->set(compact('orders'));
        $this->set('title', 'Shipped Orders');
    }

    /**
     * Completed method - shows all completed orders
     *
     * @return \Cake\Http\Response|null|void
     */
    public function completed()
    {
        // Set up pagination for completed orders
        $this->paginate = [
            'contain' => ['ArtworkOrders.Artworks'],
            'conditions' => ['Orders.status' => 'completed'],
            'order' => ['Orders.created' => 'DESC'],
            'limit' => 20,
        ];

        $orders = $this->paginate($this->Orders);

        $this->set(compact('orders'));
        $this->set('title', 'Completed Orders');
    }

    /**
     * Mark as Processing - changes an order status to processing
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects to pending orders page.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function markAsProcessing(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        $order = $this->Orders->get($id);

        $order->status = 'processing';

        if ($this->Orders->save($order)) {
            $this->Flash->success(__('Order #{0} has been marked as processing.', $order->id));
        } else {
            $this->Flash->error(__('Failed to update order status. Please try again.'));
        }

        return $this->redirect(['action' => 'pending']);
    }

    /**
     * Mark as Shipped - changes an order status to shipped
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects to processing orders page.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function markAsShipped(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        $order = $this->Orders->get($id);

        $order->status = 'shipped';

        if ($this->Orders->save($order)) {
            $this->Flash->success(__('Order #{0} has been marked as shipped.', $order->id));
        } else {
            $this->Flash->error(__('Failed to update order status. Please try again.'));
        }

        return $this->redirect(['action' => 'processing']);
    }

    /**
     * Mark as Completed - changes an order status to completed
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects to shipped orders page.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function markAsCompleted(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        $order = $this->Orders->get($id);

        $order->status = 'completed';

        if ($this->Orders->save($order)) {
            $this->Flash->success(__('Order #{0} has been marked as completed.', $order->id));
        } else {
            $this->Flash->error(__('Failed to update order status. Please try again.'));
        }

        return $this->redirect(['action' => 'shipped']);
    }

    /**
     * Check admin authentication
     *
     * You should implement proper authentication
     * This is just a placeholder
     *
     * @return \Cake\Http\Response|null
     */
    private function checkAdminAuth()
    {
        // This is a placeholder for authentication
        // In a real application, you would check session, login status, etc.
        // and redirect non-admin users to login page

        // For example:
        /*
        if (!$this->Authentication->getIdentity() || !$this->Authentication->getIdentity()->is_admin) {
            $this->Flash->error('You must be logged in as an administrator to access this area.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        }
        */
    }
}
