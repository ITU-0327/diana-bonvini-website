<?php
/**
 * @var \App\View\AppView $this
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

        <h2 class="text-xl font-semibold mb-4">Forgot Your Password?</h2>
        <p class="mb-4">Please enter your email address to reset your password.</p>

        <?= $this->Form->create(null, ['url' => ['action' => 'forgotPassword'], 'onsubmit' => 'this.querySelector("button[type=submit]").disabled = true;']) ?>
        <div class="mb-4">
            <?= $this->Form->control('email', [
                'label' => ['text' => 'Email Address', 'class' => 'block text-gray-700 mb-1'],
                'required' => true,
                'class' => 'border rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'placeholder' => 'Enter your email',
            ]) ?>
        </div>
        <?= $this->Form->button('Send Reset Link', [
            'class' => 'w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700',
        ]) ?>
        <?= $this->Form->end() ?>

        <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">Remembered your password?</span>
            <?= $this->Html->link('Sign In', ['action' => 'login'], [
                'class' => 'text-indigo-600 hover:text-indigo-500 text-sm ml-1',
            ]) ?>
        </div>
    </div>
</div>
