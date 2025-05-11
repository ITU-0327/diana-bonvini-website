<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 * @var int $remaining
 */

use Cake\Collection\Collection;

$user = $this->request->getAttribute('identity');

// Define desired size order
$orderMap = ['A3' => 1, 'A2' => 2, 'A1' => 3];

// Wrap variants in a Collection for sorting and combining
$variants = new Collection($artwork->artwork_variants ?? []);

// Determine cheapest variant price
$cheapest = $variants->sortBy('price')->last();
$displayPrice = $cheapest->price ?? null;

// Sort by defined size order for dropdown
$sortedBySize = $variants->sortBy(fn($v) => $orderMap[$v->dimension] ?? PHP_INT_MAX);

// Build options for size selector
$options = $sortedBySize->combine(
    'artwork_variant_id',
    fn($v) => $v->dimension . ' – $' . $v->price,
)->toArray();
?>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Left Column: Artwork Images -->
        <div class="space-y-6">
            <!-- Main Artwork Image -->
            <div class="bg-white shadow rounded p-4 flex items-center justify-center">
                <?= $this->Html->image($artwork->image_url, [
                    'alt' => $artwork->title,
                    'class' => 'object-cover max-h-96',
                ]) ?>
            </div>
        </div>

        <!-- Right Column: Artwork Details -->
        <div class="space-y-4">
            <!-- Back Link -->
            <div class="mb-4">
                <?= $this->Html->link(
                    '<i class="fa-solid fa-arrow-left"></i> Back',
                    ['action' => 'index'],
                    ['class' => 'text-gray-600 hover:text-gray-800 text-sm', 'escape' => false],
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
                <?php if ($displayPrice !== null) : ?>
                    <p class="text-xl font-semibold text-gray-800">
                        From $<?= $this->Number->format($displayPrice) ?>
                    </p>
                <?php endif; ?>
                <p class="text-gray-500">Ships from Adelaide, SA, Australia</p>
                <p class="text-gray-500">Estimated to ship in 3–7 days within Australia</p>
            </div>

            <?php if ($artwork->availability_status === 'sold' || $remaining == 0) : ?>
                <!-- Sold display -->
                <div class="text-red-500 font-semibold">Sold</div>
            <?php else : ?>
                <!-- Add to Cart -->
                <div>
                    <?= $this->Form->create(
                        null,
                        ['url' => ['controller' => 'Carts', 'action' => 'add']],
                    ) ?>

                    <!-- size selector -->
                    <div class="mb-4">
                        <?= $this->Form->control('artwork_variant_id', [
                            'type' => 'select',
                            'options' => $options,
                            'required' => true,
                            'empty' => 'Select size',
                            'label' => 'Size',
                            'class' => 'border rounded p-2 w-full',
                        ]) ?>
                    </div>

                    <!-- quantity -->
                    <div class="mb-4">
                        <?= $this->Form->control('quantity', [
                            'type' => 'number',
                            'min' => 1,
                            'max' => $remaining,
                            'value' => 1,
                            'label' => 'Quantity',
                            'class' => 'border rounded p-2 w-full',
                        ]) ?>
                        <p class="text-sm text-gray-500">
                            You can add up to <?= h($remaining) ?> more copy<?= $remaining === 1 ? '' : 'ies' ?>.
                        </p>
                    </div>

                    <?= $this->Form->button('<i class="fa fa-shopping-cart mr-2"></i>Add to Cart', [
                        'escapeTitle' => false,
                        'class' => 'inline-flex items-center bg-indigo-600 text-white py-2 px-6 rounded hover:bg-indigo-700',
                    ]) ?>

                    <?= $this->Form->end() ?>
                </div>

                <?php if ($user && $user->user_type === 'admin') : ?>
                    <!-- Admin Edit + Delete -->
                    <div class="flex space-x-2 mt-2">
                        <?= $this->Html->link(
                            '<i class="fa fa-edit mr-2"></i>Edit',
                            ['action' => 'edit', $artwork->artwork_id],
                            [
                                'escapeTitle' => false,
                                'class' => 'inline-flex items-center bg-blue-600 text-white py-2 px-6 rounded hover:bg-blue-700',
                            ],
                        ) ?>

                        <?= $this->Form->postLink(
                            '<i class="fa fa-trash mr-2"></i>Delete',
                            ['action' => 'delete', $artwork->artwork_id],
                            [
                                'escapeTitle' => false,
                                'confirm' => 'Are you sure you want to delete this artwork?',
                                'class' => 'inline-flex items-center bg-red-600 text-white py-2 px-6 rounded hover:bg-red-700',
                            ],
                        ) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Tax/Shipping Info -->
            <div class="text-sm text-gray-500">
                Taxes and shipping fees will apply upon checkout
            </div>
        </div>
    </div>
</div>
