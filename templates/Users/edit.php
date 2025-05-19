<?php
/**
 * User Edit View — two cards + external submit button
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */

use Cake\Core\Configure;

$this->assign('title', 'Edit User');
$this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css', ['block' => true]);
$this->Html->script('https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js', ['block' => true]);
$this->Html->script('https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js', ['block' => true]);
?>

<div class="max-w-4xl mx-auto space-y-8">

    <!-- 1. Begin the form. Give it an explicit ID so an external button can reference it -->
    <?= $this->Form->create($user, [
        'id' => 'user-edit-form',
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
                'type' => 'tel',
                'id' => 'phone_number',
                'label' => [
                    'text' => 'Phone Number',
                    'class' => 'block mb-2',
                ],
                'class' => 'form-input w-full border rounded px-3 py-2',
                'placeholder' => '+61 412 345 677',
            ]) ?>
        </div>
    </div>

    <!-- ——— Address Information Card ——— -->
    <div class="bg-white shadow rounded p-6">
        <h2 class="text-xl font-semibold mb-6 text-center">Address Information</h2>

        <div class="space-y-6 max-w-md mx-auto">

            <?= $this->Form->control('street_address', [
                'label' => 'Street Address *',
                'id' => 'address-lookup',
                'placeholder' => 'Start typing your address…',
                'class' => 'form-input w-full border rounded px-3 py-2',
            ]) ?>
            <?= $this->Form->control('street_address2', [
                'label' => 'Street Address 2',
                'id' => 'street_address2',
                'class' => 'form-input w-full border rounded px-3 py-2',
            ]) ?>
            <?= $this->Form->control('suburb', [
                'label' => 'Suburb',
                'id' => 'shipping_suburb',
                'class' => 'form-input w-full border rounded px-3 py-2',
            ]) ?>
            <?= $this->Form->control('state', [
                'label' => 'State',
                'id' => 'shipping_state',
                'class' => 'form-input w-full border rounded px-3 py-2',
            ]) ?>
            <?= $this->Form->control('postcode', [
                'label' => 'Postcode',
                'id' => 'shipping_postcode',
                'class' => 'form-input w-full border rounded px-3 py-2',
            ]) ?>
            <?= $this->Form->control('country', [
                'label' => 'Country',
                'id' => 'shipping_country',
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
                'url' => ['action' => 'changePassword'],
                'type' => 'post',
                'class' => 'max-w-xs mx-auto',
            ],
        ) ?>

        <!-- optional hidden fields if your action needs them -->
        <?= $this->Form->hidden('email', ['value' => $user->email]) ?>

        <?= $this->Form->button('Send Reset Link', [
            'class' => 'w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700',
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

<script
    src="https://maps.googleapis.com/maps/api/js?key=<?= h(Configure::read('GoogleMaps.key')) ?>&libraries=places"
    async
    defer
></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function init() {
            const input = document.getElementById('address-lookup');
            if (!input || !window.google?.maps?.places) {
                return setTimeout(init, 100);
            }
            const autocomplete = new google.maps.places.Autocomplete(input, {
                fields: ['address_components'],
                types: ['address']
            });
            autocomplete.addListener('place_changed', function () {
                const place = autocomplete.getPlace();
                const map = {};
                (place.address_components || []).forEach(function (c) {
                    c.types.forEach(function (t) {
                        if (t === 'country') {
                            map[t] = c.short_name;   // ISO 2-letter code
                        } else {
                            map[t] = c.long_name;
                        }
                    });
                });
                document.getElementById('address-lookup').value =
                    ((map.street_number || '') + ' ' + (map.route || '')).trim();
                document.getElementById('shipping_suburb').value =
                    map.locality || map.sublocality_level_1 || '';
                document.getElementById('shipping_state').value =
                    map.administrative_area_level_1 || '';
                document.getElementById('shipping_postcode').value =
                    map.postal_code || '';
                document.getElementById('shipping_country').value =
                    map.country || '';
            });
        }
        init();
    });
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.querySelector('#phone_number');
        if (!input || !window.intlTelInput) return;

        const iti = intlTelInput(input, {
            separateDialCode: true,
            nationalMode: false,
            utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
        });

        const form = document.querySelector('#user-edit-form');
        form.addEventListener('submit', function() {
            const full = iti.getNumber();   // e.g. "+61412345677"
            if (full) input.value = full;
        });
    });
</script>
