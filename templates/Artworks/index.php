<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Artwork> $artworks
 */
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Art Gallery</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($artworks as $artwork) : ?>
            <!-- Single Artwork Card -->
            <a href="<?= $this->Url->build(['action' => 'view', $artwork->artwork_id]) ?>"
                class="bg-white rounded shadow p-4 flex flex-col">
                <!-- Artwork Image -->
                <div class="mb-4">
                    <?= $this->Html->image($artwork->image_path, [
                        'alt' => $artwork->title,
                        'class' => 'max-w-full h-auto rounded',
                    ]) ?>
                </div>

                <!-- Artwork Details -->
                <h2 class="text-xl font-semibold mb-1"><?= h($artwork->title) ?></h2>
                <p class="text-gray-800 font-semibold mb-4">$<?= $this->Number->format($artwork->price) ?></p>

                <!-- Action Buttons -->
                <div class="mt-auto flex space-x-2">
                    <!-- Buy Now Form/Button -->
                    <?= $this->Form->create(null, [
                        'url' => [
                            'controller' => 'Checkout',
                            'action' => 'buyNow',
                            $artwork->artwork_id,
                        ],
                    ]) ?>
                    <?= $this->Form->button('Buy Now', [
                        'class' => 'bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700',
                    ]) ?>
                    <?= $this->Form->end() ?>

                    <!-- Add to Cart Form/Button -->
                    <?= $this->Form->create(null, [
                        'url' => [
                            'controller' => 'Cart',
                            'action' => 'add',
                            $artwork->artwork_id,
                        ],
                    ]) ?>
                    <?= $this->Form->button('Add to Cart', [
                        'class' => 'bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400',
                    ]) ?>
                    <?= $this->Form->end() ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
