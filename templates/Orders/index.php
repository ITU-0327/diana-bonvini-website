<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Order> $orders
 */
?>
<div class="orders index content">
    <?= $this->Html->link(__('New Order'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Orders') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('order_id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('total_amount') ?></th>
                    <th><?= $this->Paginator->sort('payment_method') ?></th>
                    <th><?= $this->Paginator->sort('order_status') ?></th>
                    <th><?= $this->Paginator->sort('order_date') ?></th>
                    <th><?= $this->Paginator->sort('is_deleted') ?></th>
                    <th><?= $this->Paginator->sort('created_at') ?></th>
                    <th><?= $this->Paginator->sort('updated_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= h($order->order_id) ?></td>
                    <td><?= $order->hasValue('user') ? $this->Html->link($order->user->first_name, ['controller' => 'Users', 'action' => 'view', $order->user->user_id]) : '' ?></td>
                    <td><?= $this->Number->format($order->total_amount) ?></td>
                    <td><?= h($order->payment_method) ?></td>
                    <td><?= h($order->order_status) ?></td>
                    <td><?= h($order->order_date) ?></td>
                    <td><?= $this->Number->format($order->is_deleted) ?></td>
                    <td><?= h($order->created_at) ?></td>
                    <td><?= h($order->updated_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $order->order_id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $order->order_id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $order->order_id], ['confirm' => __('Are you sure you want to delete # {0}?', $order->order_id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>