<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 * @var \App\Model\Entity\Cart $cart
 * @var \App\Model\Entity\User|null $user
 * @var float $total
 */

use Cake\Core\Configure;

$googleMapsApiKey = Configure::read('GoogleMaps.key');
?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Checkout</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- LEFT COLUMN: Checkout Form (spanning 2 columns) -->
        <div class="md:col-span-2 bg-white p-6 rounded shadow">
            <?= $this->Form->create($order, ['url' => ['action' => 'placeOrder'], 'class' => 'space-y-6']) ?>

            <!-- Billing Details -->
            <section>
                <h2 class="text-2xl font-semibold mb-4">Billing Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <?= $this->Form->control('billing_first_name', [
                        'label' => 'First Name *',
                        'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                        'required' => true,
                    ]) ?>
                    <?= $this->Form->control('billing_last_name', [
                        'label' => 'Last Name *',
                        'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                        'required' => true,
                    ]) ?>
                </div>
                <?= $this->Form->control('billing_company', [
                    'label' => 'Company Name (optional)',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'required' => false,
                ]) ?>
                <?= $this->Form->control('billing_email', [
                    'label' => 'Email Address *',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'type' => 'email',
                    'required' => true,
                    'value' => $user ? $user->email : '',
                ]) ?>
            </section>

            <!-- Shipping Information -->
            <section>
                <h2 class="text-2xl font-semibold mb-4">Shipping Information</h2>
                <?= $this->Form->control('shipping_country', [
                    'label' => 'Country/Region *',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'required' => true,
                    'id' => 'shipping-country',
                    'options' => [
                        '' => 'Select Country',
                        'AU' => 'Australia',
                    ],
                ]) ?>
                <?= $this->Form->control('shipping_address1', [
                    'label' => 'Street address *',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'required' => true,
                    'placeholder' => 'House number and street name',
                ]) ?>
                <?= $this->Form->control('shipping_address2', [
                    'label' => '',
                    'class' => 'border rounded w-full px-4 py-3 mt-4 focus:ring-2 focus:ring-indigo-500',
                    'required' => false,
                    'placeholder' => 'Apartment, suite, unit, etc. (optional)',
                ]) ?>
                <?= $this->Form->control('shipping_suburb', [
                    'label' => 'Suburb *',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'required' => true,
                    'id' => 'suburbField',
                ]) ?>
                <div class="grid grid-cols-2 gap-4">
                    <?= $this->Form->control('shipping_state', [
                        'label' => 'State *',
                        'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                        'required' => true,
                        'type' => 'select', // Change the type to 'select'
                        'id' => 'stateField', // Keep the ID for JavaScript targeting
                        'empty' => 'Select Country First', // Initial empty value
                        'disabled' => true, // Initially disabled until a country is selected
                    ]) ?>
                    <?= $this->Form->control('shipping_postcode', [
                        'label' => 'Postcode *',
                        'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                        'required' => true,
                        'id' => 'postcodeField',
                    ]) ?>
                </div>
                <?= $this->Form->control('shipping_phone', [
                    'label' => 'Phone *',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'required' => true,
                    'value' => $user ? $user->phone_number : '',
                ]) ?>
            </section>

            <!-- Payment Method (Bank Transfer) -->
            <section>
                <h2 class="text-2xl font-semibold mb-4">Payment Method</h2>
                <p class="text-gray-700 text-lg mb-3">
                    We accept bank transfers. Please transfer the total amount to our bank account. Your order will be confirmed once payment is received.
                </p>
                <div class="p-4 bg-gray-100 rounded space-y-2 text-lg">
                    <p><strong>Bank Name:</strong> Example Bank</p>
                    <p><strong>Account Number:</strong> 123456789</p>
                    <p><strong>Account Name:</strong> Diana Bonvini Art &amp; Writing</p>
                </div>
            </section>

            <!-- Order Notes -->
            <section>
                <?= $this->Form->control('order_notes', [
                    'label' => 'Additional Order Notes (optional)',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'type' => 'textarea',
                    'placeholder' => 'Enter any special instructions or notes here...',
                ]) ?>
            </section>

            <!-- Submit Button -->
            <div class="mt-8">
                <?= $this->Form->button('Place Order', [
                    'class' => 'w-full md:w-auto bg-indigo-600 text-white py-3 px-6 rounded hover:bg-indigo-700 text-xl',
                ]) ?>
            </div>

            <?= $this->Form->end() ?>
        </div>

        <!-- RIGHT COLUMN: Order Summary (Editable via 'Edit Cart' link) -->
        <div class="bg-white p-6 rounded shadow max-w-md mx-auto md:mx-0">
            <h2 class="text-2xl font-semibold mb-4">Order Summary</h2>
            <?php if (!empty($cart->artwork_carts)) : ?>
                <div class="space-y-4">
                    <?php foreach ($cart->artwork_carts as $item) : ?>
                        <?php if (isset($item->artwork)) : ?>
                            <div class="flex items-center space-x-4 border-b pb-4">
                                <?= $this->Html->image($item->artwork->image_path, [
                                    'alt' => $item->artwork->title,
                                    'class' => 'w-16 h-16 object-cover rounded',
                                ]) ?>
                                <div class="flex-1">
                                    <p class="font-semibold text-lg"><?= h($item->artwork->title) ?></p>
                                    <p class="text-sm text-gray-600">Quantity: <?= h($item->quantity) ?></p>
                                </div>
                                <div class="font-semibold text-lg">
                                    $<?= number_format($item->artwork->price * $item->quantity, 2) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <!-- Totals -->
                <div class="mt-4 text-right border-t pt-4">
                    <?php
                    $subtotal = 0;
                    foreach ($cart->artwork_carts as $ci) {
                        if (isset($ci->artwork)) {
                            $subtotal += $ci->artwork->price * $ci->quantity;
                        }
                    }
                    // Placeholder values; update as needed.
                    $shippingCost = 0.00;
                    $tax = 0.00;
                    $totalCost = $subtotal + $shippingCost + $tax;
                    ?>
                    <p class="text-gray-700 text-lg">Subtotal: $<?= number_format($subtotal, 2) ?></p>
                    <p class="text-gray-700 text-lg">Shipping: $<?= number_format($shippingCost, 2) ?></p>
                    <p class="text-gray-700 text-lg">Tax: $<?= number_format($tax, 2) ?></p>
                    <hr class="my-2">
                    <p class="text-2xl font-bold">Total: $<?= number_format($totalCost, 2) ?></p>
                </div>
                <!-- Edit Cart Link -->
                <div class="mt-6 text-center">
                    <?= $this->Html->link('Edit Cart', ['controller' => 'Carts', 'action' => 'index'], [
                        'class' => 'text-indigo-600 hover:text-indigo-800 font-semibold text-lg',
                    ]) ?>
                </div>
            <?php else : ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for Auto-fill Postcode/State (Placeholder) -->
<script>
    let countryField;
    let suburbField;
    let stateField;
    let postcodeField;

    function initAutocomplete() {
        countryField = document.getElementById('shipping-country');
        suburbField = document.getElementById('suburbField');
        stateField = document.getElementById('stateField');
        postcodeField = document.getElementById('postcodeField');

        // --- Functionality for Country-Specific States (Initial Dropdown) ---
        const statesByCountry = {
            'AU': [
                { value: '', text: 'Select State' },
                { value: 'ACT', text: 'Australian Capital Territory' },
                { value: 'NSW', text: 'New South Wales' },
                { value: 'NT', text: 'Northern Territory' },
                { value: 'QLD', text: 'Queensland' },
                { value: 'SA', text: 'South Australia' },
                { value: 'TAS', text: 'Tasmania' },
                { value: 'VIC', text: 'Victoria' },
                { value: 'WA', text: 'Western Australia' },
            ],
        };

        if (countryField) {
            countryField.addEventListener('change', function() {
                const selectedCountry = this.value;
                const stateDropdown = document.getElementById('stateField');

                if (stateDropdown) {
                    stateDropdown.innerHTML = '';

                    if (statesByCountry[selectedCountry]) {
                        statesByCountry[selectedCountry].forEach(state => {
                            const option = document.createElement('option');
                            option.value = state.value;
                            option.textContent = state.text;
                            stateDropdown.appendChild(option);
                        });
                        stateDropdown.disabled = false;
                    } else {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Select Country First';
                        stateDropdown.appendChild(option);
                        stateDropdown.disabled = true;
                    }
                } else {
                    console.error('State dropdown element not found.');
                }
            });
        }

        // --- Functionality for Postcode Autofill after Suburb Input ---
        suburbField.addEventListener('blur', function() {
            const suburb = this.value;
            const state = document.getElementById('stateField').value;
            const country = document.getElementById('shipping-country').value;

            if (suburb && state && country) {
                const geocoder = new google.maps.Geocoder();
                const address = `${suburb}, ${state}, ${country}`;

                geocoder.geocode({ 'address': address }, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            let postcode = null;
                            for (const component of results[0].address_components) {
                                if (component.types.includes('postal_code')) {
                                    postcode = component.long_name;
                                    break;
                                }
                            }
                            if (postcode) {
                                postcodeField.value = postcode;
                            }
                        }
                    } else {
                        console.error('Geocode was not successful for the following reason: ' + status);
                    }
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=<?= h($googleMapsApiKey) ?>&libraries=geocoding&callback=initAutocomplete`;
        script.async = true;
        document.head.appendChild(script);
    });
</script>
