<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Writing Service Request'), ['action' => 'edit', $writingServiceRequest->request_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Writing Service Request'), ['action' => 'delete', $writingServiceRequest->request_id], ['confirm' => __('Are you sure you want to delete # {0}?', $writingServiceRequest->request_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Writing Service Requests'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Writing Service Request'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="writingServiceRequests view content">
            <h3><?= h($writingServiceRequest->service_type) ?></h3>
            <table>
                <tr>
                    <th><?= __('Request Id') ?></th>
                    <td><?= h($writingServiceRequest->request_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $writingServiceRequest->hasValue('user') ? $this->Html->link($writingServiceRequest->user->first_name, ['controller' => 'Users', 'action' => 'view', $writingServiceRequest->user->user_id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Service Type') ?></th>
                    <td><?= h($writingServiceRequest->service_type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Word Count Range') ?></th>
                    <td><?= h($writingServiceRequest->word_count_range) ?></td>
                </tr>
                <tr>
                    <th><?= __('Notes') ?></th>
                    <td><?= h($writingServiceRequest->notes) ?></td>
                </tr>
                <tr>
                    <th><?= __('Request Status') ?></th>
                    <td><?= h($writingServiceRequest->request_status) ?></td>
                </tr>
                <tr>
                    <th><?= __('Estimated Price') ?></th>
                    <td><?= $writingServiceRequest->estimated_price === null ? '' : $this->Number->format($writingServiceRequest->estimated_price) ?></td>
                </tr>
                <tr>
                    <th><?= __('Final Price') ?></th>
                    <td><?= $writingServiceRequest->final_price === null ? '' : $this->Number->format($writingServiceRequest->final_price) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Deleted') ?></th>
                    <td><?= $this->Number->format($writingServiceRequest->is_deleted) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created At') ?></th>
                    <td><?= h($writingServiceRequest->created_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Updated At') ?></th>
                    <td><?= h($writingServiceRequest->updated_at) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>