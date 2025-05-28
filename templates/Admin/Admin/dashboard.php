<?php
/**
 * Admin Dashboard View
 *
 * @var \Cake\View\View $this
 * @var int $artworksCount
 * @var int $ordersCount
 * @var int $processingOrdersCount
 * @var int $completedOrdersCount
 * @var int $usersCount
 * @var int $activeServicesCount
 * @var array $recentOrders
 * @var array $recentRequests
 * @var float $totalRevenueToday
 * @var float $totalRevenueWeek
 * @var float $totalRevenueMonth
 * @var int $lowStockCount
 * @var int $upcomingBookingsCount
 * @var int $pendingQuotesCount
 * @var int $completedServicesCount
 * @var array $monthlyArtworkData
 * @var array $monthlyWritingData
 */

use Cake\Utility\Inflector;

$this->assign('title', 'Dashboard');
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Top Stats Cards -->
    <div class="row">
        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Monthly Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($totalRevenueMonth, 2) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($ordersCount) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Services Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Active Services</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($activeServicesCount ?? 0) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pen-fancy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Registered Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($usersCount) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Overview</h6>
                </div>
                <div class="card-body">
                    <div class="revenue-summary mb-4">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Today</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($totalRevenueToday, 2) ?></div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">This Week</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($totalRevenueWeek, 2) ?></div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">This Month</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">$<?= number_format($totalRevenueMonth, 2) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="chart-area">
                        <canvas id="revenueChart" style="min-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Business Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <?= $this->Html->image('logo.png', [
                            'alt' => 'Logo',
                            'class' => 'rounded-circle d-block mx-auto mb-3',
                            'style' => 'width: 60px; height: 60px;',
                        ]) ?>
                        <h5 class="mb-3">Diana Bonvini Art</h5>
                    </div>

                    <!-- Orders Stats -->
                    <div class="mt-4 mb-3">
                        <h6 class="font-weight-bold">Orders</h6>

                        <div class="progress-wrapper">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small">Processing</span>
                                <span class="small font-weight-bold"><?= h($processingOrdersCount) ?></span>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $ordersCount > 0 ? ($processingOrdersCount / $ordersCount * 100) : 0 ?>%" aria-valuenow="<?= h($processingOrdersCount) ?>" aria-valuemin="0" aria-valuemax="<?= h($ordersCount) ?>"></div>
                            </div>
                        </div>

                        <div class="progress-wrapper">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small">Completed</span>
                                <span class="small font-weight-bold"><?= h($completedOrdersCount) ?></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $ordersCount > 0 ? ($completedOrdersCount / $ordersCount * 100) : 0 ?>%" aria-valuenow="<?= h($completedOrdersCount) ?>" aria-valuemin="0" aria-valuemax="<?= h($ordersCount) ?>"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Stats -->
                    <div class="mt-4 mb-3">
                        <h6 class="font-weight-bold">Products</h6>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="circle-stat d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; border-radius: 50%; background-color: rgba(42, 157, 143, 0.1);">
                                        <div>
                                            <div class="h4 mb-0 font-weight-bold text-primary"><?= h($artworksCount) ?></div>
                                            <div class="small text-gray-600">Total</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="circle-stat d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; border-radius: 50%; background-color: rgba(231, 111, 81, 0.1);">
                                        <div>
                                            <div class="h4 mb-0 font-weight-bold text-danger"><?= h($lowStockCount) ?></div>
                                            <div class="small text-gray-600">Low Stock</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Writing Services Stats -->
                    <div class="mt-4">
                        <h6 class="font-weight-bold">Writing Services</h6>
                        <div class="row mt-3">
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="h4 mb-0 font-weight-bold text-warning"><?= h($pendingQuotesCount) ?></div>
                                    <div class="small text-gray-600">Quotes</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="h4 mb-0 font-weight-bold text-info"><?= h($upcomingBookingsCount) ?></div>
                                    <div class="small text-gray-600">Bookings</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <div class="h4 mb-0 font-weight-bold text-success"><?= h($completedServicesCount) ?></div>
                                    <div class="small text-gray-600">Completed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                    <a href="<?= $this->Url->build(['controller' => 'Orders', 'action' => 'index']) ?>" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (count($recentOrders) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order) :
                                        /** @var \App\Model\Entity\Order $order **/
                                        ?>
                                        <tr>
                                            <td>
                                                <?= $this->Html->link(
                                                    '#' . h($order->order_id),
                                                    ['controller' => 'Orders', 'action' => 'view', $order->order_id],
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= h($order->user->first_name . ' ' . $order->user->last_name) ?>
                                            </td>
                                            <td>$<?= number_format($order->total_amount, 2) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match ($order->order_status) {
                                                    'pending' => 'info',
                                                    'confirmed' => 'primary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                }; ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= h(Inflector::humanize($order->order_status)) ?>
                                                </span>
                                            </td>
                                            <td><?= $order->created_at->format('M d, Y') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-shopping-cart fa-3x text-gray-300"></i>
                            </div>
                            <p class="text-gray-600 mb-0">No orders found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Writing Service Requests -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Service Requests</h6>
                    <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'index']) ?>" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (count($recentRequests) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Request #</th>
                                        <th>Client</th>
                                        <th>Service Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentRequests as $request) :
                                        /** @var \App\Model\Entity\WritingServiceRequest $request **/
                                        ?>
                                        <tr>
                                            <td>
                                                <?= $this->Html->link(
                                                    '#' . h($request->writing_service_request_id),
                                                    ['controller' => 'WritingServiceRequests', 'action' => 'view', $request->writing_service_request_id],
                                                ) ?>
                                            </td>
                                            <td>
                                                <?= h($request->user->first_name . ' ' . $request->user->last_name) ?>
                                            </td>
                                            <td><?= h(Inflector::humanize($request->service_type)) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match ($request->request_status) {
                                                    'pending' => 'warning',
                                                    'in_progress' => 'primary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                }; ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= h(Inflector::humanize($request->request_status)) ?>
                                                </span>
                                            </td>
                                            <td><?= $request->created_at->format('M d, Y') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-pen fa-3x text-gray-300"></i>
                            </div>
                            <p class="text-gray-600 mb-0">No service requests found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');

        // Chart labels for months
        const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        // Data provided by the controller
        const artworkSalesData = <?= json_encode($monthlyArtworkData) ?>;
        const writingServicesData = <?= json_encode($monthlyWritingData) ?>;

        const revenueData = {
            labels: monthLabels,
            datasets: [
                {
                    label: 'Artwork Sales',
                    data: artworkSalesData,
                    backgroundColor: 'rgba(42, 157, 143, 0.1)',
                    borderColor: '#2A9D8F',
                    pointBackgroundColor: '#2A9D8F',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#2A9D8F',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Writing Services',
                    data: writingServicesData,
                    backgroundColor: 'rgba(231, 111, 81, 0.1)',
                    borderColor: '#E76F51',
                    pointBackgroundColor: '#E76F51',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#E76F51',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }
            ]
        };

        new Chart(ctx, {
            type: 'line',
            data: revenueData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // Include a dollar sign in the ticks
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
