<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|array<\App\Model\Entity\WritingServiceRequest> $writingServiceRequests
 */

use Cake\Utility\Inflector;
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Writing Service Requests</h1>
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Requests</h6>
        </div>
        <div class="card-body">
            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'form-row align-items-end']) ?>
                <div class="col-md-3 mb-3">
                    <label for="q" class="font-weight-bold">Search Keywords</label>
                    <input type="text" name="q" id="q" class="form-control" 
                           value="<?= h($this->request->getQuery('q')) ?>"
                           placeholder="Search by title, content, etc.">
                </div>

                <div class="col-md-3 mb-3">
                    <label for="service_type" class="font-weight-bold">Service Type</label>
                    <?= $this->Form->select('service_type', [
                        'creative_writing' => 'Creative Writing',
                        'editing' => 'Editing',
                        'proofreading' => 'Proofreading',
                        'gamsat_preparation' => 'GAMSAT Preparation',
                        'other' => 'Other',
                    ], [
                        'empty' => 'All Service Types',
                        'default' => $this->request->getQuery('service_type'),
                        'class' => 'form-control',
                    ]) ?>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="status" class="font-weight-bold">Status</label>
                    <?= $this->Form->select('status', [
                        'pending' => 'Pending',
                        'pending_quote' => 'Pending Quote',
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ], [
                        'empty' => 'All Statuses',
                        'default' => $this->request->getQuery('status'),
                        'class' => 'form-control',
                    ]) ?>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-secondary">
                            <i class="fas fa-redo-alt mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <!-- Request Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Requests</h6>
            <div>
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-download fa-sm mr-1"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="#"><i class="fas fa-file-csv mr-2"></i>CSV</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-file-excel mr-2"></i>Excel</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="requestsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Client</th>
                            <th>Service Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($writingServiceRequests as $request) : ?>
                            <tr>
                                <td><?= h(substr($request->writing_service_request_id, 0, 8)) ?></td>
                                <td><?= h($request->service_title) ?></td>
                                <td>
                                    <?php if (isset($request->user) && $request->user) : ?>
                                        <?= h($request->user->first_name . ' ' . $request->user->last_name) ?>
                                    <?php else : ?>
                                        <?= h($request->client_name ?? 'Unknown') ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= h(Inflector::humanize($request->service_type)) ?></td>
                                <td>
                                    <?php if ($request->final_price) : ?>
                                        $<?= number_format($request->final_price, 2) ?>
                                    <?php else : ?>
                                        <span class="badge badge-warning">Pending Quote</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match ($request->status) {
                                        'pending', 'pending_quote' => 'warning',
                                        'scheduled' => 'info',
                                        'in_progress' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    $statusLabel = str_replace('_', ' ', $request->status);
    ?>
                                    <span class="badge badge-<?= $statusClass ?>">
                                        <?= ucfirst(h($statusLabel)) ?>
                                    </span>
                                </td>
                                <td><?= $request->created->format('M d, Y') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $this->Url->build(['action' => 'view', $request->writing_service_request_id]) ?>" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-primary update-status" data-toggle="modal" data-target="#updateStatusModal" data-id="<?= h($request->writing_service_request_id) ?>" data-status="<?= h($request->status) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (count($writingServiceRequests) === 0) : ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="fas fa-inbox fa-3x text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-600 mb-0">No writing service requests found</p>
                                </td>
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

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Request Status</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, ['url' => ['action' => 'updateStatus'], 'id' => 'updateStatusForm']) ?>
                <?= $this->Form->hidden('id', ['id' => 'modal-request-id']) ?>
                <div class="form-group">
                    <?= $this->Form->control('status', [
                        'options' => [
                            'pending' => 'Pending',
                            'pending_quote' => 'Pending Quote',
                            'scheduled' => 'Scheduled',
                            'in_progress' => 'In Progress',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                        ],
                        'class' => 'form-control',
                        'id' => 'modal-status',
                        'label' => 'Status',
                        'required' => true,
                    ]); ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->control('admin_notes', [
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Add notes about this status change (optional)',
                    ]); ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="button" id="updateStatusSubmit">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        $('#requestsTable').DataTable({
            paging: false,
            searching: false,
            info: false
        });
        
        // Update status modal handling
        $('.update-status').on('click', function() {
            const requestId = $(this).data('id');
            const currentStatus = $(this).data('status');
            
            $('#modal-request-id').val(requestId);
            $('#modal-status').val(currentStatus);
        });
        
        // Submit status update
        $('#updateStatusSubmit').on('click', function() {
            $('#updateStatusForm').submit();
        });
    });
</script>