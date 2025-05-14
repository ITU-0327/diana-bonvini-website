<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use DateTime;

/**
 * Admin Controller
 *
 * Main controller for admin dashboard
 *
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class AdminController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->viewBuilder()->setLayout('admin');
        $this->checkAdminAuth();
    }

    /**
     * Dashboard method - Main admin landing page
     *
     * @return void
     */
    public function dashboard(): void
    {
        // Helper to calculate date ranges
        $today = new DateTime('today');
        $weekStart = new DateTime('monday this week');
        $monthStart = new DateTime('first day of this month');

        // Users data
        $usersTable = $this->getTableLocator()->get('Users');
        $usersCount = $usersTable->find()
            ->where(['user_type' => 'customer'])
            ->count();

        // Artworks data
        $artworksTable = $this->getTableLocator()->get('Artworks');
        $artworksCount = $artworksTable->find()
            ->where(['availability_status' => 'available'])
            ->count();
        // Low stock count (artwork count <= 2)
//         TODO: Add a condition to check if the artwork is available
//                $lowStockCount = $artworksTable->find()
//                    ->where(['quantity <=' => 2, 'availability_status' => 'available'])
//                    ->count();
        $lowStockCount = 0;

        // Orders data
        $ordersTable = $this->getTableLocator()->get('Orders');
        $ordersCount = $ordersTable->find()
            ->count();

        // Orders by status
        $processingOrdersCount = $ordersTable->find()
            ->where(['order_status' => 'confirmed'])
            ->count();
        $completedOrdersCount = $ordersTable->find()
            ->where(['order_status' => 'completed'])
            ->count();

        // Revenue calculations
        /** @var array<\App\Model\Entity\Order> $todayOrders */
        $todayOrders = $ordersTable->find()
            ->where([
                'created_at >=' => $today->format('Y-m-d 00:00:00'),
                'order_status !=' => 'cancelled',
            ]);

        /** @var array<\App\Model\Entity\Order> $weekOrders */
        $weekOrders = $ordersTable->find()
            ->where([
                'created_at >=' => $weekStart->format('Y-m-d 00:00:00'),
                'order_status !=' => 'cancelled',
            ]);

        /** @var array<\App\Model\Entity\Order> $monthOrders */
        $monthOrders = $ordersTable->find()
            ->where([
                'created_at >=' => $monthStart->format('Y-m-d 00:00:00'),
                'order_status !=' => 'cancelled',
            ]);

        // Calculate revenue
        $totalRevenueToday = 0.0;
        foreach ($todayOrders as $order) {
            $totalRevenueToday += $order->total_amount;
        }

        $totalRevenueWeek = 0.0;
        foreach ($weekOrders as $order) {
            $totalRevenueWeek += $order->total_amount;
        }

        $totalRevenueMonth = 0.0;
        foreach ($monthOrders as $order) {
            $totalRevenueMonth += $order->total_amount;
        }

        // Recent orders
        /** @var array<\App\Model\Entity\Order> $recentOrders */
        $recentOrders = $ordersTable->find()
            ->contain(['Users'])
            ->orderBy(['Orders.created_at' => 'DESC'])
            ->limit(5)
            ->all();

        // Writing Service Requests
        $writingTable = $this->getTableLocator()->get('WritingServiceRequests');

        // Service counts by status
        $pendingQuotesCount = $writingTable->find()
            ->where(['request_status' => 'pending'])
            ->count();

        $upcomingBookingsCount = $writingTable->find()
            ->where(['request_status' => 'in_progress'])
            ->count();

        $completedServicesCount = $writingTable->find()
            ->where(['request_status' => 'completed'])
            ->count();

        // Count active services
        $activeServicesCount = $pendingQuotesCount + $upcomingBookingsCount;

        // Recent requests
        $recentRequests = $writingTable->find()
            ->contain(['Users'])
            ->orderBy(['WritingServiceRequests.created_at' => 'DESC'])
            ->limit(5)
            ->all();

        // Monthly breakdown for last 12 months
        $firstOfPeriod = (new DateTime('first day of this month'))->modify('-11 months');
        $ordersMonthly = $ordersTable->find()
            ->select([
                'month' => $ordersTable->find()->func()->extract('MONTH', 'created_at', ['identifier' => true]),
                'revenue' => $ordersTable->find()->func()->sum('total_amount'),
            ])
            ->where([
                'created_at >=' => $firstOfPeriod->format('Y-m-01 00:00:00'),
                'order_status !=' => 'cancelled',
            ])
            ->groupBy(['month'])
            ->enableHydration(false)
            ->toArray();

        $writingMonthly = $writingTable->find()
            ->select([
                'month' => $writingTable->find()->func()->extract('MONTH', 'created_at', ['identifier' => true]),
                'revenue' => $writingTable->find()->func()->sum('final_price'),
            ])
            ->where([
                'created_at >=' => $firstOfPeriod->format('Y-m-01 00:00:00'),
                'request_status !=' => 'cancelled',
            ])
            ->groupBy(['month'])
            ->enableHydration(false)
            ->toArray();

        $monthlyArtworkData = array_fill(0, 12, 0.0);
        $monthlyWritingData = array_fill(0, 12, 0.0);
        foreach ($ordersMonthly as $row) {
            $monthlyArtworkData[$row['month'] - 1] = (float)$row['revenue'];
        }
        foreach ($writingMonthly as $row) {
            $monthlyWritingData[$row['month'] - 1] = (float)$row['revenue'];
        }

        // Pass data to the view
        $this->set(compact(
            'artworksCount',
            'ordersCount',
            'processingOrdersCount',
            'completedOrdersCount',
            'usersCount',
            'activeServicesCount',
            'recentOrders',
            'recentRequests',
            'totalRevenueToday',
            'totalRevenueWeek',
            'totalRevenueMonth',
            'lowStockCount',
            'upcomingBookingsCount',
            'pendingQuotesCount',
            'completedServicesCount',
            'monthlyArtworkData',
            'monthlyWritingData',
        ));
    }

    /**
     * Check admin authentication
     *
     * @return void
     */
    private function checkAdminAuth(): void
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error('You must be logged in as an administrator to access this area.');

            $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        }
    }
}
