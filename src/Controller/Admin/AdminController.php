<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use DateTime;
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
    public function dashboard()
    {
        // Set a title for the dashboard
        $this->set('title', 'Dashboard');

        // Initialize variables with default values
        $artworksCount = 0;
        $ordersCount = 0;
        $pendingOrdersCount = 0;
        $processingOrdersCount = 0;
        $completedOrdersCount = 0;
        $writingRequestsCount = 0;
        $usersCount = 0;
        $adminCount = 0;
        $customerCount = 0;
        $recentOrders = [];
        $recentRequests = [];
        $recentUsers = [];
        $totalRevenueToday = 0;
        $totalRevenueWeek = 0;
        $totalRevenueMonth = 0;
        $lowStockCount = 0;
        $pendingApprovalCount = 0;
        $upcomingBookingsCount = 0;
        $pendingQuotesCount = 0;
        $completedServicesCount = 0;

        // Helper to calculate date ranges
        $today = new DateTime('today');
        $weekStart = new DateTime('monday this week');
        $monthStart = new DateTime('first day of this month');

        // Try to get statistics using getTableLocator
        try {
            // Users data
            try {
                $usersTable = $this->getTableLocator()->get('Users');
                $usersCount = $usersTable->find()->count();

                // Count by user type
                $adminCount = $usersTable->find()->where(['user_type' => 'admin'])->count();
                $customerCount = $usersTable->find()->where(['user_type' => 'customer'])->count();

                // Recent users
                $recentUsers = $usersTable->find()
                    ->order(['created' => 'DESC'])
                    ->limit(5)
                    ->all();
            } catch (Exception $e) {
                // Table might not exist
                $this->log($e->getMessage(), 'error');
            }

            // Artworks data
            try {
                $artworksTable = $this->getTableLocator()->get('Artworks');
                $artworksCount = $artworksTable->find()->count();

                // Low stock count (artwork count <= 2)
                $lowStockCount = $artworksTable->find()
                    ->where(['quantity <=' => 2, 'availability_status' => 'available'])
                    ->count();

                // Pending approval count - artworks that need admin review
                $pendingApprovalCount = $artworksTable->find()
                    ->where(['approval_status' => 'pending'])
                    ->count();
            } catch (Exception $e) {
                // Table might not exist or fields don't exist
                $this->log($e->getMessage(), 'error');
            }

            // Orders data
            try {
                $ordersTable = $this->getTableLocator()->get('Orders');
                $ordersCount = $ordersTable->find()->count();

                try {
                    // Orders by status
                    $pendingOrdersCount = $ordersTable->find()->where(['status' => 'pending'])->count();
                    $processingOrdersCount = $ordersTable->find()->where(['status' => 'processing'])->count();
                    $completedOrdersCount = $ordersTable->find()->where(['status' => 'completed'])->count();

                    // Revenue calculations
                    $todayOrders = $ordersTable->find()
                        ->where([
                            'created >=' => $today->format('Y-m-d 00:00:00'),
                            'status !=' => 'cancelled',
                        ]);

                    $weekOrders = $ordersTable->find()
                        ->where([
                            'created >=' => $weekStart->format('Y-m-d 00:00:00'),
                            'status !=' => 'cancelled',
                        ]);

                    $monthOrders = $ordersTable->find()
                        ->where([
                            'created >=' => $monthStart->format('Y-m-d 00:00:00'),
                            'status !=' => 'cancelled',
                        ]);

                    // Calculate revenue
                    foreach ($todayOrders as $order) {
                        $totalRevenueToday += $order->total_amount;
                    }

                    foreach ($weekOrders as $order) {
                        $totalRevenueWeek += $order->total_amount;
                    }

                    foreach ($monthOrders as $order) {
                        $totalRevenueMonth += $order->total_amount;
                    }

                    // Recent orders
                    $recentOrders = $ordersTable->find()
                        ->contain(['Users'])
                        ->order(['Orders.created' => 'DESC'])
                        ->limit(5)
                        ->all();
                } catch (Exception $e) {
                    // Status field might not exist
                    $this->log($e->getMessage(), 'error');
                }
            } catch (Exception $e) {
                // Table might not exist
                $this->log($e->getMessage(), 'error');
            }

            // Writing Service Requests
            try {
                $writingTable = $this->getTableLocator()->get('WritingServiceRequests');
                $writingRequestsCount = $writingTable->find()->count();

                // Service counts by status
                $upcomingBookingsCount = $writingTable->find()
                    ->where(['status' => 'scheduled'])
                    ->count();

                $pendingQuotesCount = $writingTable->find()
                    ->where(['status' => 'pending_quote'])
                    ->count();

                $completedServicesCount = $writingTable->find()
                    ->where(['status' => 'completed'])
                    ->count();

                // Recent requests
                $recentRequests = $writingTable->find()
                    ->contain(['Users'])
                    ->order(['WritingServiceRequests.created' => 'DESC'])
                    ->limit(5)
                    ->all();
            } catch (Exception $e) {
                // Table might not exist
                $this->log($e->getMessage(), 'error');
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
            'processingOrdersCount',
            'completedOrdersCount',
            'writingRequestsCount',
            'usersCount',
            'adminCount',
            'customerCount',
            'recentOrders',
            'recentRequests',
            'recentUsers',
            'totalRevenueToday',
            'totalRevenueWeek',
            'totalRevenueMonth',
            'lowStockCount',
            'pendingApprovalCount',
            'upcomingBookingsCount',
            'pendingQuotesCount',
            'completedServicesCount',
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
