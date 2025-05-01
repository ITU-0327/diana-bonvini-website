<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-palette mr-2"></i><?= __('View Artwork') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Artworks'), ['action' => 'index']) ?></li>
                        <li class="breadcrumb-item active"><?= __('View') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12 col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Artwork Details</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Title</label>
                                <p class="form-control-static"><?= h($artwork->title) ?></p>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Price</label>
                                <p class="form-control-static">$<?= $this->Number->format($artwork->price, ['precision' => 2]) ?></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Availability Status</label>
                                <p class="form-control-static">
                                    <?php
                                    $statusClass = match ($artwork->availability_status) {
                                        'available' => 'success',
                                        'sold' => 'danger',
                                        'pending' => 'warning',
                                        'reserved' => 'info',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst(h($artwork->availability_status)) ?></span>
                                </p>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Artwork ID</label>
                                <p class="form-control-static"><?= h($artwork->artwork_id) ?></p>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label">Description</label>
                                <div class="p-3 bg-light rounded">
                                    <?= nl2br(h($artwork->description)) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Created</label>
                                <p class="form-control-static"><?= $artwork->created_at ? $artwork->created_at->format('M d, Y H:i') : 'N/A' ?></p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">Last Updated</label>
                                <p class="form-control-static"><?= $artwork->updated_at ? $artwork->updated_at->format('M d, Y H:i') : 'N/A' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Update Status Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Status</h6>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, [
                        'url' => ['action' => 'updateStatus', $artwork->artwork_id],
                        'id' => 'updateStatusForm'
                    ]) ?>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <?= $this->Form->label('availability_status', 'Change Availability Status', ['class' => 'form-label']) ?>
                            <?= $this->Form->select('availability_status', [
                                'available' => 'Available',
                                'sold' => 'Sold',
                                'pending' => 'Pending',
                                'reserved' => 'Reserved',
                            ], [
                                'class' => 'form-select',
                                'value' => $artwork->availability_status,
                                'required' => true,
                            ]); ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-success d-block w-100">
                                <i class="fas fa-save mr-1"></i> Update Status
                            </button>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Artwork Image</h6>
                </div>
                <div class="card-body text-center">
                    <div class="image-container mb-3">
                        <?php if ($artwork->image_url): ?>
                            <img src="<?= h($artwork->image_url) ?>" alt="<?= h($artwork->title) ?>" class="img-fluid rounded shadow-sm">
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                No image available for this artwork.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="d-grid gap-2 mb-2">
                            <?= $this->Html->link(__('Edit Artwork'), ['action' => 'edit', $artwork->artwork_id], [
                                'class' => 'btn btn-primary btn-lg',
                            ]) ?>
                        </div>
                        <div class="d-grid gap-2 mb-2">
                            <?= $this->Form->postLink(__('Delete Artwork'), ['action' => 'delete', $artwork->artwork_id], [
                                'confirm' => __('Are you sure you want to delete # {0}?', $artwork->artwork_id),
                                'class' => 'btn btn-danger',
                            ]) ?>
                        </div>
                        <div class="d-grid gap-2">
                            <?= $this->Html->link(__('Back to List'), ['action' => 'index'], [
                                'class' => 'btn btn-outline-secondary',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.image-container {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.image-container img {
    max-height: 300px;
    object-fit: contain;
}

.card-outline {
    border-top: 3px solid;
}

.card-primary.card-outline {
    border-top-color: #007bff;
}

.card-success.card-outline {
    border-top-color: #28a745;
}

.form-label {
    font-weight: 600;
}

.breadcrumb {
    background: transparent;
    margin-bottom: 0;
    padding: 0.75rem 0;
}

.float-sm-end {
    float: right !important;
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

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
    color: white;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white;
}

.form-control-static {
    font-size: 1rem;
    padding-top: 0.375rem;
    padding-bottom: 0.375rem;
    margin-bottom: 0;
}

.d-grid {
    display: grid !important;
}

.gap-2 {
    gap: 0.5rem !important;
}
</style>