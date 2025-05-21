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
                            <label class="form-label">Search Keywords</label>
                            <input type="text" name="q" id="q" class="form-control"
                                value="<?= h($this->request->getQuery('q')) ?>"
                                placeholder="Search by title, content, etc.">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Service Type</label>
                            <?= $this->Form->select('service_type', [
                                'career_coaching' => 'Career Coaching',
                                'academic_coaching' => 'Academic Coaching',
                                'life_coaching' => 'Life Coaching',
                                'health_coaching' => 'Health Coaching',
                                'other' => 'Other',
                            ], [
                                'empty' => 'All Service Types',
                                'default' => $this->request->getQuery('service_type'),
                                'class' => 'form-control',
                            ]) ?>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <?= $this->Form->select('status', [
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'canceled' => 'Canceled',
                                'cancelled' => 'Cancelled',
                            ], [
                                'empty' => 'All Statuses',
                                'default' => $this->request->getQuery('status'),
                                'class' => 'form-control',
                            ]) ?>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search mr-1"></i> Search
                                </button>
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
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($coachingServiceRequests) > 0) : ?>
                                    <?php foreach ($coachingServiceRequests as $request) : ?>
                                        <tr class="request-row" data-status="<?= h($request->request_status ?? '') ?>">
                                            <td class="align-middle"><?= h(substr($request->coaching_service_request_id, 0, 8)) ?></td>
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
                                                    <span class="font-weight-bold">$<?= number_format($totalPaid, 2) ?></span>
                                                <?php else : ?>
                                                    <span class="badge bg-warning">No Payments</span>
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
                                            <td class="align-middle text-center">
                                                <div class="btn-group d-flex justify-content-center">
                                                    <a href="<?= $this->Url->build(['action' => 'view', $request->coaching_service_request_id]) ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
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
</style>

<?php $this->append('script'); ?>
<script>
    $(document).ready(function() {
        // Initialize DataTable but disable the built-in pagination
        // since we're using CakePHP's pagination
        $('#requestsTable').DataTable({
            "paging": false,
            "searching": false,
            "info": false,
            "ordering": true,
            "order": [[6, 'desc']] // Sort by created date (column 6) by default
        });
        
        // Initialize tooltips if Bootstrap 5 is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Initialize daterangepicker if available
        if ($.fn.daterangepicker) {
            $('.daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MM/DD/YYYY'
                }
            });
            
            $('.daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
            });
            
            $('.daterange').on('cancel.daterangepicker', function() {
                $(this).val('');
            });
        }

        // Filter by status
        $('select[name="status"]').change(function() {
            filterRequests();
        });

        // Filter by service type
        $('select[name="service_type"]').change(function() {
            filterRequests();
        });

        // Search functionality
        $('#q').keyup(function() {
            filterRequests();
        });

        // Function to filter requests
        function filterRequests() {
            const statusFilter = $('select[name="status"]').val();
            const serviceTypeFilter = $('select[name="service_type"]').val();
            const searchTerm = $('#q').val().toLowerCase();

            $('.request-row').each(function() {
                let display = true;
                const row = $(this);

                // Status filtering
                if (statusFilter !== '' && row.data('status') !== statusFilter) {
                    display = false;
                }

                // Service type filtering
                if (serviceTypeFilter !== '') {
                    const serviceType = row.find('td:eq(3)').text().toLowerCase();
                    if (!serviceType.includes(serviceTypeFilter.toLowerCase())) {
                        display = false;
                    }
                }

                // Search filtering
                if (searchTerm !== '') {
                    const title = row.find('td:eq(1)').text().toLowerCase();
                    const client = row.find('td:eq(2)').text().toLowerCase();
                    const serviceType = row.find('td:eq(3)').text().toLowerCase();

                    if (!title.includes(searchTerm) && !client.includes(searchTerm) && !serviceType.includes(searchTerm)) {
                        display = false;
                    }
                }

                // Show/hide row
                row.css('display', display ? '' : 'none');
            });
        }
    });
</script>
<?php $this->end(); ?> 