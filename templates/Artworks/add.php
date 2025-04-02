<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
?>
<div class="flex flex-col md:flex-row gap-6">
    <!-- Side Nav -->
    <aside class="w-full md:w-1/4">
        <div class="bg-white shadow rounded p-4">
            <h4 class="text-xl font-bold mb-4"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Artworks'), ['action' => 'index'], [
                'class' => 'block text-blue-600 hover:underline'
            ]) ?>
        </div>
    </aside>

    <!-- Form Section -->
    <div class="w-full md:w-3/4">
        <div class="bg-white shadow rounded p-6 max-w-xl">
            <h2 class="text-2xl font-semibold mb-6"><?= __('Add Artwork') ?></h2>

            <?= $this->Form->create($artwork, ['type' => 'file', 'class' => 'space-y-6']) ?>

            <!-- Title -->
            <div>
                <?= $this->Form->label('title', 'Title', ['class' => 'block text-sm font-medium text-gray-700 mb-1']) ?>
                <?= $this->Form->control('title', [
                    'label' => false,
                    'class' => 'form-input'
                ]) ?>
            </div>

            <!-- Description -->
            <div>
                <?= $this->Form->label('description', 'Description', ['class' => 'block text-sm font-medium text-gray-700 mb-1']) ?>
                <?= $this->Form->textarea('description', [
                    'class' => 'form-input resize-y min-h-[100px]'
                ]) ?>
            </div>

            <!-- Image Upload -->
            <div>
                <?= $this->Form->label('image_path', 'Upload Image', ['class' => 'block text-sm font-medium text-gray-700 mb-1']) ?>
                <?= $this->Form->file('image_path', [
                    'class' => 'form-input file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100'
                ]) ?>
            </div>

            <!-- Price -->
            <div>
                <?= $this->Form->label('price', 'Price', ['class' => 'block text-sm font-medium text-gray-700 mb-1']) ?>
                <?= $this->Form->control('price', [
                    'label' => false,
                    'class' => 'form-input'
                ]) ?>
            </div>

            <!-- Submit Button -->
            <div>
                <?= $this->Form->button(__('Submit'), [
                    'class' => 'bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition'
                ]) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
