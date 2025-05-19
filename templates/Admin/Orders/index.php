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
                            <div class="d-flex">
                                <div class="input-group mr-2">
                                    <input type="text" id="search-input" class="form-control" placeholder="Search orders...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <button id="reset-filters" class="btn btn-secondary" type="button" title="Reset all filters">
                                    <i class="fas fa-undo"></i>
                                </button>
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
                            <th class="text-center">Actions</th>
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
                                <td class="align-middle">
                                    <?php if (isset($order->artwork_variant_orders) && !empty($order->artwork_variant_orders)) : ?>
                                        <?php 
                                        $totalItems = 0;
                                        $itemSummary = [];
                                        
                                        foreach ($order->artwork_variant_orders as $item) {
                                            $totalItems += $item->quantity;
                                            
                                            if (isset($item->artwork_variant) && isset($item->artwork_variant->artwork)) {
                                                $title = $item->artwork_variant->artwork->title;
                                                $size = $item->artwork_variant->size ?? '';
                                                
                                                if (isset($itemSummary[$title])) {
                                                    $itemSummary[$title]['qty'] += $item->quantity;
                                                } else {
                                                    $itemSummary[$title] = [
                                                        'qty' => $item->quantity,
                                                        'size' => $size
                                                    ];
                                                }
                                            }
                                        }
                                        
                                        if (count($itemSummary) > 0) {
                                            $displayItems = array_slice($itemSummary, 0, 2);
                                            
                                            foreach ($displayItems as $title => $info) {
                                                echo '<div class="small" title="' . h($title) . '">';
                                                echo $info['qty'] . 'x ' . h(substr($title, 0, 15)) . (strlen($title) > 15 ? '...' : '');
                                                if (!empty($info['size'])) {
                                                    echo ' (' . h($info['size']) . ')';
                                                }
                                                echo '</div>';
                                            }
                                            
                                            if (count($itemSummary) > 2) {
                                                echo '<div class="small text-muted">+' . (count($itemSummary) - 2) . ' more</div>';
                                            }
                                        } else {
                                            echo '<span class="badge badge-secondary">' . $totalItems . ' item' . ($totalItems !== 1 ? 's' : '') . '</span>';
                                        }
                                        ?>
                                    <?php else : ?>
                                        <span class="text-muted">0 items</span>
                                    <?php endif; ?>
                                </td>
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
                                    <span class="badge badge-<?= $statusClass ?>"><?= ucfirst(h($status)) ?></span>
                                </td>
                                <td class="align-middle text-center">
                                    <div class="btn-group">
                                        <a href="<?= $this->Url->build(['action' => 'view', $order->order_id]) ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </div>
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

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, ['url' => ['action' => 'updateStatus'], 'id' => 'updateStatusForm']) ?>
                <?= $this->Form->hidden('order_id', ['id' => 'modal-order-id']) ?>
                <div class="form-group">
                    <?= $this->Form->label('status', 'Status') ?>
                    <?= $this->Form->select('status', [
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ], [
                        'class' => 'form-control',
                        'required' => true,
                    ]); ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->label('notes', 'Notes') ?>
                    <?= $this->Form->textarea('notes', [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Add notes about this status change (optional)',
                    ]); ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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

        // Sort order
        document.getElementById('sort-order').addEventListener('change', function() {
            sortOrders();
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('keyup', function() {
            filterOrders();
        });

        // Reset filters button
        document.getElementById('reset-filters').addEventListener('click', function() {
            document.getElementById('status-filter').value = 'all';
            document.getElementById('date-filter').value = '';
            document.getElementById('search-input').value = '';
            document.getElementById('sort-order').value = 'newest';
            
            // Reset the display of all rows
            document.querySelectorAll('.order-row').forEach(function(row) {
                row.style.display = '';
            });
            
            // Resort to newest first
            sortOrders();
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

        // Function to sort orders based on selected sort order
        function sortOrders() {
            const sortOrder = document.getElementById('sort-order').value;
            const tbody = document.querySelector('#ordersTable tbody');
            const rows = Array.from(tbody.querySelectorAll('tr.order-row'));
            
            // Skip if no rows to sort
            if (rows.length <= 1) return;
            
            // Sort the rows based on the selected sort order
            rows.sort(function(a, b) {
                if (sortOrder === 'newest' || sortOrder === 'oldest') {
                    // Get dates from the third column (index 2)
                    const dateA = getDateFromCell(a.querySelector('td:nth-child(3)').textContent.trim());
                    const dateB = getDateFromCell(b.querySelector('td:nth-child(3)').textContent.trim());
                    
                    // Sort by date
                    return sortOrder === 'newest' ? dateB - dateA : dateA - dateB;
                } else {
                    // Get amount from the fifth column (index 4)
                    const amountA = getAmountFromCell(a.querySelector('td:nth-child(5)').textContent.trim());
                    const amountB = getAmountFromCell(b.querySelector('td:nth-child(5)').textContent.trim());
                    
                    // Sort by amount
                    return sortOrder === 'amount_high' ? amountB - amountA : amountA - amountB;
                }
            });
            
            // Reorder the rows in the DOM
            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        }
        
        // Helper function to convert displayed date to timestamp for sorting
        function getDateFromCell(dateText) {
            const dateParts = dateText.match(/(\w+)\s+(\d+),\s+(\d+)/);
            if (!dateParts) return 0;
            
            const month = getMonthNumber(dateParts[1]) - 1; // JavaScript months are 0-indexed
            const day = parseInt(dateParts[2]);
            const year = parseInt(dateParts[3]);
            
            return new Date(year, month, day).getTime();
        }
        
        // Helper function to convert displayed amount to number for sorting
        function getAmountFromCell(amountText) {
            // Remove dollar sign and convert to number
            return parseFloat(amountText.replace('$', '').replace(',', '')) || 0;
        }

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

                // Date filtering - proper date comparison
                if (dateFilter !== '') {
                    const dateCell = row.querySelector('td:nth-child(3)').textContent.trim();
                    // Convert the displayed date to a comparable format (YYYY-MM-DD)
                    const dateParts = dateCell.match(/(\w+)\s+(\d+),\s+(\d+)/);
                    if (dateParts) {
                        const month = getMonthNumber(dateParts[1]);
                        const day = dateParts[2].padStart(2, '0');
                        const year = dateParts[3];
                        const rowDate = `${year}-${month}-${day}`;
                        if (rowDate !== dateFilter) {
                            display = false;
                        }
                    } else {
                        display = false;
                    }
                }

                // Search filtering
                if (searchTerm !== '') {
                    const orderID = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    const customer = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const items = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                    if (!orderID.includes(searchTerm) && !customer.includes(searchTerm) && !items.includes(searchTerm)) {
                        display = false;
                    }
                }

                // Show/hide row
                row.style.display = display ? '' : 'none';
            });
        }

        // Helper function to convert month name to number
        function getMonthNumber(monthName) {
            const months = {
                'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
                'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
                'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
            };
            return months[monthName] || '01';
        }
        
        // Initialize with default sort
        sortOrders();
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
</style>
