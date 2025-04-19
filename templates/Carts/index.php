<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Cart|null $cart
 */
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Cart']) ?>

    <?php if ($cart && !empty($cart->artwork_carts)) : ?>
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
                    foreach ($cart->artwork_carts as $item) :
                        $artwork = $item->artwork;
                        $subtotal = $artwork->price * (float)$item->quantity;
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td class="py-2 px-4 border-b">
                                <?= $this->Html->image($artwork->image_url, [
                                    'alt' => $artwork->title,
                                    'class' => 'w-16 h-16 object-contain',
                                ]) ?>
                            </td>
                            <td class="py-2 px-4 border-b"><?= h($artwork->title) ?></td>
                            <td class="py-2 px-4 border-b">$<?= number_format($artwork->price, 2) ?></td>
                            <td class="py-2 px-4 border-b"><?= h($item->quantity) ?></td>
                            <td class="py-2 px-4 border-b">$<?= number_format($subtotal, 2) ?></td>
                            <td class="py-2 px-4 border-b">
                                <?= $this->Form->postLink(
                                    'Remove',
                                    ['action' => 'remove', $artwork->artwork_id],
                                    ['class' => 'text-red-600 hover:text-red-800 text-sm']
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
                'class' => 'bg-indigo-600 text-white px-6 py-3 rounded hover:bg-indigo-700'
            ]) ?>
        </div>
    <?php else : ?>
        <p class="text-gray-700">Your cart is empty.</p>
    <?php endif; ?>
</div>
