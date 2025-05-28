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

// Calculate sold count (5 - remaining = sold)
$totalEditions = 5;
$soldCount = ($totalEditions - $remaining) + 1; // Add 1 to start from 1 instead of 0
?>

<!-- Toast notification for variant selection -->
<div id="variantToast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded shadow-lg max-w-md">
        <div class="flex">
            <div class="py-1">
                <svg class="w-6 h-6 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="font-bold">Please Select a Size & Print Type</p>
                <p class="text-sm mt-1">Before adding to cart, please choose your preferred size (A1, A2, or A3) and print type (Canvas or Print) from the options below.</p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Left Column: Artwork Images -->
        <div class="space-y-6">
            <!-- Main Artwork Image -->
            <div class="bg-white shadow-lg rounded-lg p-4 flex items-center justify-center">
                <?= $this->Html->image($artwork->image_url, [
                    'alt' => $artwork->title,
                    'class' => 'object-cover max-h-96 rounded-lg',
                ]) ?>
            </div>
        </div>

        <!-- Right Column: Artwork Details -->
        <div class="space-y-6">
            <!-- Back Link -->
            <div class="mb-4">
                <?= $this->Html->link(
                    '<i class="fa-solid fa-arrow-left"></i> Back to Gallery',
                    ['action' => 'index'],
                    ['class' => 'text-indigo-600 hover:text-indigo-800 text-sm font-medium', 'escape' => false],
                ) ?>
            </div>

            <!-- Artwork Title & Limited Edition Counter -->
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= h($artwork->title) ?></h1>
                <div class="inline-flex items-center bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium">
                    <span>Limited Edition <?= h($soldCount) ?> of <?= h($totalEditions) ?></span>
                </div>
            </div>

            <!-- Artwork Description -->
            <div class="text-gray-700 prose">
                <p class="text-base"><?= h($artwork->description) ?></p>
            </div>

            <!-- Price & Shipping Info -->
            <div class="border-t border-gray-200 py-4 space-y-3">
                <?php if ($displayPrice !== null) : ?>
                    <p class="text-2xl font-bold text-gray-800">
                        From $<?= $this->Number->format($displayPrice) ?>
                    </p>
                <?php endif; ?>
                <div class="space-y-2">
                    <p class="text-gray-600 flex items-center">
                        <i class="fa-solid fa-location-dot mr-2"></i>
                        Ships from Adelaide, SA, Australia
                    </p>
                    <p class="text-gray-600 flex items-center">
                        <i class="fa-solid fa-truck mr-2"></i>
                        Estimated to ship in 3–7 days within Australia
                    </p>
                </div>
            </div>

            <?php if ($artwork->availability_status === 'sold') : ?>
                <!-- Sold display -->
                <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg font-semibold text-center">
                    <i class="fa-solid fa-circle-xmark mr-2"></i>Sold Out
                </div>
            <?php elseif ($remaining === 0) : ?>
                <!-- Reserved display -->
                <div class="bg-yellow-100 text-yellow-700 px-4 py-3 rounded-lg font-semibold">
                    <i class="fa-solid fa-clock mr-2"></i>
                    You've added all available copies to your cart. Please review your cart or proceed to checkout.
                </div>
            <?php else : ?>
                <!-- Add to Cart -->
                <div>
                    <?= $this->Form->create(
                        null,
                        [
                            'url' => [
                                'controller' => 'Carts',
                                'action' => 'add',
                                '?' => ['redirect' => '/carts']
                            ],
                            'id' => 'addToCartForm'
                        ],
                    ) ?>

                    <?php
                    $this->Form->unlockField('artwork_variant_id');
                    $this->Form->unlockField('quantity');
                    ?>

                    <!-- Variant selector grid -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-3">Select Size & Print Type</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($sortedVariants as $variant) : ?>
                                <?php $isFirstVariant = $variant === reset($sortedVariants); ?>
                                <div>
                                    <input
                                        type="radio"
                                        name="artwork_variant_id"
                                        id="variant-<?= h($variant->artwork_variant_id) ?>"
                                        value="<?= h($variant->artwork_variant_id) ?>"
                                        class="hidden peer"
                                        required
                                        <?= $isFirstVariant ? 'checked' : '' ?>
                                    />
                                    <label
                                        for="variant-<?= h($variant->artwork_variant_id) ?>"
                                        class="flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer
                                               transition-all duration-200 ease-in-out
                                               hover:border-indigo-400 hover:bg-indigo-50
                                               peer-checked:border-indigo-600 peer-checked:bg-indigo-100"
                                    >
                                        <span class="font-semibold mb-1"><?= h($variant->dimension) ?></span>
                                        <span class="text-sm text-gray-600"><?= h(ucfirst($variant->print_type)) ?></span>
                                        <span class="font-medium text-indigo-600 mt-2">$<?= $this->Number->format($variant->price) ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- quantity -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2" for="quantity">Quantity</label>
                        <div class="flex items-center space-x-4">
                            <?= $this->Form->control('quantity', [
                                'type' => 'number',
                                'min' => 1,
                                'max' => $remaining,
                                'value' => 1,
                                'label' => false,
                                'class' => 'border rounded-lg p-2 w-24 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-600',
                            ]) ?>
                            <span class="text-sm text-gray-600">
                                (<?= h($remaining) ?> available)
                            </span>
                        </div>
                    </div>

                    <?= $this->Form->button('<i class="fa fa-shopping-cart mr-2"></i>Add to Cart', [
                        'escapeTitle' => false,
                        'class' => 'w-full md:w-auto inline-flex items-center justify-center bg-indigo-600 text-white py-3 px-8 rounded-lg hover:bg-indigo-700 transition duration-200',
                        'id' => 'addToCartButton'
                    ]) ?>

                    <?= $this->Form->end() ?>
                </div>
            <?php endif; ?>

            <!-- Tax/Shipping Info -->
            <div class="text-sm text-gray-500 mt-4 p-4 bg-gray-50 rounded-lg">
                <i class="fa-solid fa-info-circle mr-2"></i>
                Taxes and shipping fees will apply upon checkout
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addToCartForm');
    const toast = document.getElementById('variantToast');
    
    // Highlight the first variant option with a subtle animation
    const firstVariant = document.querySelector('input[name="artwork_variant_id"]:checked');
    if (firstVariant) {
        const label = firstVariant.nextElementSibling;
        label.style.transition = 'all 0.5s ease-in-out';
        setTimeout(() => {
            label.style.transform = 'scale(1.02)';
            setTimeout(() => {
                label.style.transform = 'scale(1)';
            }, 300);
        }, 500);
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedVariant = form.querySelector('input[name="artwork_variant_id"]:checked');
            
            if (!selectedVariant) {
                e.preventDefault();
                toast.classList.remove('hidden');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 3000);
            }
        });
    }
});
</script>
