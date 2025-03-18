<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<!-- File: templates/Users/forgot_password.php -->
<h1>Forgot Your Password?</h1>
<p>Please enter your email address to reset your password.</p>

<?= $this->Form->create(null, ['url' => ['action' => 'forgotPassword']]) ?>
<?= $this->Form->control('email', [
    'label' => 'Email Address',
    'required' => true
]) ?>
<?= $this->Form->button('Send Reset Link') ?>
<?= $this->Form->end() ?>

