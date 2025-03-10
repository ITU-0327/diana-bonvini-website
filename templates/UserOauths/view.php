<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UserOauth $userOauth
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit User Oauth'), ['action' => 'edit', $userOauth->oauth_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete User Oauth'), ['action' => 'delete', $userOauth->oauth_id], ['confirm' => __('Are you sure you want to delete # {0}?', $userOauth->oauth_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List User Oauths'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New User Oauth'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="userOauths view content">
            <h3><?= h($userOauth->provider) ?></h3>
            <table>
                <tr>
                    <th><?= __('Oauth Id') ?></th>
                    <td><?= h($userOauth->oauth_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $userOauth->hasValue('user') ? $this->Html->link($userOauth->user->first_name, ['controller' => 'Users', 'action' => 'view', $userOauth->user->user_id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Provider') ?></th>
                    <td><?= h($userOauth->provider) ?></td>
                </tr>
                <tr>
                    <th><?= __('Provider User Id') ?></th>
                    <td><?= h($userOauth->provider_user_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Deleted') ?></th>
                    <td><?= $this->Number->format($userOauth->is_deleted) ?></td>
                </tr>
                <tr>
                    <th><?= __('Token Expires At') ?></th>
                    <td><?= h($userOauth->token_expires_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created At') ?></th>
                    <td><?= h($userOauth->created_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Updated At') ?></th>
                    <td><?= h($userOauth->updated_at) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Access Token') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($userOauth->access_token)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Refresh Token') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($userOauth->refresh_token)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>