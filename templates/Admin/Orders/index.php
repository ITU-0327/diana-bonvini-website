<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Order> $orders
 * @var int $totalOrders
 * @var float $totalRevenue
 */
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-shopping-cart mr-2"></i><?= __('Order Management') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Orders') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Stats Cards -->
    <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $totalOrders ?></h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$<?= $this->Number->format($totalRevenue, ['precision' => 2]) ?></h3>
                        <p>Total Revenue</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Filter Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-filter mr-1"></i> Filter Orders
                        </h6>
                        <div class="dropdown">
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv mr-2"></i>CSV</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel mr-2"></i>Excel</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf mr-2"></i>PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <select id="status-filter" class="form-control">
                                    <option value="all">All Status</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <input type="date" id="date-filter" class="form-control" placeholder="Filter by date">
                            </div>
                            <div class="col-md-3 mb-3">
                                <select id="sort-order" class="form-control">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="amount_high">Total (High to Low)</option>
                                    <option value="amount_low">Total (Low to High)</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-input" class="form-control" placeholder="Search orders...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">All Orders</h6>
                    </div>
                    <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) > 0) : ?>
                                <?php foreach ($orders as $order) : ?>
                                <tr class="order-row" data-status="<?= h($order->order_status ?? '') ?>">
                                    <td class="align-middle">#<?= h($order->order_id) ?></td>
                                    <td class="align-middle">
                                        <?php if (isset($order->user) && !empty($order->user->first_name) && !empty($order->user->last_name)) : ?>
                                            <?= h($order->user->first_name . ' ' . $order->user->last_name) ?>
                                        <?php elseif (!empty($order->billing_first_name) && !empty($order->billing_last_name)) : ?>
                                            <?= h($order->billing_first_name . ' ' . $order->billing_last_name) ?>
                                        <?php else : ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (isset($order->created_at) && $order->created_at) : ?>
                                            <?= $order->created_at->format('M d, Y') ?>
                                        <?php elseif (isset($order->order_date) && $order->order_date) : ?>
                                            <?= $order->order_date->format('M d, Y') ?>
                                        <?php else : ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= isset($order->artwork_orders) ? count($order->artwork_orders) : 0 ?></td>
                                    <td class="align-middle">$<?= $this->Number->format($order->total_amount) ?></td>
                                    <td class="align-middle">
                                        <?php
                                        $status = $order->order_status ?? 'confirmed';
                                        $statusClass = match ($status) {
                                            'processing' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            'confirmed' => 'primary',
                                            default => 'secondary'
                                        };
    ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= ucfirst(h($status)) ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="btn-group">
                                            <a href="<?= $this->Url->build(['action' => 'view', $order->order_id]) ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center">No orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?= $this->Paginator->prev('« Previous') ?>
                            <?= $this->Paginator->numbers() ?>
                            <?= $this->Paginator->next('Next »') ?>
                        </ul>
                    </nav>
                    <p class="text-center">
                        <?= $this->Paginator->counter('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total') ?>
                    </p>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, ['url' => ['action' => 'updateStatus'], 'id' => 'updateStatusForm']) ?>
                <?= $this->Form->hidden('order_id', ['id' => 'modal-order-id']) ?>
                <div class="form-group mb-3">
                    <?= $this->Form->label('status', 'Status', ['class' => 'form-label']) ?>
                    <?= $this->Form->select('status', [
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ], [
                        'class' => 'form-select',
                        'required' => true,
                    ]); ?>
                </div>
                <div class="form-group mb-3">
                    <?= $this->Form->label('notes', 'Notes', ['class' => 'form-label']) ?>
                    <?= $this->Form->textarea('notes', [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Add notes about this status change (optional)',
                    ]); ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateStatusSubmit">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            filterOrders();
        });

        // Date filter
        document.getElementById('date-filter').addEventListener('change', function() {
            filterOrders();
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('keyup', function() {
            filterOrders();
        });

        // Update status buttons
        document.querySelectorAll('.update-status').forEach(function(button) {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                document.getElementById('modal-order-id').value = orderId;
            });
        });

        // Submit status update
        document.getElementById('updateStatusSubmit').addEventListener('click', function() {
            document.getElementById('updateStatusForm').submit();
        });

        // Function to filter orders
        function filterOrders() {
            const statusFilter = document.getElementById('status-filter').value;
            const dateFilter = document.getElementById('date-filter').value;
            const searchTerm = document.getElementById('search-input').value.toLowerCase();

            document.querySelectorAll('.order-row').forEach(function(row) {
                let display = true;

                // Status filtering
                if (statusFilter !== 'all' && row.getAttribute('data-status') !== statusFilter) {
                    display = false;
                }

                // Date filtering (this would need to be enhanced for real date comparison)
                if (dateFilter !== '') {
                    // Simplified example - would need more comprehensive date comparison
                    const dateCell = row.querySelector('td:nth-child(3)').textContent;
                    if (!dateCell.includes(dateFilter)) {
                        display = false;
                    }
                }

                // Search filtering
                if (searchTerm !== '') {
                    const orderID = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    const customer = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    if (!orderID.includes(searchTerm) && !customer.includes(searchTerm)) {
                        display = false;
                    }
                }

                // Show/hide row
                row.style.display = display ? '' : 'none';
            });
        }
    });
</script>

<style>
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }

    .small-box .inner {
        padding: 10px;
    }

    .small-box h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px;
        padding: 0;
        white-space: nowrap;
    }

    .small-box p {
        font-size: 1rem;
    }

    .small-box .icon {
        color: rgba(0,0,0,.15);
        font-size: 70px;
        position: absolute;
        right: 15px;
        top: 15px;
        z-index: 0;
    }

    .bg-info {
        background-color: #17a2b8!important;
        color: #fff;
    }

    .bg-success {
        background-color: #28a745!important;
        color: #fff;
    }

    .bg-warning {
        background-color: #ffc107!important;
        color: #1f2d3d;
    }

    .bg-danger {
        background-color: #dc3545!important;
        color: #fff;
    }

    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .badge.bg-warning {
        color: #212529;
    }

    .float-sm-end {
        float: right !important;
    }
</style>
