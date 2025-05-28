<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
$this->assign('title', __('Add New Artwork'));
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plus-circle mr-2"></i><?= __('Add New Artwork') ?>
                    </h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Artworks'), ['action' => 'index']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Add') ?></li>
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
                    'escape' => false,
                ],
            ) ?>
        </div>
    </div>

    <?= $this->Form->create($artwork, ['type' => 'file', 'class' => 'artwork-form']) ?>
    <div class="row">
        <div class="col-12 col-lg-8">
            <!-- Artwork Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>Artwork Details
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
                                <?= $this->Form->label('max_copies', 'Max Copies', ['class' => 'form-label font-weight-bold']) ?>
                                <?= $this->Form->control('max_copies', [
                                    'label' => false,
                                    'type' => 'number',
                                    'min' => '1',
                                    'value' => 5,
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter maximum copies',
                                    'required' => true,
                                ]) ?>
                                <small class="text-muted">How many copies of this artwork can be sold</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group mb-4">
                                <?= $this->Form->label('description', 'Description', ['class' => 'form-label font-weight-bold']) ?>
                                <?= $this->Form->textarea('description', [
                                    'class' => 'form-control',
                                    'rows' => '5',
                                    'placeholder' => 'Describe the artwork in detail...',
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note:</strong> Enter prices for the sizes and print types you want to offer. Leave blank or enter 0 to skip a variant.
                    </div>

                    <div class="row">
                        <?php
                        $sizes = ['A3', 'A2', 'A1'];
                        $printTypes = ['canvas', 'print'];
                        $variantsBySize = [];
                        foreach ($artwork->artwork_variants as $i => $variant) {
                            $variantsBySize[$variant->dimension][$variant->print_type] = $i;
                        }
                        ?>

                        <?php foreach ($sizes as $size) : ?>
                            <div class="col-md-4 mb-4">
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="text-center font-weight-bold mb-3 text-primary"><?= h($size) ?></h6>

                                    <?php foreach ($printTypes as $pt) : ?>
                                        <?php if (isset($variantsBySize[$size][$pt])) :
                                            $i = $variantsBySize[$size][$pt];
                                            $variant = $artwork->artwork_variants[$i];
                                            ?>
                                            <?= $this->Form->control(
                                                "artwork_variants.$i.dimension",
                                                ['type' => 'hidden', 'value' => $variant->dimension],
                                            ) ?>
                                            <?= $this->Form->control(
                                                "artwork_variants.$i.print_type",
                                                ['type' => 'hidden', 'value' => $variant->print_type],
                                            ) ?>

                                            <div class="form-group mb-3">
                                                <label class="form-label"><?= ucfirst($pt) ?> Price ($)</label>
                                                <?= $this->Form->control(
                                                    "artwork_variants.$i.price",
                                                    [
                                                        'type' => 'number',
                                                        'step' => '0.01',
                                                        'min' => '0',
                                                        'label' => false,
                                                        'class' => 'form-control',
                                                        'placeholder' => 'Enter price (0 to skip)',
                                                        'required' => false,
                                                    ],
                                                ) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
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
                        <div class="border rounded p-3 mb-3" id="imagePreviewContainer" style="background-color: #f8f9fc; display: none;">
                            <img src="" alt="Image Preview" class="img-fluid rounded shadow-sm" id="preview" style="max-height: 300px; width: auto;">
                        </div>

                        <div class="form-group">
                            <?= $this->Form->label('image_path', 'Upload Artwork Image', ['class' => 'form-label font-weight-bold mb-3']) ?>

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
                                'required' => true,
                            ]) ?>

                            <small class="text-muted d-block">
                                <strong>Required:</strong> Please upload an image for this artwork
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
                        <?= $this->Form->button(__('Save Artwork'), [
                            'class' => 'btn btn-success btn-block btn-lg mb-3',
                            'type' => 'submit',
                            'id' => 'saveBtn',
                        ]) ?>

                        <?= $this->Html->link(__('Cancel'), ['action' => 'index'], [
                            'class' => 'btn btn-outline-secondary btn-block',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix button icons (add them via JavaScript to avoid escaping issues)
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>' + saveBtn.textContent;
    }

    // Image preview and drag/drop functionality
    const imageUpload = document.getElementById('imageUpload');
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const uploadZone = document.getElementById('imageUploadZone');
    const uploadPrompt = document.getElementById('uploadPrompt');
    const uploadSuccess = document.getElementById('uploadSuccess');

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
            preview.src = '';
            previewContainer.style.display = 'none';
            showUploadPrompt();
            return;
        }

        const file = files[0];

        // Validate file size (8MB max)
        if (file.size > 8 * 1024 * 1024) {
            alert('File size must be less than 8MB');
            imageUpload.value = '';
            preview.src = '';
            previewContainer.style.display = 'none';
            showUploadPrompt();
            return;
        }

        // Validate file type
        if (!file.type.match('image/jpeg')) {
            alert('Please select a JPEG image file');
            imageUpload.value = '';
            preview.src = '';
            previewContainer.style.display = 'none';
            showUploadPrompt();
            return;
        }

        // Show success state
        showUploadSuccess();

        // Update preview
        const reader = new FileReader();
        reader.addEventListener('load', function() {
            preview.src = reader.result;
            previewContainer.style.display = 'block';
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

    // Enhanced form validation
    const form = document.querySelector('.artwork-form');
    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Clear previous validation states
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        // Basic validation
        const title = document.querySelector('input[name="title"]');
        const description = document.querySelector('textarea[name="description"]');
        const maxCopies = document.querySelector('input[name="max_copies"]');

        if (!title.value.trim()) {
            isValid = false;
            title.classList.add('is-invalid');
            showFieldError(title, 'Please enter an artwork title');
        }

        if (!maxCopies.value || parseInt(maxCopies.value) < 1) {
            isValid = false;
            maxCopies.classList.add('is-invalid');
            showFieldError(maxCopies, 'Please enter a valid number of max copies (minimum 1)');
        }

        // Image upload validation
        if (imageUpload.files.length === 0) {
            isValid = false;
            uploadZone.style.borderColor = '#dc3545';
            uploadZone.style.backgroundColor = '#f8d7da';
            showFieldError(uploadZone, 'Please upload an image for this artwork');
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
                showFieldError(firstPriceInput, 'Please set at least one variant price greater than 0');
            }
        }

        if (!isValid) {
            event.preventDefault();
        } else {
            // Show loading state
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
                submitBtn.disabled = true;
            }
        }
    });

    // Helper function to show field-specific errors
    function showFieldError(field, message) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.textContent = message;
        field.parentNode.appendChild(feedback);
    }

    // Auto-calculate suggested prices based on size
    const priceInputs = document.querySelectorAll('input[name*="[price]"]');
    priceInputs.forEach(input => {
        const name = input.getAttribute('name');

        // Add helpful placeholder text based on size and type
        if (name.includes('A3')) {
            if (name.includes('canvas')) {
                input.placeholder = 'e.g., 25.00';
            } else {
                input.placeholder = 'e.g., 15.00';
            }
        } else if (name.includes('A2')) {
            if (name.includes('canvas')) {
                input.placeholder = 'e.g., 45.00';
            } else {
                input.placeholder = 'e.g., 25.00';
            }
        } else if (name.includes('A1')) {
            if (name.includes('canvas')) {
                input.placeholder = 'e.g., 75.00';
            } else {
                input.placeholder = 'e.g., 45.00';
            }
        }
    });
});
</script>
