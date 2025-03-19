<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 */
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Left Column: Artwork Images -->
        <div class="space-y-6">
            <!-- Main Artwork Image -->
            <div class="bg-white shadow rounded p-4 flex items-center justify-center">
                <?= $this->Html->image($artwork->image_path, [
                    'alt' => $artwork->title,
                    'class' => 'object-cover max-h-96',
                ]) ?>
            </div>

            <!-- Thumbnails -->
            <div class="flex space-x-2">
                <div class="w-20 h-20 bg-white shadow rounded p-1 flex items-center justify-center">
                    <img
                        src="/img/artworks/wallowing-breeze-thumb1.jpg"
                        alt="Wallowing Breeze Thumbnail 1"
                        class="object-cover h-full"
                    />
                </div>
                <div class="w-20 h-20 bg-white shadow rounded p-1 flex items-center justify-center">
                    <img
                        src="/img/artworks/wallowing-breeze-thumb2.jpg"
                        alt="Wallowing Breeze Thumbnail 2"
                        class="object-cover h-full"
                    />
                </div>
            </div>

            <!-- "View in a room" Link -->
            <div>
                <a href="#"
                   class="inline-block text-sm text-indigo-600 hover:text-indigo-500 underline">
                    View in a room
                </a>
            </div>
        </div>

        <!-- Right Column: Artwork Details -->
        <div class="space-y-4">
            <!-- Back Link -->
            <div class="mb-4">
                <?= $this->Html->link(
                    '← Back',
                    ['action' => 'index'],
                    ['class' => 'text-gray-600 hover:text-gray-800 text-sm']
                ) ?>
            </div>

            <!-- Artwork Title & Artist -->
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= h($artwork->title) ?></h1>
            </div>

            <!-- Artwork Meta Info -->
            <div class="text-sm text-gray-500">
                <p>Oil on canvas, 2008</p>
                <p>Gallery wrap canvas</p>
                <p>26 in × 23 in</p>
            </div>

            <!-- Artwork Description -->
            <div class="text-gray-700 text-sm">
                <p><?= h($artwork->description) ?></p>
            </div>

            <!-- Price & Shipping Info -->
            <div class="border-t py-4 text-sm space-y-2">
                <p class="text-xl font-semibold text-gray-800">$<?= $this->Number->format($artwork->price) ?></p>
                <p class="text-gray-500">Ships from Adelaide, SA, Australia</p>
                <p class="text-gray-500">Estimated to ship in 3–7 days within Australia</p>
            </div>

            <!-- Add to Cart Button -->
            <div>
                <?= $this->Form->create(null, ['url' => ['controller' => 'Carts', 'action' => 'add', $artwork->artwork_id]]) ?>
                <?= $this->Form->button(
                    '<i class="fa fa-shopping-cart mr-2"></i>Add to Cart',
                    [
                        'escapeTitle' => false,
                        'class' => 'inline-flex items-center bg-indigo-600 text-white py-2 px-6 rounded hover:bg-indigo-700',
                    ]
                ) ?>
                <?= $this->Form->end() ?>
            </div>

            <!-- Tax/Shipping Info -->
            <div class="text-sm text-gray-500">
                Taxes and shipping fees will apply upon checkout
            </div>
        </div>
    </div>
</div>
