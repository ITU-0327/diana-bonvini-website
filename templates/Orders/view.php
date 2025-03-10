<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Order'), ['action' => 'edit', $order->order_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Order'), ['action' => 'delete', $order->order_id], ['confirm' => __('Are you sure you want to delete # {0}?', $order->order_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Orders'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Order'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="orders view content">
            <h3><?= h($order->payment_method) ?></h3>
            <table>
                <tr>
                    <th><?= __('Order Id') ?></th>
                    <td><?= h($order->order_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $order->hasValue('user') ? $this->Html->link($order->user->first_name, ['controller' => 'Users', 'action' => 'view', $order->user->user_id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Payment Method') ?></th>
                    <td><?= h($order->payment_method) ?></td>
                </tr>
                <tr>
                    <th><?= __('Order Status') ?></th>
                    <td><?= h($order->order_status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Total Amount') ?></th>
                    <td><?= $this->Number->format($order->total_amount) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Deleted') ?></th>
                    <td><?= $this->Number->format($order->is_deleted) ?></td>
                </tr>
                <tr>
                    <th><?= __('Order Date') ?></th>
                    <td><?= h($order->order_date) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created At') ?></th>
                    <td><?= h($order->created_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Updated At') ?></th>
                    <td><?= h($order->updated_at) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>