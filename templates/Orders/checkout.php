<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 * @var \App\Model\Entity\Cart $cart
 * @var \App\Model\Entity\User|null $user
 * @var float $total
 */
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Checkout</h1>

    <!-- Order Summary Section -->
    <section class="mb-6">
        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
        <table class="min-w-full bg-white border">
            <thead>
            <tr class="bg-gray-100">
                <th class="py-2 px-4 border-b text-left">Artwork</th>
                <th class="py-2 px-4 border-b text-left">Title</th>
                <th class="py-2 px-4 border-b text-left">Price</th>
                <th class="py-2 px-4 border-b text-left">Quantity</th>
                <th class="py-2 px-4 border-b text-left">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($cart->artwork_carts as $item) : ?>
                <?php if (isset($item->artwork)) : ?>
                    <tr>
                        <td class="py-2 px-4 border-b">
                            <?= $this->Html->image($item->artwork->image_path, [
                                'alt' => $item->artwork->title,
                                'class' => 'w-16 h-16 object-contain',
                            ]) ?>
                        </td>
                        <td class="py-2 px-4 border-b"><?= h($item->artwork->title) ?></td>
                        <td class="py-2 px-4 border-b">$<?= number_format($item->artwork->price, 2) ?></td>
                        <td class="py-2 px-4 border-b"><?= h($item->quantity) ?></td>
                        <td class="py-2 px-4 border-b">$<?= number_format($item->artwork->price * $item->quantity, 2) ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr class="bg-gray-100">
                <td colspan="4" class="py-2 px-4 font-bold text-right">Total:</td>
                <td class="py-2 px-4 font-bold">$<?= number_format($total, 2) ?></td>
            </tr>
            </tfoot>
        </table>
    </section>

    <!-- Shipping Details Section -->
    <section class="mb-6">
        <h2 class="text-xl font-semibold mb-4">Shipping Details</h2>
        <?= $this->Form->create($order, ['url' => ['action' => 'placeOrder']]) ?>
        <div class="mb-4">
            <?= $this->Form->control('shipping_name', [
                'label' => 'Full Name',
                'class' => 'border rounded w-full px-3 py-2',
                'value' => $user ? $user->first_name . ' ' . $user->last_name : '',
            ]) ?>
        </div>
        <div class="mb-4">
            <?= $this->Form->control('shipping_address', [
                'label' => 'Shipping Address',
                'class' => 'border rounded w-full px-3 py-2',
                'value' => $user ? $user->address : '',
            ]) ?>
        </div>
        <div class="mb-4">
            <?= $this->Form->control('shipping_phone', [
                'label' => 'Phone Number',
                'class' => 'border rounded w-full px-3 py-2',
                'value' => $user ? $user->phone_number : '',
            ]) ?>
        </div>
    </section>

    <!-- Payment Method Section (Bank Transfer) -->
    <section class="mb-6">
        <h2 class="text-xl font-semibold mb-4">Payment Method</h2>
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Bank Transfer</label>
            <p class="text-gray-600 text-sm">
                Please transfer the total amount to our bank account. We will send you the order confirmation once the payment is received.
            </p>
            <div class="mt-2 p-4 bg-gray-100 rounded">
                <p class="font-semibold">Bank Name:</p>
                <p>Example Bank</p>
                <p class="font-semibold">Account Number:</p>
                <p>123456789</p>
                <p class="font-semibold">Account Name:</p>
                <p>Diana Bonvini Art &amp; Writing</p>
            </div>
        </div>
    </section>

    <!-- Order Submission Section -->
    <section>
        <h2 class="text-xl font-semibold mb-4">Review and Place Order</h2>
        <div class="mt-6">
            <?= $this->Form->button('Place Order', [
                'class' => 'bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700',
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </section>
</div>
