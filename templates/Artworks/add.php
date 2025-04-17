<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
?>
<div class="flex flex-col md:flex-row gap-8">
    <aside class="w-full md:w-1/4">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h4 class="text-2xl font-bold text-gray-800 mb-6"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Artworks'), ['action' => 'index'], [
                'class' => 'block text-blue-600 hover:underline text-lg',
            ]) ?>
        </div>
    </aside>

    <div class="w-full md:w-3/4">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h2 class="text-3xl font-semibold text-gray-800 mb-8"><?= __('Add Artwork') ?></h2>

            <?= $this->Form->create($artwork, ['type' => 'file', 'class' => 'space-y-6']) ?>

            <div>
                <?= $this->Form->label('title', 'Title', ['class' => 'block text-lg font-medium text-gray-700 mb-2']) ?>
                <?= $this->Form->control('title', [
                    'label' => false,
                    'class' => 'w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400',
                ]) ?>
            </div>

            <div>
                <?= $this->Form->label('description', 'Description', ['class' => 'block text-lg font-medium text-gray-700 mb-2']) ?>
                <?= $this->Form->textarea('description', [
                    'class' => 'w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 resize-y min-h-[120px]',
                ]) ?>
            </div>

            <div>
                <?= $this->Form->label('image_path', 'Upload Image', ['class' => 'block text-lg font-medium text-gray-700 mb-2']) ?>
                <?= $this->Form->file('image_path', [
                    'class' => 'w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
                    'accept' => 'image/jpeg'
                ]) ?>
            </div>

            <div>
                <?= $this->Form->label('price', 'Price', ['class' => 'block text-lg font-medium text-gray-700 mb-2']) ?>
                <?= $this->Form->control('price', [
                    'label' => false,
                    'class' => 'w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400',
                ]) ?>
            </div>

            <div>
                <?= $this->Form->button(__('Submit'), [
                    'class' => 'w-full bg-blue-600 text-white font-semibold px-4 py-3 rounded-md hover:bg-blue-700 transition duration-150',
                ]) ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
