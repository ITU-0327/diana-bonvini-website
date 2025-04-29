<?php
/**
 * @var \App\View\AppView $this
 * @var string $email
 */
$this->assign('title', __('Verification Required'));
?>

<div class="users form content">
    <div class="column-responsive column-80">
        <div class="users form">
            <h3><?= __('Verification Required') ?></h3>
            <p><?= __('For your security, we sent a verification code to:') ?> <strong><?= h($email) ?></strong></p>
            
            <?= $this->Form->create(null) ?>
            <fieldset>
                <?= $this->Form->control('verification_code', [
                    'label' => __('Enter Verification Code'),
                    'type' => 'text',
                    'autofocus' => true,
                    'required' => true,
                    'class' => 'form-control',
                    'maxlength' => 6,
                    'placeholder' => '6-digit code',
                ]) ?>
                <?= $this->Form->control('trust_device', [
                    'label' => __('Trust this device for 30 days'),
                    'type' => 'checkbox',
                ]) ?>
            </fieldset>
            <?= $this->Form->button(__('Verify')); ?>
            <?= $this->Form->end() ?>
            
            <div class="mt-4">
                <p><?= __("Didn't receive a code?") ?></p>
                <?= $this->Form->create(null, ['url' => ['action' => 'resendCode']]) ?>
                <?= $this->Form->button(__('Resend Code'), ['class' => 'button-outline']) ?>
                <?= $this->Form->end() ?>
            </div>
            
            <div class="mt-4">
                <p><?= __("Having trouble?") ?></p>
                <p><?= __("Contact support or ") ?><?= $this->Html->link(__('return to login'), ['controller' => 'Users', 'action' => 'login']) ?></p>
            </div>
        </div>
    </div>
</div>