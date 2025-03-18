<?php
/**
 * @var \App\View\AppView $this
 */
?>

<!-- File: templates/Users/reset_password.php -->
<h1>Reset Your Password</h1>

<?= $this->Form->create($user) ?>
    <?= $this->Form->control('password', [
        'label' => 'New Password'
    ]) ?>
    <?= $this->Form->control('password_confirm', [
        'type' => 'password',
        'label' => 'Confirm New Password'
    ]) ?>
    <?= $this->Form->button('Update Password') ?>
<?= $this->Form->end() ?>
