<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */
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
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Payment Method</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800"><?= h($order->payment->payment_method) ?></td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Order Status</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800"><?= h($order->order_status) ?></td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Total Amount</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800">$<?= $this->Number->format($order->total_amount) ?></td>
            </tr>
            <tr>
                <th class="py-3 px-6 text-left text-sm font-medium text-gray-600">Order Date</th>
                <td class="py-3 px-6 text-left text-sm text-gray-800">
                    <span class="local-time" data-datetime="<?= h($order->order_date->format('c')) ?>"></span>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <!-- Action Link -->
    <div class="mt-6 text-center">
        <?= $this->Html->link('Back to Orders', ['action' => 'index'], ['class' => 'text-teal-600 hover:text-teal-700 font-semibold']) ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timeElements = document.querySelectorAll('.local-time');
        timeElements.forEach(el => {
            const isoTime = el.dataset.datetime;
            const date = new Date(isoTime);
            el.textContent = date.toLocaleString(undefined, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            });
        });
    });
</script>
