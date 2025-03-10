<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UserOauth $userOauth
 * @var \Cake\Collection\CollectionInterface|string[] $users
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List User Oauths'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="userOauths form content">
            <?= $this->Form->create($userOauth) ?>
            <fieldset>
                <legend><?= __('Add User Oauth') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('provider');
                    echo $this->Form->control('provider_user_id');
                    echo $this->Form->control('access_token');
                    echo $this->Form->control('refresh_token');
                    echo $this->Form->control('token_expires_at', ['empty' => true]);
                    echo $this->Form->control('is_deleted');
                    echo $this->Form->control('created_at');
                    echo $this->Form->control('updated_at');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
