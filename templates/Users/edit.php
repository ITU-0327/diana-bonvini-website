<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<!-- Add Alpine.js for tab switching -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div x-data="{ tab: 'personal' }" class="flex flex-col md:flex-row gap-6">
    <!-- Side Nav -->
    <aside class="w-full md:w-1/4">
        <div class="bg-white shadow rounded p-4 space-y-2">
            <h4 class="text-xl font-bold mb-4"><?= __('Actions') ?></h4>

            <?= $this->Html->link(__('Go Back'), ['action' => 'view', $user->user_id], [
                'class' => 'block text-red-600 hover:underline',
            ]) ?>

            <?= $this->Html->link(__('Personal Information'), '#', [
                '@click' => "tab = 'personal'",
                'class' => 'block text-blue-600 hover:underline cursor-pointer',
            ]) ?>

            <?= $this->Html->link(__('Address Information'), '#', [
                '@click' => "tab = 'address'",
                'class' => 'block text-blue-600 hover:underline cursor-pointer',
            ]) ?>

            <?= $this->Html->link(__('Security'), '#', [
                '@click' => "tab = 'security'",
                'class' => 'block text-blue-600 hover:underline cursor-pointer',
            ]) ?>
        </div>
    </aside>

    <!-- Main Form -->
    <div class="w-full md:w-3/4">
        <?= $this->Form->create($user, ['class' => 'space-y-6']) ?>

        <!-- Personal Info -->
        <div x-show="tab === 'personal'" class="bg-white shadow rounded p-6">
            <h2 class="text-xl font-semibold mb-4 text-center">Personal Information</h2>
            <div class="space-y-6 max-w-md mx-auto">

                <?= $this->Form->control('first_name', [
                    'label' => 'First Name',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

                <?= $this->Form->control('last_name', [
                    'label' => 'Last Name',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

                <?= $this->Form->control('email', [
                    'label' => 'Email',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

                <?= $this->Form->control('phone_number', [
                    'label' => 'Phone Number',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

            </div>
        </div>

        <!-- Address Info -->
        <div x-show="tab === 'address'" x-cloak class="bg-white shadow rounded p-6">
            <h2 class="text-xl font-semibold mb-4 text-center">Address Information</h2>
            <div class="space-y-6 max-w-md mx-auto">

                <?= $this->Form->control('street_address', [
                    'label' => 'Street Address',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

                <?= $this->Form->control('street_address2', [
                    'label' => 'Street Address 2',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

                <?= $this->Form->control('suburb', [
                    'label' => 'Suburb',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

                <?= $this->Form->control('state', [
                    'label' => 'State',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

                <?= $this->Form->control('postcode', [
                    'label' => 'Postcode',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

                <?= $this->Form->control('country', [
                    'label' => 'Country',
                    'class' => 'form-input w-full border rounded px-3 py-2',
                ]) ?>

            </div>
        </div>

        <!-- Security -->
        <div x-show="tab === 'security'" x-cloak class="bg-white shadow rounded p-6" x-data="{ show1: false, show2: false }">
            <h2 class="text-xl font-semibold mb-4">Security</h2>
            <div class="space-y-6">

                <!-- New Password -->
                <div class="relative max-w-md mx-auto">
                    <?= $this->Form->label('password', 'New Password', ['class' => 'block mb-1']) ?>
                    <input :type="show1 ? 'text' : 'password'" name="password" id="password"
                           class="form-input w-full pr-10 border rounded px-3 py-2" />
                    <button type="button" @click="show1 = !show1"
                            class="absolute right-2 top-9 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <!-- Eye Icon -->
                        <svg x-show="!show1" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Eye Off Icon -->
                        <svg x-show="show1" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.27-2.944-9.543-7a9.978 9.978 0 012.034-3.338M15 12a3 3 0 01-3 3m0 0a3 3 0 01-3-3m6 0a3 3 0 00-3-3m0 0a3 3 0 00-3 3m12 0a9.978 9.978 0 00-1.332-3.338M21 21L3 3" />
                        </svg>
                    </button>
                </div>

                <!-- Confirm Password -->
                <div class="relative max-w-md mx-auto">
                    <?= $this->Form->label('password_confirm', 'Confirm Password', ['class' => 'block mb-1']) ?>
                    <input :type="show2 ? 'text' : 'password'" name="password_confirm" id="password-confirm"
                           class="form-input w-full pr-10 border rounded px-3 py-2" />
                    <button type="button" @click="show2 = !show2"
                            class="absolute right-2 top-9 text-gray-500 hover:text-gray-700 focus:outline-none">
                        <!-- Eye Icon -->
                        <svg x-show="!show2" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Eye Off Icon -->
                        <svg x-show="show2" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.27-2.944-9.543-7a9.978 9.978 0 012.034-3.338M15 12a3 3 0 01-3 3m0 0a3 3 0 01-3-3m6 0a3 3 0 00-3-3m0 0a3 3 0 00-3 3m12 0a9.978 9.978 0 00-1.332-3.338M21 21L3 3" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Save Button -->
        <div class="text-center">
            <button
                type="submit"
                :name="tab === 'security' ? 'password_submit' : 'info_submit'"
                class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700"
            >
                Save
            </button>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>
