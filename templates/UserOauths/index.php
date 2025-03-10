<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\UserOauth> $userOauths
 */
?>
<div class="userOauths index content">
    <?= $this->Html->link(__('New User Oauth'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('User Oauths') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('oauth_id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('provider') ?></th>
                    <th><?= $this->Paginator->sort('provider_user_id') ?></th>
                    <th><?= $this->Paginator->sort('token_expires_at') ?></th>
                    <th><?= $this->Paginator->sort('is_deleted') ?></th>
                    <th><?= $this->Paginator->sort('created_at') ?></th>
                    <th><?= $this->Paginator->sort('updated_at') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userOauths as $userOauth): ?>
                <tr>
                    <td><?= h($userOauth->oauth_id) ?></td>
                    <td><?= $userOauth->hasValue('user') ? $this->Html->link($userOauth->user->first_name, ['controller' => 'Users', 'action' => 'view', $userOauth->user->user_id]) : '' ?></td>
                    <td><?= h($userOauth->provider) ?></td>
                    <td><?= h($userOauth->provider_user_id) ?></td>
                    <td><?= h($userOauth->token_expires_at) ?></td>
                    <td><?= $this->Number->format($userOauth->is_deleted) ?></td>
                    <td><?= h($userOauth->created_at) ?></td>
                    <td><?= h($userOauth->updated_at) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $userOauth->oauth_id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $userOauth->oauth_id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $userOauth->oauth_id], ['confirm' => __('Are you sure you want to delete # {0}?', $userOauth->oauth_id)]) ?>
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