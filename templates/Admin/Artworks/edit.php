<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
$this->assign('title', __('Edit Artwork'));
$this->Html->script('https://cdn.tailwindcss.com', ['block' => 'script']);
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
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <?= $this->Form->label('availability_status', 'Availability Status', ['class' => 'form-label']) ?>
                                <?= $this->Form->select(
                                    'availability_status',
                                    [
                                        'available' => 'Available',
                                        'sold' => 'Sold',
                                    ],
                                    ['class' => 'form-select'],
                                ) ?>
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
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label">Variant Prices</label>
                                <div class="row">
                                    <?php foreach ($artwork->artwork_variants as $i => $variant) : ?>
                                        <?= $this->Form->control("artwork_variants.$i.artwork_variant_id", ['type' => 'hidden']) ?>
                                        <div class="col-md-4 mb-3">
                                            <?= $this->Form->control("artwork_variants.$i.dimension", [
                                                'type' => 'text',
                                                'label' => 'Size',
                                                'readonly' => true,
                                                'class' => 'form-control',
                                            ]) ?>
                                            <?= $this->Form->control("artwork_variants.$i.price", [
                                                'type' => 'number',
                                                'step' => '0.01',
                                                'min' => '1',
                                                'label' => 'Price ($)',
                                                'class' => 'form-control',
                                            ]) ?>
                                            <?= $this->Form->control("artwork_variants.$i.print_type", [
                                                'type' => 'select',
                                                'options' => ['canvas' => 'Canvas', 'print' => 'Print'],
                                                'label' => 'Print Type',
                                                'class' => 'form-control',
                                            ]) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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
                        <div class="image-preview" id="imagePreview">=
                            <?= $this->Html->image('Artworks/' . $artwork->artwork_id . '.jpg', [
                                'alt' => h($artwork->title),
                                'class' => 'img-fluid rounded shadow-sm d-block mx-auto',
                                'id' => 'preview',
                            ]) ?>
                        </div>

                        <div class="form-group mt-4">
                            <?= $this->Form->label('image_path', 'Upload New Image', ['class' => 'block text-lg font-medium text-gray-700 mb-2']) ?>
                            <?= $this->Form->file('image_path', [
                                'class' => 'w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
                                'accept' => 'image/jpeg',
                                'id' => 'imageUpload',
                                'required' => false,
                            ]) ?>
                            <div class="text-muted small mt-1">JPEG format, Max 8MB</div>
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
    // Capture original image URL to revert when no file is selected
    const originalImageSrc = preview.src;

    imageUpload.addEventListener('change', function() {
        // If no file selected, revert to original image
        if (!this.files || this.files.length === 0) {
            preview.src = originalImageSrc;
            return;
        }
        // Otherwise, show the new image preview
        const file = this.files[0];
        const reader = new FileReader();
        reader.addEventListener('load', function() {
            preview.src = reader.result;
        });
        reader.readAsDataURL(file);
    });

    // Form validation
    const form = document.querySelector('.artwork-form');
    form.addEventListener('submit', function(event) {
        let isValid = true;

        // Basic validation
        const title = document.querySelector('input[name="title"]');
        const description = document.querySelector('textarea[name="description"]');

        if (!title.value.trim()) {
            isValid = false;
            title.classList.add('is-invalid');
        } else {
            title.classList.remove('is-invalid');
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
