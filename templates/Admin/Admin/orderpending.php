<?php
/**
 * Pending Orders View Template
 *
 * @var \App\View\AppView $this
 * @var \Cake\Collection\CollectionInterface $orders
 */
?>

<div class="orders-pending">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pending Orders</h5>
            <div class="actions">
                <?= $this->Html->link(
                    '<i class="fas fa-plus"></i> New Order',
                    ['action' => 'add'],
                    ['class' => 'btn btn-sm btn-primary', 'escape' => false],
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-list"></i> All Orders',
                    ['action' => 'index'],
                    ['class' => 'btn btn-sm btn-secondary', 'escape' => false],
                ) ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($orders->toArray())) : ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th><?= $this->Paginator->sort('id', 'Order ID') ?></th>
                            <th><?= $this->Paginator->sort('customer_name', 'Customer') ?></th>
                            <th><?= $this->Paginator->sort('email', 'Email') ?></th>
                            <th><?= $this->Paginator->sort('total', 'Total') ?></th>
                            <th><?= $this->Paginator->sort('created', 'Order Date') ?></th>
                            <th><?= $this->Paginator->sort('status', 'Status') ?></th>
                            <th class="actions">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order) : ?>
                            <tr>
                                <td><?= $this->Html->link(h($order->id), ['action' => 'view', $order->id]) ?></td>
                                <td><?= h($order->customer_name) ?></td>
                                <td><?= h($order->email) ?></td>
                                <td><?= $this->Number->currency($order->total) ?></td>
                                <td><?= h($order->created->format('M d, Y H:i')) ?></td>
                                <td>
                                    <span class="badge bg-warning">Pending</span>
                                </td>
                                <td class="actions">
                                    <div class="btn-group btn-group-sm">
                                        <?= $this->Html->link(
                                            '<i class="fas fa-eye"></i>',
                                            ['action' => 'view', $order->id],
                                            ['class' => 'btn btn-info', 'escape' => false, 'title' => 'View', 'data-toggle' => 'tooltip'],
                                        ) ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-edit"></i>',
                                            ['action' => 'edit', $order->id],
                                            ['class' => 'btn btn-primary', 'escape' => false, 'title' => 'Edit', 'data-toggle' => 'tooltip'],
                                        ) ?>
                                        <?= $this->Form->postLink(
                                            '<i class="fas fa-check"></i>',
                                            ['action' => 'markAsProcessing', $order->id],
                                            [
                                                'class' => 'btn btn-success',
                                                'escape' => false,
                                                'title' => 'Mark as Processing',
                                                'data-toggle' => 'tooltip',
                                                'confirm' => 'Are you sure you want to mark this order as processing?',
                                            ],
                                        ) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="paginator">
                    <ul class="pagination justify-content-center mt-4">
                        <?= $this->Paginator->first('<< First', ['class' => 'page-link']) ?>
                        <?= $this->Paginator->prev('< Previous', ['class' => 'page-link']) ?>
                        <?= $this->Paginator->numbers(['before' => '', 'after' => '', 'class' => 'page-link']) ?>
                        <?= $this->Paginator->next('Next >', ['class' => 'page-link']) ?>
                        <?= $this->Paginator->last('Last >>', ['class' => 'page-link']) ?>
                    </ul>
                    <p class="text-center"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
                </div>
            <?php else : ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No pending orders found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
