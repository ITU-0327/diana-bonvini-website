<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
$this->assign('title', __('Edit Artwork'));
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-palette mr-2"></i><?= __('Edit Artwork') ?>
                        </h6>
                        <span class="mx-3 text-muted">|</span>
                        <span class="text-muted"><?= h($artwork->title) ?></span>
                    </div>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Artworks'), ['action' => 'index']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Edit') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <?= $this->Html->link(
                '<i class="fas fa-arrow-left mr-2"></i>' . __('Back to Artworks'),
                ['action' => 'index'],
                [
                    'class' => 'btn btn-outline-primary',
                    'escape' => false
                ]
            ) ?>
        </div>
    </div>

    <?= $this->Form->create($artwork, ['type' => 'file', 'class' => 'artwork-form', 'onsubmit' => 'this.querySelector("button[type=submit]").disabled = true;']) ?>
    <div class="row">
        <div class="col-12 col-lg-8">
            <!-- Artwork Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit mr-2"></i>Artwork Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <?= $this->Form->label('title', 'Title', ['class' => 'form-label font-weight-bold']) ?>
                                <?= $this->Form->control('title', [
                                    'label' => false,
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter artwork title',
                                    'required' => true,
                                ]) ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <?= $this->Form->label('availability_status', 'Availability Status', ['class' => 'form-label font-weight-bold']) ?>
                                <?= $this->Form->select(
                                    'availability_status',
                                    [
                                        'available' => 'Available',
                                        'sold' => 'Sold',
                                    ],
                                    ['class' => 'form-control'],
                                ) ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group mb-4">
                                <?= $this->Form->label('description', 'Description', ['class' => 'form-label font-weight-bold']) ?>
                                <?= $this->Form->textarea('description', [
                                    'class' => 'form-control',
                                    'rows' => '5',
                                    'placeholder' => 'Describe the artwork in detail...',
                                    'required' => true,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Variant Prices Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tags mr-2"></i>Variant Prices
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($artwork->artwork_variants as $i => $variant) : ?>
                            <?= $this->Form->control("artwork_variants.$i.artwork_variant_id", ['type' => 'hidden']) ?>
                            <div class="col-md-4 mb-4">
                                <div class="border rounded p-3 bg-light">
                                    <?= $this->Form->control("artwork_variants.$i.dimension", [
                                        'type' => 'text',
                                        'label' => 'Size',
                                        'readonly' => true,
                                        'class' => 'form-control mb-3 font-weight-bold text-center',
                                    ]) ?>
                                    <?= $this->Form->control("artwork_variants.$i.price", [
                                        'type' => 'number',
                                        'step' => '0.01',
                                        'min' => '1',
                                        'label' => 'Price ($)',
                                        'class' => 'form-control mb-3',
                                    ]) ?>
                                    <?= $this->Form->control("artwork_variants.$i.print_type", [
                                        'type' => 'select',
                                        'options' => ['canvas' => 'Canvas', 'print' => 'Print'],
                                        'label' => 'Print Type',
                                        'class' => 'form-control',
                                    ]) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <!-- Image Upload Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-image mr-2"></i>Artwork Image
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <div class="border rounded p-3 mb-3" style="background-color: #f8f9fc;">
                            <?= $this->Html->image('Artworks/' . $artwork->artwork_id . '.jpg', [
                                'alt' => h($artwork->title),
                                'class' => 'img-fluid rounded shadow-sm',
                                'id' => 'preview',
                                'style' => 'max-height: 300px; width: auto;'
                            ]) ?>
                        </div>

                        <div class="form-group">
                            <?= $this->Form->label('image_path', 'Upload New Image', ['class' => 'form-label font-weight-bold mb-3']) ?>
                            
                            <!-- Drag and Drop Zone -->
                            <div class="image-upload-zone border border-dashed rounded p-4 mb-3" id="imageUploadZone" 
                                 style="border-color: #ddd; background-color: #f9f9f9; cursor: pointer; transition: all 0.3s ease;">
                                <div id="uploadPrompt">
                                    <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 2rem;"></i>
                                    <p class="mb-2 text-muted">
                                        <strong>Click to browse</strong> or drag and drop your image here
                                    </p>
                                    <small class="text-muted">JPEG format, Max 8MB</small>
                                </div>
                                <div id="uploadSuccess" class="d-none">
                                    <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                    <p class="mb-0 text-success">
                                        <strong>Image ready for upload!</strong>
                                    </p>
                                </div>
                            </div>
                            
                            <?= $this->Form->file('image_path', [
                                'class' => 'd-none',
                                'accept' => 'image/jpeg',
                                'id' => 'imageUpload',
                                'required' => false,
                            ]) ?>
                            
                            <small class="text-muted d-block">
                                Leave empty to keep current image
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs mr-2"></i>Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <?= $this->Form->button(__('Update Artwork'), [
                            'class' => 'btn btn-success btn-block btn-lg mb-3',
                            'type' => 'submit',
                            'id' => 'updateBtn'
                        ]) ?>
                        
                        <button type="button" class="btn btn-danger btn-block" id="deleteArtworkBtn">
                            <i class="fas fa-trash mr-2"></i><?= __('Delete Artwork') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<!-- Custom Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #dc3545; color: white;">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-trash-alt" style="font-size: 3rem; color: #dc3545;"></i>
                </div>
                <h6 class="mb-3">Are you sure you want to delete this artwork?</h6>
                <p class="text-muted mb-3">
                    <strong>"<?= h($artwork->title) ?>"</strong>
                </p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All associated data will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash mr-2"></i>Yes, Delete Artwork',
                    ['action' => 'delete', $artwork->artwork_id],
                    [
                        'class' => 'btn btn-danger',
                        'escape' => false,
                        'confirm' => false, // We're handling confirmation with the modal
                        'id' => 'confirmDeleteBtn'
                    ]
                ) ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix button icons (add them via JavaScript to avoid escaping issues)
    const updateBtn = document.getElementById('updateBtn');
    if (updateBtn) {
        updateBtn.innerHTML = '<i class="fas fa-save mr-2"></i>' + updateBtn.textContent;
    }

    // Image preview and drag/drop functionality
    const imageUpload = document.getElementById('imageUpload');
    const preview = document.getElementById('preview');
    const uploadZone = document.getElementById('imageUploadZone');
    const uploadPrompt = document.getElementById('uploadPrompt');
    const uploadSuccess = document.getElementById('uploadSuccess');
    const originalImageSrc = preview.src;

    // Click to browse functionality
    uploadZone.addEventListener('click', function() {
        imageUpload.click();
    });

    // File input change handler
    imageUpload.addEventListener('change', function() {
        handleFileSelection(this.files);
    });

    // Drag and drop functionality
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#007bff';
        this.style.backgroundColor = '#e3f2fd';
    });

    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#ddd';
        this.style.backgroundColor = '#f9f9f9';
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#ddd';
        this.style.backgroundColor = '#f9f9f9';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            // Update the file input with the dropped file
            imageUpload.files = files;
            handleFileSelection(files);
        }
    });

    // Handle file selection (both click and drag/drop)
    function handleFileSelection(files) {
        if (!files || files.length === 0) {
            preview.src = originalImageSrc;
            showUploadPrompt();
            return;
        }
        
        const file = files[0];
        
        // Validate file size (8MB max)
        if (file.size > 8 * 1024 * 1024) {
            alert('File size must be less than 8MB');
            imageUpload.value = '';
            preview.src = originalImageSrc;
            showUploadPrompt();
            return;
        }
        
        // Validate file type
        if (!file.type.match('image/jpeg')) {
            alert('Please select a JPEG image file');
            imageUpload.value = '';
            preview.src = originalImageSrc;
            showUploadPrompt();
            return;
        }
        
        // Show success state
        showUploadSuccess();
        
        // Update preview
        const reader = new FileReader();
        reader.addEventListener('load', function() {
            preview.src = reader.result;
        });
        reader.readAsDataURL(file);
    }

    function showUploadPrompt() {
        uploadPrompt.classList.remove('d-none');
        uploadSuccess.classList.add('d-none');
    }

    function showUploadSuccess() {
        uploadPrompt.classList.add('d-none');
        uploadSuccess.classList.remove('d-none');
    }

    // Custom delete confirmation modal
    const deleteBtn = document.getElementById('deleteArtworkBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            $('#deleteConfirmModal').modal('show');
        });
    }

    // Form validation
    const form = document.querySelector('.artwork-form');
    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Clear previous validation states
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        // Basic validation
        const title = document.querySelector('input[name="title"]');
        const description = document.querySelector('textarea[name="description"]');

        if (!title.value.trim()) {
            isValid = false;
            title.classList.add('is-invalid');
            showFieldError(title, 'Please enter an artwork title');
        }

        if (!description.value.trim()) {
            isValid = false;
            description.classList.add('is-invalid');
            showFieldError(description, 'Please enter an artwork description');
        }

        // Validate at least one variant has a price
        const priceInputs = document.querySelectorAll('input[name*="[price]"]');
        let hasValidPrice = false;
        priceInputs.forEach(input => {
            if (input.value && parseFloat(input.value) > 0) {
                hasValidPrice = true;
            }
        });

        if (!hasValidPrice) {
            isValid = false;
            const firstPriceInput = priceInputs[0];
            if (firstPriceInput) {
                firstPriceInput.classList.add('is-invalid');
                showFieldError(firstPriceInput, 'Please set at least one variant price');
            }
        }

        if (!isValid) {
            event.preventDefault();
        } else {
            // Show loading state
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
                submitBtn.disabled = true;
            }
        }
    });

    // Helper function to show field-specific errors
    function showFieldError(field, message) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        field.parentNode.appendChild(feedback);
    }
});
</script>
