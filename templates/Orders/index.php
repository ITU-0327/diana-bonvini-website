<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Order> $orders
 */
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'My Orders']) ?>


    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow">
            <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                <th class="py-3 px-6 text-left">Order ID</th>
                <th class="py-3 px-6 text-left">Total</th>
                <th class="py-3 px-6 text-left">Status</th>
                <th class="py-3 px-6 text-center">Details</th>
            </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
            <?php foreach ($orders as $order) : ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-6"><?= h($order->order_id) ?></td>
                    <td class="py-3 px-6">$<?= $this->Number->format($order->total_amount) ?></td>
                    <td class="py-3 px-6"><?= h($order->order_status) ?></td>
                    <td class="py-3 px-6 text-center">
                        <?= $this->Html->link(
                            'View',
                            ['action' => 'view', $order->order_id],
                            ['class' => 'bg-teal-600 text-white py-1 px-3 rounded hover:bg-teal-700 transition'],
                        ) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
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
