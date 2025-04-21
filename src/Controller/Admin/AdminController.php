<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Exception;

/**
 * Admin Controller
 *
 * Main controller for admin dashboard
 */
class AdminController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Use admin layout for all admin actions
        $this->viewBuilder()->setLayout('admin');

        // Load components as needed
        $this->loadComponent('Flash');

        // Check for authentication
        $this->checkAdminAuth();
    }

    /**
     * Dashboard method - Main admin landing page
     *
     * @return \Cake\Http\Response|null
     */

    /**
     * Dashboard method - Main admin landing page
     *
     * @return \Cake\Http\Response|null
     */
    public function dashboard()
    {
        // Set a simple title for the dashboard
        $this->set('title', 'Dashboard Overview');

        // Initialize variables with default values
        $artworksCount = 0;
        $ordersCount = 0;
        $pendingOrdersCount = 0;
        $writingRequestsCount = 0;
        $usersCount = 0;
        $recentOrders = [];
        $recentRequests = [];

        // Try to get statistics using getTableLocator
        try {
            // Users data
            try {
                $usersTable = $this->getTableLocator()->get('Users');
                $usersCount = $usersTable->find()->count();
            } catch (Exception $e) {
                // Table might not exist
            }

            // Artworks data
            try {
                $artworksTable = $this->getTableLocator()->get('Artworks');
                $artworksCount = $artworksTable->find()->count();
            } catch (Exception $e) {
                // Table might not exist
            }

            // Orders data
            try {
                $ordersTable = $this->getTableLocator()->get('Orders');
                $ordersCount = $ordersTable->find()->count();

                try {
                    $pendingOrdersCount = $ordersTable->find()->where(['status' => 'pending'])->count();
                    $recentOrders = $ordersTable->find()
                        ->order(['created' => 'DESC'])
                        ->limit(5)
                        ->all();
                } catch (Exception $e) {
                    // Status field might not exist
                }
            } catch (Exception $e) {
                // Table might not exist
            }

            // Writing Service Requests
            try {
                $writingTable = $this->getTableLocator()->get('WritingServiceRequests');
                $writingRequestsCount = $writingTable->find()->count();
                $recentRequests = $writingTable->find()
                    ->order(['created' => 'DESC'])
                    ->limit(5)
                    ->all();
            } catch (Exception $e) {
                // Table might not exist
            }
        } catch (Exception $e) {
            // Log the error
            $this->log($e->getMessage(), 'error');
        }

        // Pass data to the view
        $this->set(compact(
            'artworksCount',
            'ordersCount',
            'pendingOrdersCount',
            'writingRequestsCount',
            'usersCount',
            'recentOrders',
            'recentRequests',
        ));
    }

    /**
     * Check admin authentication
     *
     * @return \Cake\Http\Response|null
     */
    private function checkAdminAuth()
    {
        // Get the authenticated user
        $user = $this->Authentication->getIdentity();

        // Check if user is logged in and is an admin
        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error('You must be logged in as an administrator to access this area.');

            return $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        }

        return null;
    }
}
