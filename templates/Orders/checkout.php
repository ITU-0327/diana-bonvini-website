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
<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col items-start mb-8">
        <h1 class="text-3xl uppercase text-gray-800">Checkout</h1>
        <div class="mt-1 w-16 h-[2px] bg-gray-800"></div>
    </div>
    <?= $this->Form->create($order, ['url' => ['action' => 'placeOrder'], 'class' => 'space-y-8']) ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- LEFT COLUMN: Checkout Fields -->
        <div class="lg:col-span-2 bg-white shadow-lg rounded-lg p-8 space-y-8">
            <!-- Billing Details -->
            <section>
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Billing Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?= $this->Form->control('billing_first_name', [
                        'label' => 'First Name *',
                        'value' => $user ? $user->first_name : '',
                        'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                        'required' => true,
                        'pattern' => "^[a-zA-Z '\\-]+$",
                        'title' => 'First name should only contain letters, spaces, apostrophes, and hyphens.',
                    ]) ?>
                    <?= $this->Form->control('billing_last_name', [
                        'label' => 'Last Name *',
                        'value' => $user ? $user->last_name : '',
                        'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                        'required' => true,
                        'pattern' => "^[a-zA-Z '\\-]+$",
                        'title' => 'Last name should only contain letters, spaces, apostrophes, and hyphens.',
                    ]) ?>
                </div>
                <?= $this->Form->control('billing_company', [
                    'label' => 'Company Name (optional)',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => false,
                ]) ?>
                <?= $this->Form->control('billing_email', [
                    'label' => 'Email Address *',
                    'value' => $user ? $user->email : '',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'type' => 'email',
                    'required' => true,
                ]) ?>
            </section>

            <!-- Shipping Information -->
            <section>
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Shipping Information</h2>
                <?= $this->Form->control('shipping_country', [
                    'label' => 'Country/Region *',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => true,
                    'id' => 'shipping-country',
                    'options' => [
                        '' => 'Select Country',
                        'AU' => 'Australia',
                    ],
                ]) ?>
                <?= $this->Form->control('shipping_address1', [
                    'label' => 'Street address *',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => true,
                    'placeholder' => 'House number and street name',
                ]) ?>
                <?= $this->Form->control('shipping_address2', [
                    'label' => '',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 mt-4 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => false,
                    'placeholder' => 'Apartment, suite, unit, etc. (optional)',
                ]) ?>
                <?= $this->Form->control('shipping_suburb', [
                    'label' => 'Suburb *',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => true,
                    'id' => 'suburbField',
                ]) ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                    <?= $this->Form->control('shipping_state', [
                        'label' => 'State *',
                        'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                        'required' => true,
                        'type' => 'select',
                        'id' => 'stateField',
                        'empty' => 'Select Country First',
                        'disabled' => true,
                    ]) ?>
                    <?= $this->Form->control('shipping_postcode', [
                        'label' => 'Postcode *',
                        'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                        'required' => true,
                        'id' => 'postcodeField',
                        'pattern' => '^[0-9]{4}$',
                        'title' => 'Please enter a valid 4-digit postal code.',
                    ]) ?>
                </div>
                <?= $this->Form->control('shipping_phone', [
                    'label' => 'Phone *',
                    'value' => $user ? $user->phone_number : '',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => true,
                    'pattern' => '^[0-9\\-\\+\\(\\) ]+$',
                    'title' => 'Please enter a valid phone number.',
                ]) ?>
            </section>

            <!-- Payment Method -->
            <section>
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Payment Method</h2>
                <p class="text-gray-600 mb-4">
                    We accept bank transfers. Please transfer the total amount to our bank account. Your order will be confirmed once payment is received.
                </p>
                <div class="p-6 bg-gray-100 rounded-lg space-y-3 text-lg">
                    <p><strong>Bank Name:</strong> Commonwealth Bank of Australia</p>
                    <p><strong>BSB:</strong> 062-123</p>
                    <p><strong>Account Number:</strong> 1234 5678</p>
                    <p><strong>Account Name:</strong> Diana Bonvini Art &amp; Writing</p>
                </div>
            </section>

            <!-- Order Notes -->
            <section>
                <?= $this->Form->control('order_notes', [
                    'label' => 'Additional Order Notes (optional)',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'type' => 'textarea',
                    'placeholder' => 'Enter any special instructions or notes here...',
                ]) ?>
            </section>
        </div>

        <!-- RIGHT COLUMN: Order Summary -->
        <div class="bg-white shadow-lg rounded-lg p-6 max-w-md w-full mx-auto lg:mx-0 flex flex-col max-h-[600px]">
            <!-- Header with Order Summary Title and Edit Link -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Order Summary</h2>
                <?= $this->Html->link('Edit Cart', ['controller' => 'Carts', 'action' => 'index'], [
                    'class' => 'text-indigo-600 hover:text-indigo-800 font-semibold text-lg',
                ]) ?>
            </div>
            <!-- Order Items & Totals -->
            <div class="flex-1 overflow-y-auto">
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
                                        <p class="font-semibold text-lg text-gray-900"><?= h($item->artwork->title) ?></p>
                                        <p class="text-sm text-gray-500">Quantity: <?= h($item->quantity) ?></p>
                                    </div>
                                    <div class="font-semibold text-lg text-gray-900">
                                        $<?= number_format($item->artwork->price * $item->quantity, 2) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <!-- Totals -->
                    <div class="mt-6 border-t pt-4">
                        <?php
                        $subtotal = 0;
                        foreach ($cart->artwork_carts as $ci) {
                            if (isset($ci->artwork)) {
                                $subtotal += $ci->artwork->price * $ci->quantity;
                            }
                        }
                        $shippingCost = 0.00;
                        $tax = 0.00;
                        $totalCost = (float)$subtotal + $shippingCost + $tax;
                        ?>
                        <p class="text-gray-700 text-lg">Subtotal: $<?= number_format($subtotal, 2) ?></p>
                        <p class="text-gray-700 text-lg">Shipping: $<?= number_format($shippingCost, 2) ?></p>
                        <p class="text-gray-700 text-lg">Tax: $<?= number_format($tax, 2) ?></p>
                        <hr class="my-3">
                        <p class="text-2xl font-bold text-gray-900">Total: $<?= number_format($totalCost, 2) ?></p>
                    </div>
                <?php else : ?>
                    <p>Your cart is empty.</p>
                <?php endif; ?>
            </div>
            <!-- Place Order Button in the Card Footer -->
            <div class="mt-6">
                <?= $this->Form->button('Proceed to Payment', [
                    'class' => 'w-full inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-8 rounded-md transition-colors duration-200 text-xl',
                ]) ?>
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<!-- JavaScript for Auto-fill Postcode/State -->
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
