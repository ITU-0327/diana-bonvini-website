
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Order> $orders
 */
?>
<div class="max-w-6xl mx-auto py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">My Orders</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow rounded-lg">
            <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Order ID</th>
                <th class="py-3 px-6 text-left">Total Amount</th>
                <th class="py-3 px-6 text-left">Payment Method</th>
                <th class="py-3 px-6 text-left">Order Status</th>
                <th class="py-3 px-6 text-left">Order Date</th>
                <th class="py-3 px-6 text-center">Details</th>
            </tr>
            </thead>
            <tbody class="text-gray-700 text-sm font-light">
            <?php foreach ($orders as $order) : ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-6 text-left"><?= h($order->payment->payment_id) ?></td>
                    <td class="py-3 px-6 text-left"><?= $this->Number->format($order->total_amount) ?></td>
                    <td class="py-3 px-6 text-left"><?= h($order->payment->payment_method) ?></td>
                    <td class="py-3 px-6 text-left"><?= h($order->order_status) ?></td>
                    <td class="py-3 px-6 text-left"><?= h($order->order_date) ?></td>
                    <td class="py-3 px-6 text-center">
                        <?= $this->Html->link('View', ['action' => 'view', $order->order_id], ['class' => 'bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        <ul class="flex justify-center space-x-2">
            <?= $this->Paginator->first('<<', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->prev('<', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->numbers(['before' => '', 'after' => '', 'class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->next('>', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->last('>>', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
        </ul>
        <p class="text-center text-gray-600 mt-2">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </p>
    </div>
</div>
