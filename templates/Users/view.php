<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<!-- Using flex layout -->
<div class="flex flex-col md:flex-row">
    <!-- Sidebar section -->
    <aside class="w-full md:w-1/4 p-4">
        <div class="bg-white p-4 shadow rounded">
            <h4 class="text-xl font-bold mb-4"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->user_id], [
                'class' => 'block text-blue-500 hover:underline mb-2'
            ]) ?>
        </div>
    </aside>
    <!-- Main Content Section -->
    <div class="w-full md:w-3/4 p-4">
        <div class="bg-white p-6 shadow rounded">
            <h3 class="text-2xl font-bold mb-4">User Profile</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500"><?= __('First Name') ?></th>
                    <td class="px-6 py-3 text-sm text-gray-900"><?= h($user->first_name) ?></td>
                </tr>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500"><?= __('Last Name') ?></th>
                    <td class="px-6 py-3 text-sm text-gray-900"><?= h($user->last_name) ?></td>
                </tr>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500"><?= __('Email') ?></th>
                    <td class="px-6 py-3 text-sm text-gray-900"><?= h($user->email) ?></td>
                </tr>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500"><?= __('Phone Number') ?></th>
                    <td class="px-6 py-3 text-sm text-gray-900"><?= h($user->phone_number) ?></td>
                </tr>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500"><?= __('Last Login') ?></th>
                    <td class="px-6 py-3 text-sm text-gray-900"><?= h($user->last_login) ?></td>
                </tr>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500"><?= __('Address') ?></th>
                    <td class="px-6 py-3 text-sm text-gray-900">
                        <?= h($user->street_address) ?>
                        <?php if (!empty($user->suburb) || !empty($user->state) || !empty($user->postcode)): ?>
                            , <?= trim(h($user->suburb . ' ' . $user->state . ' ' . $user->postcode)) ?>
                        <?php endif; ?>
                        <?= $user->country ? ', ' . h($user->country) : '' ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
