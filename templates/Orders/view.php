<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */

$this->assign('title', __('Order Details #' . $order->order_id));
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Order Details']) ?>

    <!-- Order Details Card -->
    <div class="bg-white shadow rounded-lg p-6">
        <table class="min-w-full">
            <tbody class="divide-y divide-gray-200">
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Order ID</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800"><?= h($order->order_id) ?></td>
            </tr>
            <?php if ($order->has('user')) : ?>
                <tr>
                    <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">User</th>
                    <td class="py-3 px-6 text-left text-sm text-gray-800">
                        <?= $this->Html->link($order->user->first_name . ' ' . $order->user->last_name, ['controller' => 'Users', 'action' => 'view', $order->user->user_id]) ?>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Order Status</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800"><?= h($order->order_status) ?></td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Order Date</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800">
                    <span class="created-date" data-server-time="<?= $order->order_date->jsonSerialize() ?>" data-time-format="datetime">
                        <?= $order->order_date->format('M d, Y H:i') ?>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- Order Items Card -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Order Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($order->artwork_variant_orders)) : ?>
                        <?php foreach ($order->artwork_variant_orders as $item) : ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap flex items-center">
                                    <?php if (!empty($item->artwork_variant->artwork->image_url)) : ?>
                                        <img src="<?= h($item->artwork_variant->artwork->image_url) ?>" alt="<?= h($item->artwork_variant->artwork->title) ?>" class="w-12 h-12 object-cover rounded mr-4">
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900"><?= h($item->artwork_variant->artwork->title) ?></p>
                                        <p class="text-xs text-gray-500">Variant: <?= h($item->artwork_variant->dimension) ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900"><?= h($item->quantity) ?></td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">$<?= $this->Number->format($item->price, ['precision' => 2]) ?></td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">$<?= $this->Number->format($item->price * $item->quantity, ['precision' => 2]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No items found for this order.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total:</td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">$<?= $this->Number->format($order->total_amount, ['precision' => 2]) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Billing & Shipping Card -->
    <div class="mt-6 bg-white shadow rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h2 class="text-lg font-semibold mb-4">Billing Details</h2>
            <p>
                <strong>Name:</strong> <?= h($order->billing_first_name . ' ' . $order->billing_last_name) ?><br>
                <strong>Email:</strong> <?= h($order->billing_email) ?><br>
                <?php if (!empty($order->billing_company)) : ?><strong>Company:</strong> <?= h($order->billing_company) ?><br><?php endif; ?>
            </p>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-4">Shipping Address</h2>
            <p>
                <?= h($order->shipping_address1) ?><br>
                <?php if (!empty($order->shipping_address2)) : ?><?= h($order->shipping_address2) ?><br><?php endif; ?>
                <?= h($order->shipping_suburb . ', ' . $order->shipping_state . ' ' . $order->shipping_postcode) ?><br>
                <?= h($order->shipping_country) ?><br>
                <strong>Phone:</strong> <?= h($order->shipping_phone) ?>
            </p>
        </div>
    </div>

    <!-- Payment Information Card -->
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Payment Information</h2>
        <?php if (!empty($order->payment)) : ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p><strong>Payment Method:</strong> <?= h(ucfirst($order->payment->payment_method)) ?></p>
                    <p><strong>Payment Status:</strong>
                        <?php
                        $paymentStatusClass = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-green-100 text-green-800',
                            'completed' => 'bg-blue-100 text-blue-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                        ][$order->payment->status] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium <?= $paymentStatusClass; ?>"><?= ucfirst(h($order->payment->status)) ?></span>
                    </p>
                </div>
                <div>
                    <p><strong>Payment Date:</strong>
                        <?php if ($order->payment->payment_date) : ?>
                            <span class="payment-date" data-server-time="<?= $order->payment->payment_date->jsonSerialize() ?>" data-time-format="datetime">
                                <?= $order->payment->payment_date->format('M d, Y H:i') ?>
                            </span>
                        <?php else : ?>
                            <span class="text-gray-500">N/A</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php else : ?>
            <p class="text-center text-gray-500">No payment information available.</p>
        <?php endif; ?>
    </div>

    <!-- Order Notes Card -->
    <?php if (!empty($order->order_notes)) : ?>
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Order Notes</h2>
        <p><?= nl2br(h($order->order_notes)) ?></p>
    </div>
    <?php endif; ?>

    <!-- Action Link -->
    <div class="mt-6 text-center">
        <?= $this->Html->link('Back to Orders', ['action' => 'index'], ['class' => 'text-teal-600 hover:text-teal-700 font-semibold']) ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // The local time converter will automatically handle all timestamp conversions
    });
</script>
