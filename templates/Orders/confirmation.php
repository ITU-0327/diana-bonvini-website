<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */
?>
<div class="container max-w-3xl mx-auto py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900">Order Confirmation</h1>
        <p class="mt-3 text-lg text-gray-700">
            Your order has been placed successfully. Thank you for your order!
        </p>
    </div>

    <!-- Confirmation Card -->
    <div class="bg-white shadow-lg rounded-xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Order & Payment Summary -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Summary</h2>
                <p class="mb-2 text-base">
                    <span class="font-bold">Total Amount:</span>
                    $<?= number_format($order->total_amount, 2) ?>
                </p>
                <?php if (!empty($order->payment)) : ?>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Reference No:</span>
                        <?= h($order->payment->payment_id) ?>
                    </p>
                <?php endif; ?>
            </div>
            <!-- Bank Transfer Instructions -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Payment Instructions</h2>
                <p class="text-gray-700 mb-3 text-base">
                    Please transfer <strong>$<?= number_format($order->total_amount, 2) ?></strong> to:
                </p>
                <ul class="list-disc list-inside text-gray-700 text-base space-y-1 mb-3">
                    <li><span class="font-semibold">Bank:</span> Commonwealth Bank of Australia</li>
                    <li><span class="font-semibold">BSB:</span> 062-123</li>
                    <li><span class="font-semibold">Account:</span> 1234 5678</li>
                </ul>
                <?php if (!empty($order->payment)) : ?>
                    <p class="text-gray-700 text-base">
                        Use <span class="font-semibold">Reference No: <?= h($order->payment->payment_id) ?></span>
                        in your transfer reference.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-6 border-gray-300">

        <!-- Items Ordered -->
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Items Ordered</h2>
            <?php if (!empty($order->artwork_orders)) : ?>
                <ul class="space-y-4">
                    <?php foreach ($order->artwork_orders as $item) : ?>
                        <li class="flex items-center justify-between border-b pb-3">
                            <div class="flex items-center">
                                <?= $this->Html->image(
                                    $item->artwork->image_path,
                                    ['alt' => $item->artwork->title, 'class' => 'w-16 h-16 object-cover rounded-lg mr-4']
                                ) ?>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg"><?= h($item->artwork->title) ?></p>
                                    <p class="text-gray-600 text-sm">Qty: <?= h($item->quantity) ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900 text-lg">$<?= number_format($item->price, 2) ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="text-gray-600 text-base">No items found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
