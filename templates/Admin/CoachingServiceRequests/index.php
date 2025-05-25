<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest[]|\Cake\Collection\CollectionInterface $coachingServiceRequests
 */

use Cake\Utility\Inflector;

// Calculate statistics for the dashboard
$totalRequests = count($coachingServiceRequests);
$pendingRequests = 0;
$inProgressRequests = 0;
$completedRequests = 0;
$totalRevenue = 0;

foreach ($coachingServiceRequests as $request) {
    $status = $request->request_status ?? 'pending';

    if ($status === 'pending') {
        $pendingRequests++;
    } elseif ($status === 'in_progress') {
        $inProgressRequests++;
    } elseif ($status === 'completed') {
        $completedRequests++;
    }

    // Calculate total revenue from all paid payments
    if (!empty($request->coaching_service_payments)) {
        foreach ($request->coaching_service_payments as $payment) {
            if ($payment->status === 'paid' && !$payment->is_deleted) {
                $totalRevenue += (float)$payment->amount;
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chalkboard-teacher mr-2"></i><?= __('Coaching Service Management') ?>
                        <?php if (isset($totalUnreadCount) && $totalUnreadCount > 0) : ?>
                            <span class="badge badge-danger ml-2"><?= $totalUnreadCount ?> unread</span>
                        <?php endif; ?>
                    </h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Coaching Requests') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= $totalRequests ?></h3>
                    <p>Total Requests</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>$<?= number_format($totalRevenue, 2) ?></h3>
                    <p>Total Revenue</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?= $pendingRequests ?></h3>
                    <p>Pending Quotes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?= $inProgressRequests ?></h3>
                    <p>In Progress</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter mr-1"></i> Filter Requests
                    </h6>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, ['type' => 'get']) ?>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="q" class="form-label">Search Keywords</label>
                            <input type="text" name="q" id="q" class="form-control"
                                value="<?= h($this->request->getQuery('q')) ?>"
                                placeholder="Search by title, client name, etc.">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Created Date</label>
                            <input type="date" name="created_date" class="form-control"
                                value="<?= h($this->request->getQuery('created_date')) ?>"
                                placeholder="Select date">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <?= $this->Form->select('status', [
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                            ], [
                                'empty' => 'All Statuses',
                                'default' => $this->request->getQuery('status'),
                                'class' => 'form-control',
                            ]) ?>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex">
                                <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-secondary">
                                    <i class="fas fa-redo-alt mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">All Coaching Service Requests</h6>
                    <small class="text-muted">
                        Showing <?= count($coachingServiceRequests) ?> results
                        <?php if (!empty($this->request->getQuery())): ?>
                            (filtered)
                        <?php endif; ?>
                    </small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="requestsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Client</th>
                                    <th>Service Type</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($coachingServiceRequests) > 0) : ?>
                                    <?php foreach ($coachingServiceRequests as $request) : ?>
                                        <tr class="request-row hover-clickable cursor-pointer transition-colors" 
                                            data-status="<?= h($request->request_status ?? '') ?>"
                                            data-created-date="<?= isset($request->created_at) ? $request->created_at->format('Y-m-d') : '' ?>"
                                            data-href="<?= $this->Url->build(['action' => 'view', $request->coaching_service_request_id]) ?>"
                                            onclick="window.location.href = this.dataset.href">
                                            <td class="align-middle">
                                                <span class="text-primary font-weight-bold"><?= h(substr($request->coaching_service_request_id, 0, 8)) ?></span>
                                            </td>
                                            <td class="align-middle font-weight-bold"><?= h($request->service_title) ?></td>
                                            <td class="align-middle">
                                                <?php if (isset($request->user) && $request->user) : ?>
                                                    <?= h($request->user->first_name . ' ' . $request->user->last_name) ?>
                                                <?php else : ?>
                                                    Unknown
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle"><?= h($request->service_type) ?></td>
                                            <td class="align-middle">
                                                <?php 
                                                $totalPaid = 0;
                                                if (!empty($request->coaching_service_payments)) {
                                                    foreach ($request->coaching_service_payments as $payment) {
                                                        if ($payment->status === 'paid' && !$payment->is_deleted) {
                                                            $totalPaid += (float)$payment->amount;
                                                        }
                                                    }
                                                }
                                                ?>
                                                <?php if ($totalPaid > 0) : ?>
                                                    <span class="font-weight-bold text-success">$<?= number_format($totalPaid, 2) ?></span>
                                                <?php else : ?>
                                                    <span class="text-muted">$0.00</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <?php
                                                $status = $request->request_status ?? 'pending';
                                                $statusClass = match ($status) {
                                                    'pending' => 'warning',
                                                    'in_progress' => 'primary',
                                                    'completed' => 'success',
                                                    'canceled', 'cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                                $statusLabel = str_replace('_', ' ', $status);
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?= ucfirst(h($statusLabel)) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <?php if (isset($request->created_at) && $request->created_at) : ?>
                                                    <?= $request->created_at->format('M d, Y') ?>
                                                <?php else : ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="mb-0">No coaching service requests found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="paginator">
                        <ul class="pagination justify-content-center mt-4">
                            <?= $this->Paginator->first('<< ' . __('First')) ?>
                            <?= $this->Paginator->prev('< ' . __('Previous')) ?>
                            <?= $this->Paginator->numbers() ?>
                            <?= $this->Paginator->next(__('Next') . ' >') ?>
                            <?= $this->Paginator->last(__('Last') . ' >>') ?>
                        </ul>
                        <p class="text-center"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

    .bg-primary {
        background-color: #007bff!important;
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

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.05);
    }

    /* Clickable row styles */
    .hover-clickable {
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .hover-clickable:hover {
        background-color: #f8f9fa !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .hover-clickable:focus {
        background-color: #e3f2fd !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .transition-colors {
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    /* Improved form controls */
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: 0;
    }
</style>

<?php $this->append('script'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Sort the table by created date (column 6) by default
        const table = document.getElementById('requestsTable');
        if (table && typeof $.fn.DataTable !== 'undefined') {
            $(table).DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: true,
                order: [[6, 'desc']]
            });
        }

        // Filter by status
        document.querySelector('select[name="status"]').addEventListener('change', function() {
            filterRequests();
        });

        // Filter by created date
        document.querySelector('input[name="created_date"]').addEventListener('change', function() {
            filterRequests();
        });

        // Search functionality
        document.getElementById('q').addEventListener('keyup', function() {
            filterRequests();
        });

        // Function to filter requests
        function filterRequests() {
            const statusFilter = document.querySelector('select[name="status"]').value;
            const createdDateFilter = document.querySelector('input[name="created_date"]').value;
            const searchTerm = document.getElementById('q').value.toLowerCase();

            document.querySelectorAll('.request-row').forEach(function(row) {
                let display = true;

                // Status filtering
                if (statusFilter !== '' && row.getAttribute('data-status') !== statusFilter) {
                    display = false;
                }

                // Created date filtering
                if (createdDateFilter !== '') {
                    const rowCreatedDate = row.getAttribute('data-created-date');
                    if (rowCreatedDate !== createdDateFilter) {
                        display = false;
                    }
                }

                // Search filtering
                if (searchTerm !== '') {
                    const title = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const client = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const serviceType = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                    if (!title.includes(searchTerm) && !client.includes(searchTerm) && !serviceType.includes(searchTerm)) {
                        display = false;
                    }
                }

                // Show/hide row
                row.style.display = display ? '' : 'none';
            });
        }

        // Handle clickable rows
        const clickableRows = document.querySelectorAll('tr[data-href]');
        clickableRows.forEach(row => {
            // Add keyboard accessibility
            row.setAttribute('tabindex', '0');
            row.setAttribute('role', 'button');
            row.setAttribute('aria-label', 'View request details');

            // Handle keyboard navigation
            row.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    window.location.href = this.dataset.href;
                }
            });

            // Add visual feedback for focus
            row.addEventListener('focus', function() {
                this.style.outline = '2px solid #007bff';
                this.style.outlineOffset = '-2px';
            });

            row.addEventListener('blur', function() {
                this.style.outline = '';
                this.style.outlineOffset = '';
            });

            // Handle mouse clicks (including middle-click for new tabs)
            row.addEventListener('click', function(e) {
                if (e.ctrlKey || e.metaKey || e.button === 1) {
                    // Ctrl/Cmd+click or middle click - open in new tab
                    window.open(this.dataset.href, '_blank');
                } else {
                    // Regular click - navigate in same tab
                    window.location.href = this.dataset.href;
                }
            });

            // Prevent text selection when clicking
            row.addEventListener('selectstart', function(e) {
                e.preventDefault();
            });
        });
    });
</script>
<?php $this->end(); ?> 