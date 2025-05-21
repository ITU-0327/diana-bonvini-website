<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */
$this->assign('title', __('Order #' . $order->order_id . ' - Confirmation'));
?>
<div class="bg-gray-100 min-h-screen pb-8">
    <!-- Success Message Banner -->
    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 mb-6">
        <p class="text-center font-medium">Your payment was successful! Your order has been placed.</p>
    </div>

    <div class="container max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Order Confirmation</h1>
            <p class="mt-3 text-lg text-gray-700">
                Your order has been placed successfully. Thank you for your order!
            </p>
        </div>

        <!-- Confirmation Card -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Order Details -->
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Order Details</h2>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Order ID:</span>
                        <?= h($order->order_id) ?>
                    </p>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Order Date:</span>
                        <?= $order->order_date->format('F j, Y') ?>
                    </p>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Total Amount:</span>
                        $<?= number_format($order->total_amount, 2) ?>
                    </p>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Status:</span>
                        <span class="text-green-600 font-semibold">
                            Confirmed
                        </span>
                    </p>
                </div>

                <!-- Payment Information -->
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Payment Information</h2>
                    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-3">
                        <p class="text-green-700 font-semibold mb-1">Payment Completed</p>
                        <div class="flex items-center mt-2">
                            <svg class="w-8 h-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="24" height="24" rx="4" fill="#6B7280"/>
                                <path d="M4 9C4 7.89543 4.89543 7 6 7H18C19.1046 7 20 7.89543 20 9V15C20 16.1046 19.1046 17 18 17H6C4.89543 17 4 16.1046 4 15V9Z" stroke="white" stroke-width="2"/>
                                <path d="M7 13H10" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            </svg>

                            <div>
                                <p class="text-gray-700">Stripe Payment</p>
                                <p class="text-sm text-gray-600">
                                    <?= date('F j, Y') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-6 border-gray-300">

            <!-- Shipping Information -->
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Shipping Information</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="mb-1"><span class="font-semibold">Name:</span> <?= h($order->billing_first_name) ?> <?= h($order->billing_last_name) ?></p>
                    <p class="mb-1"><span class="font-semibold">Address:</span> <?= h($order->shipping_address1) ?></p>
                    <?php if (!empty($order->shipping_address2)) : ?>
                        <p class="mb-1"><span class="font-semibold"></span> <?= h($order->shipping_address2) ?></p>
                    <?php endif; ?>
                    <p class="mb-1"><?= h($order->shipping_suburb) ?>, <?= h($order->shipping_state) ?> <?= h($order->shipping_postcode) ?></p>
                    <p class="mb-1"><?= h($order->shipping_country === 'AU' ? 'Australia' : $order->shipping_country) ?></p>
                    <p class="mb-1"><span class="font-semibold">Phone:</span> <?= h($order->shipping_phone) ?></p>
                </div>
            </div>

            <!-- Items Ordered -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Items Ordered</h2>
                <?php if (!empty($order->artwork_variant_orders)) : ?>
                    <ul class="space-y-4">
                        <?php foreach ($order->artwork_variant_orders as $item) : ?>
                            <li class="flex items-center justify-between border-b pb-3">
                                <div class="flex items-center">
                                    <?= $this->Html->image(
                                        $item->artwork_variant->artwork->image_url,
                                        ['alt' => $item->artwork_variant->artwork->title, 'class' => 'w-16 h-16 object-cover rounded-lg mr-4'],
                                    ) ?>
                                    <div>
                                        <p class="font-bold text-gray-900 text-lg"><?= h($item->artwork_variant->artwork->title) ?></p>
                                        <p class="text-gray-600 text-sm">Size: <?= h($item->artwork_variant->dimension) ?></p>
                                        <p class="text-gray-600 text-sm">Qty: <?= h($item->quantity) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 text-lg">$<?= number_format($item->price * (float)$item->quantity, 2) ?></p>
                                    <p class="text-gray-600 text-sm">($<?= number_format($item->price, 2) ?> each)</p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Order Totals -->
                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-between mb-2">
                            <span class="font-medium">Subtotal:</span>
                            <span>$<?= number_format($order->total_amount - $order->shipping_cost, 2) ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="font-medium">Shipping:</span>
                            <span>$<?= number_format($order->shipping_cost, 2) ?></span>
                        </div>
                        <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-2 mt-2">
                            <span>Total:</span>
                            <span>$<?= number_format($order->total_amount, 2) ?></span>
                        </div>
                    </div>
                <?php else : ?>
                    <p class="text-gray-600 text-base">No items found.</p>
                <?php endif; ?>
            </div>

            <!-- Call to Action -->
            <div class="mt-8 text-center">
                <?= $this->Html->link(
                    'Continue Shopping',
                    ['controller' => 'Artworks', 'action' => 'index'],
                    ['class' => 'inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md transition-colors duration-200'],
                ) ?>
            </div>
        </div>
    </div>
</div>
