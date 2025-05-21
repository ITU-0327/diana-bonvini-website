<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Artwork $artwork
 * @var int $remaining
 */

$this->assign('title', __('Artwork Details'));

use Cake\Collection\Collection;

$user = $this->request->getAttribute('identity');

// Define desired size order
$orderMap = ['A3' => 1, 'A2' => 2, 'A1' => 3];
// Sort and filter variants, skipping those with price 0
$orderPrintType = ['canvas' => 1, 'print' => 2];

// Wrap variants in a Collection for sorting and combining
$variants = new Collection($artwork->artwork_variants ?? []);

// Determine cheapest variant price
$cheapest = $variants->sortBy('price')->last();
$displayPrice = $cheapest->price ?? null;

$sortedVariants = $variants
    ->sortBy(fn($v) => (($orderMap[$v->dimension] ?? PHP_INT_MAX) * 10) + ($orderPrintType[$v->print_type] ?? PHP_INT_MAX))
    ->filter(fn($v) => $v->price > 0)
    ->toList();
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

            <?php if ($artwork->availability_status === 'sold') : ?>
                <!-- Sold display -->
                <div class="text-red-500 font-semibold">Sold</div>
            <?php elseif ($remaining === 0) : ?>
                <!-- Reserved display -->
                <div class="text-yellow-600 font-semibold">
                    You've added all available copies to your cart. Please review your cart or proceed to checkout.
                </div>
            <?php else : ?>
                <!-- Add to Cart -->
                <div>
                    <?= $this->Form->create(
                        null,
                        ['url' => ['controller' => 'Carts', 'action' => 'add']],
                    ) ?>

                    <?php
                    // Tell the FormProtector not to lock these two fields:
                    $this->Form->unlockField('artwork_variant_id');
                    $this->Form->unlockField('quantity');
                    ?>

                    <!-- Variant selector grid -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Select Variant</label>
                        <div class="grid grid-cols-3 gap-4">
                            <?php foreach ($sortedVariants as $variant) : ?>
                                <div>
                                    <input
                                        type="radio"
                                        name="artwork_variant_id"
                                        id="variant-<?= h($variant->artwork_variant_id) ?>"
                                        value="<?= h($variant->artwork_variant_id) ?>"
                                        class="hidden peer"
                                        required
                                    />
                                    <label
                                        for="variant-<?= h($variant->artwork_variant_id) ?>"
                                        class="flex flex-col items-center p-4 border rounded-lg cursor-pointer
                                               peer-checked:border-indigo-600 peer-checked:bg-indigo-100"
                                    >
                                        <span class="font-semibold"><?= h($variant->dimension) ?> (<?= h(ucfirst($variant->print_type)) ?>)</span>
                                        <span class="text-gray-600">$<?= $this->Number->format($variant->price) ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
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


            <?php endif; ?>

            <!-- Tax/Shipping Info -->
            <div class="text-sm text-gray-500">
                Taxes and shipping fees will apply upon checkout
            </div>
        </div>
    </div>
</div>
