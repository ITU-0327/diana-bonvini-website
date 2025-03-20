<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\WritingServiceRequest> $writingServiceRequests
 */
?>

<!-- Ask for Another Requests Button -->
<div class="container mt-4 text-center">
    <?= $this->Html->link(
        'Submit another writing service request',
        ['controller' => 'WritingServiceRequests', 'action' => 'add'],
        ['class' => 'btn btn-outline-secondary px-4']
    ) ?>
</div>

<div class="writingServiceRequests index content">
    <?= $this->Html->link(__('New Writing Service Request'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Writing Service Requests') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('request_id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('service_type') ?></th>
                    <th><?= $this->Paginator->sort('word_count_range') ?></th>
                    <th><?= $this->Paginator->sort('notes') ?></th>
                    <th><?= $this->Paginator->sort('estimated_price') ?></th>
                    <th><?= $this->Paginator->sort('final_price') ?></th>
                    <th><?= $this->Paginator->sort('request_status') ?></th>
                    <th><?= $this->Paginator->sort('is_deleted') ?></th>
                    <th><?= $this->Paginator->sort('created_at') ?></th>
                    <th><?= $this->Paginator->sort('updated_at') ?></th>

                    <th><?= __('Document') ?></th>

                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($writingServiceRequests as $writingServiceRequest): ?>
                    <tr>
                        <td><?= h($writingServiceRequest->request_id) ?></td>
                        <td><?= $writingServiceRequest->hasValue('user') ? $this->Html->link($writingServiceRequest->user->first_name, ['controller' => 'Users', 'action' => 'view', $writingServiceRequest->user->user_id]) : '' ?></td>
                        <td><?= h($writingServiceRequest->service_type) ?></td>
                        <td><?= h($writingServiceRequest->word_count_range) ?></td>
                        <td><?= h($writingServiceRequest->notes) ?></td>
                        <td><?= $writingServiceRequest->estimated_price === null ? '' : $this->Number->format($writingServiceRequest->estimated_price) ?></td>
                        <td><?= $writingServiceRequest->final_price === null ? '' : $this->Number->format($writingServiceRequest->final_price) ?></td>
                        <td><?= h($writingServiceRequest->request_status) ?></td>
                        <td><?= $this->Number->format($writingServiceRequest->is_deleted) ?></td>
                        <td><?= h($writingServiceRequest->created_at) ?></td>
                        <td><?= h($writingServiceRequest->updated_at) ?></td>

                        <td>
                            <?php if (!empty($writingServiceRequest->document)): ?>
                                <?= $this->Html->link('View Document', '/' . $writingServiceRequest->document, ['target' => '_blank']) ?>
                            <?php else: ?>
                                <span>No Document</span>
                            <?php endif; ?>
                        </td>

                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $writingServiceRequest->request_id]) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $writingServiceRequest->request_id]) ?>
                            <?= $this->Form->postLink(
                                __('Delete'),
                                ['action' => 'delete', $writingServiceRequest->request_id],
                                [
                                    'method' => 'delete',
                                    'confirm' => __('Are you sure you want to delete # {0}?', $writingServiceRequest->request_id),
                                ]
                            ) ?>
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
