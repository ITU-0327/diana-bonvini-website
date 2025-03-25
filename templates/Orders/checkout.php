<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 * @var \App\Model\Entity\Cart $cart
 * @var \App\Model\Entity\User|null $user
 * @var float $total
 */
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
                ]) ?>
                <?= $this->Form->control('shipping_address1', [
                    'label' => 'Street address * (House number and street name)',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'required' => true,
                ]) ?>
                <?= $this->Form->control('shipping_address2', [
                    'label' => 'Apartment, suite, unit, etc. (optional)',
                    'class' => 'border rounded w-full px-4 py-3 focus:ring-2 focus:ring-indigo-500',
                    'required' => false,
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
                        'id' => 'stateField',
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
    document.getElementById('suburbField').addEventListener('blur', function() {
        const suburb = this.value.trim();
        if (!suburb) return;
        // Placeholder: Replace with an actual API call
        // Example:
        // fetch('/api/lookup?suburb=' + encodeURIComponent(suburb))
        //   .then(response => response.json())
        //   .then(data => {
        //       document.getElementById('postcodeField').value = data.postcode;
        //       document.getElementById('stateField').value = data.state;
        //   });
    });
</script>
