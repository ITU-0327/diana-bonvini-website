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
            <h1 class="text-4xl font-bold text-indigo-600">Diana Bonvini</h1>
        </div>
        <h3 class="text-xl font-semibold mb-4 text-center">Register</h3>
        <?= $this->Flash->render() ?>
        <?= $this->Form->create($user) ?>
        <!-- First Name Field -->
        <div class="mb-4">
            <?= $this->Form->control('first_name', [
                'required' => true,
                'class' => 'border rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'placeholder' => 'Enter your first name',
                'label' => ['text' => 'First Name', 'class' => 'block text-gray-700 mb-1'],
            ]) ?>
        </div>
        <!-- Last Name Field -->
        <div class="mb-4">
            <?= $this->Form->control('last_name', [
                'required' => true,
                'class' => 'border rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'placeholder' => 'Enter your last name',
                'label' => ['text' => 'Last Name', 'class' => 'block text-gray-700 mb-1'],
            ]) ?>
        </div>
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
        <!-- Confirm Password Field -->
        <div class="mb-4">
            <?= $this->Form->control('password_confirm', [
                'label' => 'Confirm Password',
                'required' => true,
                'type' => 'password',
                'class' => 'border rounded w-full px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'placeholder' => 'Confirm your password',
            ]) ?>
        </div>
        <!-- Submit Button -->
        <div>
            <?= $this->Form->button(__('Register'), [
                'class' => 'w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700',
            ]) ?>
        </div>
        <?= $this->Form->end() ?>

        <!-- Login Link -->
        <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">Already have an account?</span>
            <?= $this->Html->link('Login', ['action' => 'login'], [
                'class' => 'text-indigo-600 hover:text-indigo-500 text-sm ml-1',
            ]) ?>
        </div>
    </div>
</div>
