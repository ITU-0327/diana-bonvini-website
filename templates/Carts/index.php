<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Cart|null $cart
 */
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Cart']) ?>

    <?php if ($cart && !empty($cart->artwork_variant_carts)) : ?>
        <!-- Cart Table Card -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 border-b text-left">Artwork</th>
                            <th class="py-2 px-4 border-b text-left">Product</th>
                            <th class="py-2 px-4 border-b text-left">Price</th>
                            <th class="py-2 px-4 border-b text-left">Quantity</th>
                            <th class="py-2 px-4 border-b text-left">Subtotal</th>
                            <th class="py-2 px-4 border-b text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $total = 0.0;
                    foreach ($cart->artwork_variant_carts as $item) :
                        $variant = $item->artwork_variant;
                        $artwork = $variant->artwork;

                        $price = $variant->price;
                        $quantity = (float)$item->quantity;
                        $subtotal = $price * $quantity;
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td class="py-2 px-4 border-b">
                                <?= $this->Html->image($artwork->image_url, [
                                    'alt' => $artwork->title,
                                    'class' => 'w-16 h-16 object-contain',
                                ]) ?>
                            </td>
                            <!-- show title and size -->
                            <td class="py-2 px-4 border-b">
                                <?= h($artwork->title) ?>
                                <br>
                                <small class="text-gray-500"><?= h($variant->dimension) ?></small>
                            </td>
                            <td class="py-2 px-4 border-b">$<?= number_format($price, 2) ?></td>
                            <td class="py-2 px-4 border-b">
                                <?= $this->Form->create(null, ['url' => ['action' => 'updateQuantities'], 'type' => 'post']) ?>
                                <?= $this->Form->control(
                                    "quantities.$item->artwork_variant_cart_id",
                                    [
                                        'type' => 'number',
                                        'min' => 1,
                                        'max' => 5,
                                        'value' => $quantity,
                                        'label' => false,
                                        'class' => 'w-20 border rounded p-1',
                                        'onchange' => 'this.form.submit();',
                                    ]
                                ) ?>
                                <?= $this->Form->end() ?>
                            </td>
                            <td class="py-2 px-4 border-b">$<?= number_format($subtotal, 2) ?></td>
                            <td class="py-2 px-4 border-b">
                                <?= $this->Form->postLink(
                                    'Remove',
                                    ['action' => 'remove', $variant->artwork_variant_id],
                                    ['class' => 'text-red-600 hover:text-red-800 text-sm'],
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100">
                            <td colspan="4" class="py-2 px-4 font-bold text-right">Total:</td>
                            <td class="py-2 px-4 font-bold">$<?= number_format($total, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="text-right">
            <?= $this->Html->link('Proceed to Checkout', ['controller' => 'Orders', 'action' => 'checkout'], [
                'class' => 'bg-indigo-600 text-white px-6 py-3 rounded hover:bg-indigo-700',
            ]) ?>
        </div>
    <?php else : ?>
        <p class="text-gray-700">Your cart is empty.</p>
    <?php endif; ?>
</div>
