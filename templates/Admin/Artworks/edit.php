<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Edit Artwork</h1>
        <div>
            <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i>Back to List
            </a>
            <?= $this->Form->postLink(
                '<i class="fas fa-trash-alt mr-1"></i>Delete',
                ['action' => 'delete', $artwork->artwork_id],
                [
                    'confirm' => 'Are you sure you want to delete this artwork?',
                    'class' => 'btn btn-danger',
                    'escape' => false,
                ],
            ) ?>
        </div>
    </div>

    <!-- Edit Artwork Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Artwork ID: <?= h($artwork->artwork_id) ?></h6>
            <span class="badge badge-<?= $artwork->availability_status === 'available' ? 'success' : 'secondary' ?>">
                <?= ucfirst(h($artwork->availability_status)) ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <!-- Current Image Preview -->
                    <div class="text-center">
                        <h6 class="font-weight-bold mb-3">Current Image</h6>
                        <img src="<?= h($artwork->image_url) ?>" alt="<?= h($artwork->title) ?>" class="img-fluid img-thumbnail" style="max-height: 300px;">
                    </div>
                </div>
                <div class="col-md-8">
                    <?= $this->Form->create($artwork, ['type' => 'file']) ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $this->Form->control('title', [
                                    'class' => 'form-control',
                                    'label' => ['class' => 'font-weight-bold'],
                                ]) ?>
                            </div>
                            
                            <div class="form-group">
                                <?= $this->Form->control('description', [
                                    'type' => 'textarea',
                                    'rows' => 4,
                                    'class' => 'form-control',
                                    'label' => ['class' => 'font-weight-bold'],
                                ]) ?>
                            </div>
                            
                            <div class="form-group">
                                <?= $this->Form->control('category', [
                                    'class' => 'form-control',
                                    'label' => ['class' => 'font-weight-bold'],
                                ]) ?>
                            </div>
                            
                            <div class="form-group">
                                <?= $this->Form->control('artist_name', [
                                    'class' => 'form-control',
                                    'label' => ['class' => 'font-weight-bold'],
                                ]) ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $this->Form->control('dimensions', [
                                    'class' => 'form-control',
                                    'label' => ['class' => 'font-weight-bold'],
                                ]) ?>
                            </div>
                            
                            <div class="form-group">
                                <?= $this->Form->control('medium', [
                                    'class' => 'form-control',
                                    'label' => ['class' => 'font-weight-bold'],
                                ]) ?>
                            </div>
                            
                            <div class="form-group">
                                <?= $this->Form->control('price', [
                                    'class' => 'form-control',
                                    'label' => ['class' => 'font-weight-bold'],
                                ]) ?>
                            </div>
                            
                            <div class="form-group">
                                <?= $this->Form->control('availability_status', [
                                    'options' => [
                                        'available' => 'Available',
                                        'sold' => 'Sold',
                                        'reserved' => 'Reserved',
                                    ],
                                    'class' => 'form-control',
                                    'label' => ['class' => 'font-weight-bold'],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= $this->Form->control('quantity', [
                            'class' => 'form-control',
                            'type' => 'number',
                            'min' => '0',
                            'label' => ['class' => 'font-weight-bold', 'text' => 'Quantity in Stock'],
                        ]) ?>
                    </div>
                    
                    <div class="form-group">
                        <?= $this->Form->control('additional_notes', [
                            'type' => 'textarea',
                            'rows' => 3,
                            'class' => 'form-control',
                            'label' => ['class' => 'font-weight-bold'],
                        ]) ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="image_path" class="font-weight-bold">Update Artwork Image</label>
                        <div class="custom-file">
                            <?= $this->Form->control('image_path', [
                                'type' => 'file',
                                'class' => 'custom-file-input',
                                'id' => 'image_path',
                                'label' => [
                                    'class' => 'custom-file-label',
                                    'text' => 'Choose new image file...',
                                ],
                                'templates' => [
                                    'inputContainer' => '{{content}}',
                                ],
                            ]) ?>
                        </div>
                        <small class="form-text text-muted">Leave empty to keep the current image.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Metadata</label>
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="d-block"><strong>Created:</strong> <?= $artwork->created->format('M d, Y H:i') ?></small>
                                        <small class="d-block"><strong>Modified:</strong> <?= $artwork->modified->format('M d, Y H:i') ?></small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="d-block"><strong>ID:</strong> <?= h($artwork->artwork_id) ?></small>
                                        <small class="d-block"><strong>Status:</strong> <?= ucfirst(h($artwork->availability_status)) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save mr-2"></i>Update Artwork
                </button>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
    // Update the file input label with the selected filename
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const fileName = e.target.files[0].name;
            const label = e.target.nextElementSibling;
            label.innerText = fileName;
        }
    });
</script>