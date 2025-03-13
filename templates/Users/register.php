<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>

<div class="users form">
    <?= $this->Flash->render() ?>
    <h3>Register</h3>
    <?= $this->Form->create($user) ?>
    <fieldset>
        <?= $this->Form->control('first_name', ['required' => true]) ?>
        <?= $this->Form->control('last_name', ['required' => true]) ?>
        <?= $this->Form->control('email', ['required' => true]) ?>
        <?= $this->Form->control('password', ['required' => true]) ?>
        <?= $this->Form->control('password_confirm', [
            'label' => 'Confirm Password',
            'required' => true,
            'type' => 'password',
        ]) ?>
    </fieldset>
    <?= $this->Form->submit(__('Register')); ?>
    <?= $this->Form->end() ?>

    <?= $this->Html->link('Login', ['action' => 'login']) ?>
</div>
