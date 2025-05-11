<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
?>

<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Artwork</h1>

    <div class="mb-4">
        <?= $this->Html->link(
            '← Back to Artworks',
            ['action' => 'view', $artwork->artwork_id],
            ['class' => 'text-blue-500 hover:text-blue-700'],
        ) ?>
    </div>

    <?= $this->Form->create($artwork, ['class' => 'space-y-6']) ?>

    <!-- Title -->
    <div>
        <?= $this->Form->label('title', 'Title', ['class' => 'block text-gray-700 font-semibold']) ?>
        <?= $this->Form->control('title', [
            'label' => false,
            'class' => 'border border-gray-300 rounded w-full p-2 focus:outline-none focus:ring',
        ]) ?>
    </div>

    <!-- Description -->
    <div>
        <?= $this->Form->label('description', 'Description', ['class' => 'block text-gray-700 font-semibold']) ?>
        <?= $this->Form->control('description', [
            'label' => false,
            'type'  => 'textarea',
            'class' => 'border border-gray-300 rounded w-full p-2 focus:outline-none focus:ring',
            'rows'  => 4,
        ]) ?>
    </div>

    <!-- Price -->
    <div>
        <?= $this->Form->label('price', 'Price', ['class' => 'block text-gray-700 font-semibold']) ?>
        <?= $this->Form->control('price', [
            'label' => false,
            'class' => 'border border-gray-300 rounded w-full p-2 focus:outline-none focus:ring',
        ]) ?>
    </div>

    <!-- Availability Status -->
    <div>
        <?= $this->Form->label('availability_status', 'Availability', ['class' => 'block text-gray-700 font-semibold']) ?>
        <?= $this->Form->control('availability_status', [
            'type'    => 'select',
            'label'   => false,
            'options' => [
                'available' => 'Available',
                'sold'      => 'Sold',
            ],
            'class'   => 'border border-gray-300 rounded w-full p-2 focus:outline-none focus:ring',
        ]) ?>
    </div>

    <!-- Button: Delete Product + Submit -->
    <div class="flex space-x-4">
        <!-- Delete Product：postLink will automatically use POST requests and add confirmation prompts. -->
        <?= $this->Form->postLink(
            'Delete Product',
            ['action' => 'delete', $artwork->artwork_id],
            [
                'confirm' => 'Are you sure you want to delete this artwork?',
                'class'   => 'bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600',
            ],
        ) ?>

        <!-- Submit button -->
        <?= $this->Form->button(
            'Submit',
            ['class' => 'bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'],
        ) ?>
    </div>

    <?= $this->Form->end() ?>
</div>
