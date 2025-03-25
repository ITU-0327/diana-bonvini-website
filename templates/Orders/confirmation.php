<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */
?>
<div class="container max-w-4xl mx-auto py-8">
    <!-- Header Section -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Order Confirmation</h1>
        <p class="text-lg text-gray-700">
            Thank you for your order! Your order ID is:
            <span class="font-semibold text-indigo-600"><?= h($order->order_id) ?></span>
        </p>
    </div>

    <!-- Order Details Card -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Order Summary -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Order Summary</h2>
                <p class="mb-2">
                    <span class="font-semibold">Total Amount:</span>
                    $<?= number_format($order->total_amount, 2) ?>
                </p>
                <p class="mb-2">
                    <span class="font-semibold">Payment Method:</span>
                    <?= h($order->payment_method) ?>
                </p>
                <p class="mb-2">
                    <span class="font-semibold">Status:</span>
                    <?= h($order->order_status) ?>
                </p>
                <p class="mb-2">
                    <span class="font-semibold">Order Date:</span>
                    <?= h($order->order_date) ?>
                </p>
            </div>

            <!-- Items Ordered -->
            <div>
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Items Ordered</h3>
                <?php if (!empty($order->artwork_orders)) : ?>
                    <ul class="space-y-3">
                        <?php foreach ($order->artwork_orders as $item) : ?>
                            <li class="flex justify-between items-center border-b pb-2">
                                <div>
                                    <p class="font-semibold text-gray-900"><?= h($item->artwork->title) ?></p>
                                    <p class="text-sm text-gray-600">Qty: <?= h($item->quantity) ?></p>
                                </div>
                                <p class="font-semibold text-gray-900">$<?= number_format($item->price, 2) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-600">No items found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
