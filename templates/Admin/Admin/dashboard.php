<?php
/**
 * Admin Dashboard View Template
 *
 * @var \App\View\AppView $this
 * @var string $title
 * @var int $artworksCount
 * @var int $ordersCount
 * @var int $pendingOrdersCount
 * @var int $writingRequestsCount
 * @var int $usersCount
 * @var \Cake\Collection\CollectionInterface $recentOrders
 * @var \Cake\Collection\CollectionInterface $recentRequests
 */
?>
<div class="admin-dashboard">
    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Artworks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($artworksCount) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paint-brush fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <?= $this->Html->link(
                        'Manage Artworks',
                        ['prefix' => 'Admin', 'controller' => 'Artworks', 'action' => 'index'],
                        ['class' => 'small text-primary'],
                    ) ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($ordersCount) ?></div>
                            <div class="small text-muted"><?= h($pendingOrdersCount) ?> pending</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <?= $this->Html->link(
                        'View Orders',
                        ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'index'],
                        ['class' => 'small text-success'],
                    ) ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Writing Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($writingRequestsCount) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pen fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <?= $this->Html->link(
                        'View Requests',
                        ['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'index'],
                        ['class' => 'small text-info'],
                    ) ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= h($usersCount) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <?= $this->Html->link(
                        'Manage Users',
                        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
                        ['class' => 'small text-warning'],
                    ) ?>
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
                    <?= $this->Html->link(
                        'View All',
                        ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'index'],
                        ['class' => 'btn btn-sm btn-primary'],
                    ) ?>
                </div>
                <div class="card-body">
                    <?php if (count($recentOrders) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentOrders as $order) : ?>
                                    <tr>
                                        <td><?= $this->Html->link(
                                            $order->id,
                                            ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'view', $order->id],
                                        ) ?></td>
                                        <td><?= h($order->customer_name ?? ($order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'N/A')) ?></td>
                                        <td><?= $order->created ? $order->created->format('M d, Y') : 'N/A' ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'badge-warning',
                                                'processing' => 'badge-info',
                                                'shipped' => 'badge-primary',
                                                'completed' => 'badge-success',
                                                'cancelled' => 'badge-danger',
                                            ];
                                            $status = $order->status ?? 'pending';
                                            $class = $statusClass[$status] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?= $class ?>"><?= ucfirst(h($status)) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <p class="text-center text-muted">No recent orders found</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Writing Service Requests -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">Recent Writing Requests</h6>
                    <?= $this->Html->link(
                        'View All',
                        ['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'index'],
                        ['class' => 'btn btn-sm btn-info'],
                    ) ?>
                </div>
                <div class="card-body">
                    <?php if (count($recentRequests) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentRequests as $request) : ?>
                                    <tr>
                                        <td><?= $this->Html->link(
                                            $request->id,
                                            ['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'view', $request->id],
                                        ) ?></td>
                                        <td><?= h($request->client_name ?? ($request->user ? $request->user->first_name . ' ' . $request->user->last_name : 'N/A')) ?></td>
                                        <td><?= h($request->service_type ?? 'N/A') ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'badge-warning',
                                                'in-progress' => 'badge-info',
                                                'completed' => 'badge-success',
                                                'cancelled' => 'badge-danger',
                                            ];
                                            $status = $request->status ?? 'pending';
                                            $class = $statusClass[$status] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?= $class ?>"><?= ucfirst(h($status)) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <p class="text-center text-muted">No recent writing service requests found</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <?= $this->Html->link(
                                '<i class="fas fa-plus-circle mr-2"></i> Add New Artwork',
                                ['prefix' => 'Admin', 'controller' => 'Artworks', 'action' => 'add'],
                                ['class' => 'btn btn-primary btn-block py-3', 'escape' => false],
                            ) ?>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <?= $this->Html->link(
                                '<i class="fas fa-tasks mr-2"></i> Process Orders',
                                ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'pending'],
                                ['class' => 'btn btn-success btn-block py-3', 'escape' => false],
                            ) ?>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <?= $this->Html->link(
                                '<i class="fas fa-pen mr-2"></i> Writing Requests',
                                ['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'index'],
                                ['class' => 'btn btn-info btn-block py-3', 'escape' => false],
                            ) ?>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <?= $this->Html->link(
                                '<i class="fas fa-file-alt mr-2"></i> Content Blocks',
                                ['prefix' => 'Admin', 'controller' => 'ContentBlocks', 'action' => 'index'],
                                ['class' => 'btn btn-secondary btn-block py-3', 'escape' => false],
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Card border styling */
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
</style>
