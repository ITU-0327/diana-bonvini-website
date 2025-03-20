<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="w-full flex items-center justify-center" style="min-height: calc(90vh - 120px);">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <!-- Logo Placeholder -->
        <div class="mb-6 text-center">
            <h1 class="text-4xl font-bold text-indigo-600">Diana Bonvini</h1>
        </div>
        <?= $this->Flash->render() ?>
        <?= $this->Form->create() ?>
        <!-- Email Field -->
        <div class="mb-4">
            <?= $this->Form->control('email', [
                'required' => true,
                'class' => 'border rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'placeholder' => 'Enter your email',
                'label' => ['text' => 'Email', 'class' => 'block text-gray-700 mb-1'],
            ]) ?>
        </div>
        <!-- Password Field -->
        <div class="mb-4">
            <?= $this->Form->control('password', [
                'required' => true,
                'class' => 'border rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'placeholder' => 'Enter your password',
                'label' => ['text' => 'Password', 'class' => 'block text-gray-700 mb-1'],
            ]) ?>
        </div>
        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mb-4">
            <label class="inline-flex items-center text-sm text-gray-600">
                <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-500" name="remember" />
                <span class="ml-2">Remember me</span>
            </label>
            <?= $this->Html->link(
                'Forgot password?',
                ['controller' => 'Users', 'action' => 'forgotPassword'],
                ['class' => 'text-sm text-indigo-600 hover:text-indigo-500']
            ) ?>
        </div>
        <!-- Submit Button -->
        <div>
            <?= $this->Form->button(__('Login'), [
                'class' => 'w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700',
            ]) ?>
        </div>
        <?= $this->Form->end() ?>

        <!-- Register Link -->
        <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">Don't have an account?</span>
            <?= $this->Html->link('Register', ['action' => 'register'], [
                'class' => 'text-indigo-600 hover:text-indigo-500 text-sm ml-1',
            ]) ?>
        </div>
    </div>
</div>
