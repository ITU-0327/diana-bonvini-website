<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="w-full flex items-center justify-center" style="min-height: calc(90vh - 120px);">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <!-- Logo Placeholder -->
        <div class="mb-6 text-center">
            <div class="flex items-center justify-center gap-3">
                <?= $this->Html->image('logo.png', ['class' => 'h-14 w-auto']) ?>
                <span class="text-4xl font-bold text-gray-800"><?= $this->ContentBlock->text('logo') ?></span>
            </div>
        </div>

        <div class="flash-messages-container">
            <?= $this->Flash->render() ?>
        </div>

        <h2 class="text-xl font-semibold mb-4">Reset Your Password</h2>
        <p class="mb-4">Enter a new password and confirm it below.</p>

        <?= $this->Form->create($user, ['onsubmit' => 'this.querySelector("button[type=submit]").disabled = true;']) ?>
        <div class="mb-4">
            <?= $this->Form->control('password', [
                'label' => ['text' => 'New Password', 'class' => 'block text-gray-700 mb-1'],
                'class' => 'border rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'placeholder' => 'Enter your new password',
            ]) ?>
        </div>
        <div class="mb-4">
            <?= $this->Form->control('password_confirm', [
                'type' => 'password',
                'label' => ['text' => 'Confirm New Password', 'class' => 'block text-gray-700 mb-1'],
                'class' => 'border rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'placeholder' => 'Confirm your new password',
            ]) ?>
        </div>
        <?= $this->Form->button('Update Password', [
            'class' => 'w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700',
        ]) ?>
        <?= $this->Form->end() ?>

        <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">Go back to</span>
            <?= $this->Html->link('Login', ['action' => 'login'], [
                'class' => 'text-indigo-600 hover:text-indigo-500 text-sm ml-1',
            ]) ?>
        </div>
    </div>
</div>
