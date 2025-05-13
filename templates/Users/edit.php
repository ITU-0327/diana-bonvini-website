<?php
/**
 * User Edit View — two cards + external submit button
 *
 * @var \App\View\AppView   $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="max-w-4xl mx-auto space-y-8">

    <!-- 1. Begin the form. Give it an explicit ID so an external button can reference it -->
    <?= $this->Form->create($user, [
        'id'    => 'user-edit-form',     // <— referenced by the button below
        'class' => 'space-y-8',
    ]) ?>

    <!-- ——— Personal Information Card ——— -->
    <div class="bg-white shadow rounded p-6">
        <h2 class="text-xl font-semibold mb-6 text-center">Personal Information</h2>

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

    <!-- ——— Address Information Card ——— -->
    <div class="bg-white shadow rounded p-6">
        <h2 class="text-xl font-semibold mb-6 text-center">Address Information</h2>

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

    <!-- 2. Close the form BEFORE the external button -->
    <?= $this->Form->end() ?>

    <!-- —— Password Reset Card —— -->
    <div class="bg-white shadow rounded p-6">
        <h2 class="text-xl font-semibold mb-4 text-center">Password</h2>
        <p class="text-gray-600 mb-6 text-center">
            A reset link will be sent to <strong><?= h($user->email) ?></strong>.
        </p>

        <?= $this->Form->create(
            null,
            [
                'url'  => ['action' => 'changePassword'],
                'type' => 'post',
                'class' => 'max-w-xs mx-auto',
            ],
        ) ?>

        <!-- optional hidden fields if your action needs them -->
        <?= $this->Form->hidden('email', ['value' => $user->email]) ?>

        <?= $this->Form->button('Send Reset Link', [
            'class'   => 'w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700',
            'confirm' => 'Send a reset link to ' . h($user->email) . '?',
        ]) ?>

        <?= $this->Form->end() ?>
    </div>

    <!-- 3. External submit button; `form="user-edit-form"` links it back to the form -->
    <div class="text-center">
        <button type="submit" form="user-edit-form"
                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Save All Changes
        </button>
    </div>

</div>
