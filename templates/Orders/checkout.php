<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 * @var \App\Model\Entity\Cart $cart
 * @var \App\Model\Entity\User|null $user
 * @var float $total
 * @var string|null $pendingId
 * @var float $shippingFee
 */

$this->assign('title', __('Checkout'));

use Cake\Core\Configure;

$googleMapsApiKey = Configure::read('GoogleMaps.key');

/** @var \App\Model\Entity\ArtworkVariantCart $cartItem */
$subtotal = array_reduce(
    $cart->artwork_variant_carts,
    fn($sum, $cartItem) => $sum + ($cartItem->artwork_variant->price * $cartItem->quantity),
    0.0,
);
$totalCost = $subtotal + $shippingFee;
?>
<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <?= $this->element('page_title', ['title' => 'Checkout']) ?>

    <?= $this->Form->create($order, [
        'url' => ['action' => 'placeOrder'],
        'class' => 'space-y-8',
        'type' => 'post',
        'id' => 'checkout-form',
    ]) ?>
    <?php if (!empty($pendingId)) : ?>
        <?= $this->Form->hidden('order_id', ['value' => $pendingId]) ?>
    <?php endif; ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- LEFT COLUMN: Checkout Fields -->
        <div class="lg:col-span-2 bg-white shadow-lg rounded-lg p-8 space-y-8">
            <!-- Billing Details -->
            <section>
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Billing Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?= $this->Form->control('billing_first_name', [
                        'label' => 'First Name *',
                        'value' => $order->billing_first_name ?? ($user ? $user->first_name : ''),
                        'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                        'required' => true,
                        'pattern' => "^[a-zA-Z '\\-]+$",
                        'title' => 'First name should only contain letters, spaces, apostrophes, and hyphens.',
                    ]) ?>
                    <?= $this->Form->control('billing_last_name', [
                        'label' => 'Last Name *',
                        'value' => $order->billing_last_name ?? ($user ? $user->last_name : ''),
                        'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                        'required' => true,
                        'pattern' => "^[a-zA-Z '\\-]+$",
                        'title' => 'Last name should only contain letters, spaces, apostrophes, and hyphens.',
                    ]) ?>
                </div>
                <?= $this->Form->control('billing_company', [
                    'label' => 'Company Name (optional)',
                    'value' => $order->billing_company ?? '',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => false,
                ]) ?>
                <?= $this->Form->control('billing_email', [
                    'label' => 'Email Address *',
                    'value' => $order->billing_email ?? ($user ? $user->email : ''),
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
                    'value' => $order->shipping_country ?? 'AU',
                ]) ?>

                <!-- Address Lookup - Raw HTML approach -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="address-lookup">Find Address *</label>
                    <!-- This is a raw HTML input that won't be part of the form submission -->
                    <input type="text" id="address-lookup" placeholder="Start typing your address..."
                           class="border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           autocomplete="off">
                    <p class="text-sm text-gray-500 mt-1">
                        Type your address to autocomplete or fill in the fields manually below
                    </p>
                </div>

                <?= $this->Form->control('shipping_address1', [
                    'label' => 'Street address *',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => true,
                    'placeholder' => 'House number and street name',
                    'value' => $order->shipping_address1 ?? '',
                    'id' => 'shipping_address1',
                ]) ?>
                <?= $this->Form->control('shipping_address2', [
                    'label' => '',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 mt-4 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => false,
                    'placeholder' => 'Apartment, suite, unit, etc. (optional)',
                    'value' => $order->shipping_address2 ?? '',
                    'id' => 'shipping_address2',
                ]) ?>
                <?= $this->Form->control('shipping_suburb', [
                    'label' => 'Suburb *',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => true,
                    'id' => 'shipping_suburb',
                    'value' => $order->shipping_suburb ?? '',
                ]) ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                    <?= $this->Form->control('shipping_state', [
                        'label' => 'State *',
                        'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                        'required' => true,
                        'type' => 'select',
                        'id' => 'shipping_state',
                        'options' => [
                            '' => 'Select State',
                            'ACT' => 'Australian Capital Territory',
                            'NSW' => 'New South Wales',
                            'NT' => 'Northern Territory',
                            'QLD' => 'Queensland',
                            'SA' => 'South Australia',
                            'TAS' => 'Tasmania',
                            'VIC' => 'Victoria',
                            'WA' => 'Western Australia',
                        ],
                        'empty' => false,
                        'value' => $order->shipping_state ?? '',
                    ]) ?>
                    <?= $this->Form->control('shipping_postcode', [
                        'label' => 'Postcode *',
                        'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                        'required' => true,
                        'id' => 'shipping_postcode',
                        'pattern' => '^[0-9]{4}$',
                        'title' => 'Please enter a valid 4-digit postal code.',
                        'value' => $order->shipping_postcode ?? '',
                    ]) ?>
                </div>
                <?= $this->Form->control('shipping_phone', [
                    'label' => 'Phone *',
                    'value' => $order->shipping_phone ?? ($user ? $user->phone_number : ''),
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'required' => true,
                    'pattern' => '^[0-9\\-\\+\\(\\) ]+$',
                    'title' => 'Please enter a valid phone number.',
                ]) ?>
            </section>

            <!-- Order Notes -->
            <section>
                <?= $this->Form->control('order_notes', [
                    'label' => 'Additional Order Notes (optional)',
                    'class' => 'border border-gray-300 rounded-md w-full px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400',
                    'type' => 'textarea',
                    'placeholder' => 'Enter any special instructions or notes here...',
                    'value' => $order->order_notes ?? '',
                ]) ?>
            </section>
        </div>

        <!-- RIGHT COLUMN: Order Summary -->
        <div class="self-start bg-white shadow-lg rounded-lg p-6 max-w-md w-full mx-auto lg:mx-0">
            <!-- Header with Order Summary Title and Edit Link -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Order Summary</h2>
                <?= $this->Html->link('Edit Cart', ['controller' => 'Carts', 'action' => 'index'], [
                    'class' => 'text-indigo-600 hover:text-indigo-800 font-semibold text-lg',
                ]) ?>
            </div>
            <!-- Order Items & Totals -->
            <div>
                <?php if (!empty($cart->artwork_variant_carts)) : ?>
                    <div class="space-y-4">
                        <?php foreach ($cart->artwork_variant_carts as $item) :
                            $variant = $item->artwork_variant;
                            $artwork = $variant->artwork;
                            $lineTotal = $variant->price * (float)$item->quantity;
                            ?>
                            <div class="flex items-center space-x-4 border-b pb-4">
                                <?= $this->Html->image($artwork->image_url, [
                                    'alt' => $artwork->title,
                                    'class' => 'w-16 h-16 object-cover rounded',
                                ]) ?>
                                <div class="flex-1">
                                    <p class="font-semibold text-lg text-gray-900">
                                        <?= h($artwork->title) ?>
                                        <span class="text-sm text-gray-500"> (<?= h($variant->dimension) ?>)</span>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Quantity: <?= h($item->quantity) ?>
                                    </p>
                                </div>
                                <div class="font-semibold text-lg text-gray-900">
                                    $<?= number_format($lineTotal, 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Totals -->
                    <div class="mt-6 border-t pt-4">
                        <p class="text-gray-700 text-lg">Subtotal: $<span id="subtotal"><?= number_format($subtotal, 2) ?></span></p>
                        <p class="text-gray-700 text-lg">Shipping: $<span id="shipping-fee"><?= number_format($shippingFee, 2) ?></span></p>
                        <hr class="my-3">
                        <p class="text-2xl font-bold text-gray-900">Total: $<span id="total"><?= number_format($totalCost, 2) ?></span></p>
                    </div>
                <?php else : ?>
                    <p>Your cart is empty.</p>
                <?php endif; ?>
            </div>
            <!-- Place Order Button in the Card Footer -->
            <div class="mt-6">
                <?= $this->Form->button('Proceed to Payment', [
                    'class' => 'w-full inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-8 rounded-md transition-colors duration-200 text-xl',
                    'type' => 'submit',
                ]) ?>
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<!-- Load Google Maps API separately from the main DOM -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?= h($googleMapsApiKey) ?>&libraries=places" defer></script>

<!-- Initialize Google Places in a separate script tag -->
<script>
    document.addEventListener('DOMContentLoaded', function (callback) {
        // Wait for Google Maps API to load
        function checkGoogleMapsLoaded() {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.places !== 'undefined') {
                initPlacesAutocomplete();
            } else {
                setTimeout(checkGoogleMapsLoaded, 100);
            }
        }

        checkGoogleMapsLoaded();

        function initPlacesAutocomplete(callback) {
            const addressInput = document.getElementById('address-lookup');
            if (!addressInput) return;

            // Make sure Enter key doesn't submit the form
            addressInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    return false;
                }
            });

            // Create autocomplete with Australia restriction
            const options = {
                componentRestrictions: {country: 'au'},
                fields: ['address_components', 'formatted_address'],
                types: ['address']
            };

            try {
                const autocomplete = new google.maps.places.Autocomplete(addressInput, options);

                // When an address is selected
                autocomplete.addListener('place_changed', function () {
                    var place = autocomplete.getPlace();

                    if (!place.address_components) {
                        console.log('No address components found');
                        return;
                    }

                    let streetNumber = '';
                    let streetName = '';
                    let suburb = '';
                    let state = '';
                    let postcode = '';

                    // Extract address components
                    for (let i = 0; i < place.address_components.length; i++) {
                        const component = place.address_components[i];
                        const types = component.types;

                        if (types.indexOf('street_number') !== -1) {
                            streetNumber = component.long_name;
                        } else if (types.indexOf('route') !== -1) {
                            streetName = component.long_name;
                        } else if (types.indexOf('locality') !== -1 || types.indexOf('sublocality_level_1') !== -1) {
                            suburb = component.long_name;
                        } else if (types.indexOf('administrative_area_level_1') !== -1) {
                            state = component.short_name;
                        } else if (types.indexOf('postal_code') !== -1) {
                            postcode = component.long_name;
                        }
                    }

                    // Update form fields
                    document.getElementById('shipping_address1').value = (streetNumber + ' ' + streetName).trim();
                    document.getElementById('shipping_suburb').value = suburb;
                    document.getElementById('shipping_postcode').value = postcode;

                    // Update state dropdown
                    const stateSelect = document.getElementById('shipping_state');
                    for (let j = 0; j < stateSelect.options.length; j++) {
                        if (stateSelect.options[j].value === state) {
                            stateSelect.selectedIndex = j;
                            break;
                        }
                    }
                    // Trigger change event to recalculate shipping fee
                    stateSelect.dispatchEvent(new Event('change'));
                });
            } catch (err) {
                console.error('Google Places Autocomplete error:', err);
            }
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countryEl = document.getElementById('shipping-country');
    const stateEl = document.getElementById('shipping_state');
    const shippingEl = document.getElementById('shipping-fee');
    const totalEl = document.getElementById('total');
    const ORDER_SUBTOTAL = <?= json_encode($subtotal) ?>;

    function updateShippingFee() {
        const country = countryEl.value;
        const state = stateEl.value;
        if (!country || !state) {
            return;
        }
        const endpoint = '<?= $this->Url->build(['controller' => 'Orders', 'action' => 'shippingFee']) ?>';
        fetch(`${endpoint}?shipping_country=${encodeURIComponent(country)}&shipping_state=${encodeURIComponent(state)}`)
            .then(response => response.json())
            .then(data => {
                const fee = parseFloat(data.shippingFee);
                shippingEl.textContent = fee.toFixed(2);
                totalEl.textContent = (ORDER_SUBTOTAL + fee).toFixed(2);
            })
            .catch(error => console.error('Error fetching shipping fee:', error));
    }

    countryEl.addEventListener('change', updateShippingFee);
    stateEl.addEventListener('change', updateShippingFee);
});
</script>
