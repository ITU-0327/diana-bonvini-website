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
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-palette mr-2"></i><?= __('Edit Artwork') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Artworks'), ['action' => 'index']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Edit') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <?= $this->Form->create($artwork, ['type' => 'file', 'class' => 'artwork-form']) ?>
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
                                <?= $this->Form->label('title', 'Title', ['class' => 'form-label']) ?>
                                <?= $this->Form->control('title', [
                                    'label' => false,
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter artwork title',
                                    'required' => true,
                                ]) ?>
                            </div>

                            <div class="form-group mb-3">
                                <?= $this->Form->label('price', 'Price ($)', ['class' => 'form-label']) ?>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <?= $this->Form->control('price', [
                                        'label' => false,
                                        'class' => 'form-control',
                                        'placeholder' => '0.00',
                                        'step' => '0.01',
                                        'min' => '0',
                                        'required' => true,
                                    ]) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <?= $this->Form->label('availability_status', 'Availability Status', ['class' => 'form-label']) ?>
                                <?= $this->Form->select(
                                    'availability_status',
                                    [
                                        'available' => 'Available', 
                                        'sold' => 'Sold',
                                        'pending' => 'Pending',
                                        'reserved' => 'Reserved'
                                    ],
                                    ['class' => 'form-select']
                                ) ?>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Artwork ID</label>
                                <p class="form-control-static"><?= h($artwork->artwork_id) ?></p>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group mb-3">
                                <?= $this->Form->label('description', 'Description', ['class' => 'form-label']) ?>
                                <?= $this->Form->textarea('description', [
                                    'class' => 'form-control',
                                    'rows' => '4',
                                    'placeholder' => 'Describe the artwork in detail...',
                                    'required' => true,
                                ]) ?>
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
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Artwork Image</h6>
                </div>
                <div class="card-body">
                    <div class="image-upload-container mb-3">
                        <div class="image-preview" id="imagePreview">
                            <img src="<?= h($artwork->image_url) ?>" alt="<?= h($artwork->title) ?>" class="img-fluid rounded shadow-sm d-block mx-auto" id="preview">
                        </div>

                        <div class="form-group mt-4">
                            <?= $this->Form->label('image_path', 'Upload New Image', ['class' => 'form-label']) ?>
                            <?= $this->Form->file('image_path', [
                                'class' => 'form-control',
                                'accept' => 'image/jpeg,image/png',
                                'id' => 'imageUpload',
                            ]) ?>
                            <div class="text-muted small mt-1">JPEG or PNG format, Recommended size: 1200x800px, Max 5MB</div>
                            <div class="text-muted small mt-1">Leave empty to keep the current image</div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <div class="d-grid gap-2">
                            <?= $this->Form->button(__('Update Artwork'), [
                                'class' => 'btn btn-success btn-lg',
                                'type' => 'submit',
                            ]) ?>
                        </div>
                        <div class="d-grid gap-2 mt-2">
                            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], [
                                'class' => 'btn btn-outline-secondary',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const imageUpload = document.getElementById('imageUpload');
    const preview = document.getElementById('preview');

    imageUpload.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();

            reader.addEventListener('load', function() {
                preview.src = reader.result;
            });

            reader.readAsDataURL(file);
        }
    });

    // Form validation
    const form = document.querySelector('.artwork-form');
    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Basic validation
        const title = document.querySelector('input[name="title"]');
        const price = document.querySelector('input[name="price"]');
        const description = document.querySelector('textarea[name="description"]');

        if (!title.value.trim()) {
            isValid = false;
            title.classList.add('is-invalid');
        } else {
            title.classList.remove('is-invalid');
        }

        if (!price.value || isNaN(parseFloat(price.value)) || parseFloat(price.value) < 0) {
            isValid = false;
            price.classList.add('is-invalid');
        } else {
            price.classList.remove('is-invalid');
        }

        if (!description.value.trim()) {
            isValid = false;
            description.classList.add('is-invalid');
        } else {
            description.classList.remove('is-invalid');
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
});
</script>

<style>
.image-upload-container {
    border: 2px dashed #ddd;
    padding: 20px;
    text-align: center;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.image-preview {
    margin-bottom: 15px;
}

.image-preview img {
    max-height: 200px;
    border: 1px solid #eee;
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

.is-invalid {
    border-color: #dc3545 !important;
}

.breadcrumb {
    background: transparent;
    margin-bottom: 0;
    padding: 0.75rem 0;
}

.float-sm-end {
    float: right !important;
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