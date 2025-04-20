<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */
?>
<div class="bg-gray-100 min-h-screen pb-8">
    <!-- Success Message Banner -->
    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 mb-6">
        <p class="text-center font-medium">Your payment was successful! Your order has been placed.</p>
    </div>

    <div class="container max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Order Confirmation</h1>
            <p class="mt-3 text-lg text-gray-700">
                Your order has been placed successfully. Thank you for your order!
            </p>
        </div>

        <!-- Confirmation Card -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Order Details -->
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Order Details</h2>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Order ID:</span>
                        <?= h($order->order_id) ?>
                    </p>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Order Date:</span>
                        <?= $order->order_date->format('F j, Y') ?>
                    </p>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Total Amount:</span>
                        $<?= number_format($order->total_amount, 2) ?>
                    </p>
                    <p class="mb-2 text-base">
                        <span class="font-bold">Status:</span>
                        <span class="text-green-600 font-semibold">
                            Confirmed
                        </span>
                    </p>
                </div>

                <!-- Payment Information -->
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Payment Information</h2>
                    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-3">
                        <p class="text-green-700 font-semibold mb-1">Payment Completed</p>
                        <?php
                        // Determine payment method (card, Apple Pay, etc.)
                        $paymentMethod = 'Credit Card';
                        $paymentDetails = '';

                        if (!empty($order->payments)) {
                            foreach ($order->payments as $payment) {
                                if (!empty($payment->payment_details)) {
                                    $details = json_decode($payment->payment_details, true);
                                    if (!empty($details['payment_method_details']['type'])) {
                                        $methodType = $details['payment_method_details']['type'];
                                        switch ($methodType) {
                                            case 'card':
                                                $brand = $details['payment_method_details']['card']['brand'] ?? 'Credit Card';
                                                $last4 = $details['payment_method_details']['card']['last4'] ?? '****';
                                                $paymentMethod = ucfirst($brand);
                                                $paymentDetails = '•••• ' . $last4;
                                                break;
                                            case 'paypal':
                                                $paymentMethod = 'PayPal';
                                                break;
                                            case 'apple_pay':
                                                $paymentMethod = 'Apple Pay';
                                                break;
                                            case 'google_pay':
                                                $paymentMethod = 'Google Pay';
                                                break;
                                            default:
                                                $paymentMethod = ucfirst($methodType);
                                        }
                                    }
                                    break; // Use the first payment with details
                                }
                            }
                        } elseif (!empty($_GET['payment_method'])) {
                            // Fallback to query parameter if available (you can pass this from Stripe redirect)
                            $paymentMethod = h($_GET['payment_method']);
                        }
                        ?>

                        <div class="flex items-center mt-2">
                            <?php if (strtolower($paymentMethod) === 'visa') : ?>
                                <svg class="w-8 h-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="24" height="24" rx="4" fill="#1A1F71"/>
                                    <path d="M9.5 15.5H7L8.5 8.5H11L9.5 15.5Z" fill="white"/>
                                    <path d="M16.5 8.75C15.8333 8.5 14.9 8.3 14 8.5C12.5 8.5 11 9.5 11 11C11 12.1667 12.1 12.8333 13 13C13.8333 13.1667 14 13.5 14 13.5C14 14 13 14 12.5 14C11.8333 14 11.3333 13.8333 10.5 13.5L10 15.5C10.6667 15.8333 11.6 16 12.5 16C14.1667 16 15.5 15 15.5 13.5C15.5 12 14.5 11.5 13.5 11.5C13 11.3333 12.5 11 12.5 10.5C12.5 10.1 13 10 13.5 10C14.1667 10 14.8333 10.1667 15.5 10.5L16.5 8.75Z" fill="white"/>
                                    <path d="M17 15.5L18.5 8.5H20.5L19 15.5H17Z" fill="white"/>
                                    <path d="M3.5 8.5L3 10L6 15.5H8L11.5 8.5H9.5L7 13L6 8.5H3.5Z" fill="white"/>
                                </svg>
                            <?php elseif (strtolower($paymentMethod) === 'mastercard') : ?>
                                <svg class="w-8 h-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="8" cy="12" r="6" fill="#EB001B"/>
                                    <circle cx="16" cy="12" r="6" fill="#F79E1B"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 16.8C13.8075 15.4317 15 13.1313 15 10.5C15 7.86866 13.8075 5.56834 12 4.2C10.1925 5.56834 9 7.86866 9 10.5C9 13.1313 10.1925 15.4317 12 16.8Z" fill="#FF5F00"/>
                                </svg>
                            <?php elseif (strtolower($paymentMethod) === 'apple pay') : ?>
                                <svg class="w-8 h-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="24" height="24" rx="4" fill="#000000"/>
                                    <path d="M7.5 14.5C7.1 13.7 6.8 12.9 6.8 12C6.8 11.1 7.1 10.3 7.5 9.5C7.9 8.8 8.4 8.2 9.2 8C9.6 7.9 10 8 10.4 8.2C10.6 8.3 10.9 8.5 11.2 8.5C11.5 8.5 11.8 8.3 12 8.2C12.3 8.1 12.7 8 13 8C13.5 8 14 8.2 14.3 8.4C14 9.2 13.2 9.7 12.5 9.7C12 9.7 11.5 9.5 11.2 9.4C11 9.3 10.7 9.2 10.4 9.2C10.1 9.2 9.8 9.3 9.6 9.4C9.2 9.6 9 9.9 8.8 10.2C8.6 10.7 8.5 11.4 8.5 12C8.5 12.6 8.6 13.3 8.8 13.8C9 14.1 9.2 14.4 9.6 14.6C9.8 14.7 10.1 14.8 10.4 14.8C10.7 14.8 11 14.7 11.2 14.6C11.5 14.5 12 14.3 12.5 14.3C13.2 14.3 14 14.8 14.3 15.6C14 15.8 13.5 16 13 16C12.7 16 12.3 15.9 12 15.8C11.8 15.7 11.5 15.5 11.2 15.5C10.9 15.5 10.6 15.7 10.4 15.8C10 16 9.6 16.1 9.2 16C8.4 15.8 7.9 15.2 7.5 14.5Z" fill="white"/>
                                    <path d="M13.6 7.5C13.4 7.8 13.2 8 12.8 8.2C12.4 8.4 12 8.5 11.6 8.4C11.5 8 11.7 7.6 11.9 7.3C12.1 7 12.4 6.8 12.8 6.6C13.2 6.4 13.6 6.3 14 6.4C14 6.8 13.8 7.2 13.6 7.5Z" fill="white"/>
                                </svg>
                            <?php elseif (strtolower($paymentMethod) === 'google pay') : ?>
                                <svg class="w-8 h-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="24" height="24" rx="4" fill="#FFFFFF"/>
                                    <path d="M12 11V13H17.5C17.3 14.3 16.7 15.4 15.9 16.2C15 17.1 13.7 17.6 12 17.6C9.1 17.6 6.8 15.3 6.8 12.4C6.8 9.5 9.1 7.2 12 7.2C13.4 7.2 14.6 7.7 15.5 8.5L16.9 7.1C15.7 6 14 5.2 12 5.2C8 5.2 4.9 8.3 4.9 12.3C4.9 16.3 8 19.4 12 19.4C14 19.4 15.7 18.7 17 17.4C18.3 16.1 19 14.3 19 12.2C19 11.7 19 11.4 18.9 11H12Z" fill="#4285F4"/>
                                </svg>
                            <?php elseif (strtolower($paymentMethod) === 'paypal') : ?>
                                <svg class="w-8 h-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="24" height="24" rx="4" fill="#FFFFFF"/>
                                    <path d="M19.5 9.5C19.5 7.5 18 6 15.5 6H9.5C9 6 8.5 6.5 8.5 7L7 16C7 16.5 7.5 17 8 17H10.5L11 15H11L11.5 12.5H14C17 12.5 19.5 10.5 19.5 9.5Z" fill="#009CDE"/>
                                    <path d="M8.5 16L10 7H15.5C16.5 7 17.5 7.5 17.5 8.5C17.5 10.5 15.5 11.5 13.5 11.5H11.5L10.5 16H8.5Z" fill="#012169"/>
                                </svg>
                            <?php else : ?>
                                <svg class="w-8 h-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="24" height="24" rx="4" fill="#6B7280"/>
                                    <path d="M4 9C4 7.89543 4.89543 7 6 7H18C19.1046 7 20 7.89543 20 9V15C20 16.1046 19.1046 17 18 17H6C4.89543 17 4 16.1046 4 15V9Z" stroke="white" stroke-width="2"/>
                                    <path d="M7 13H10" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            <?php endif; ?>

                            <div>
                                <p class="text-gray-700"><?= h($paymentMethod) ?> <?= h($paymentDetails) ?></p>
                                <p class="text-sm text-gray-600">
                                    <?= date('F j, Y') ?>
                                </p>
                            </div>
                        </div>

                        <?php if (!empty($order->payments) && !empty($order->payments[0]->transaction_id)) : ?>
                            <p class="text-xs text-gray-500 mt-2">
                                Transaction ID: <?= substr($order->payments[0]->transaction_id, 0, 12) ?>...
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <hr class="my-6 border-gray-300">

            <!-- Shipping Information -->
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Shipping Information</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="mb-1"><span class="font-semibold">Name:</span> <?= h($order->billing_first_name) ?> <?= h($order->billing_last_name) ?></p>
                    <p class="mb-1"><span class="font-semibold">Address:</span> <?= h($order->shipping_address1) ?></p>
                    <?php if (!empty($order->shipping_address2)) : ?>
                        <p class="mb-1"><span class="font-semibold"></span> <?= h($order->shipping_address2) ?></p>
                    <?php endif; ?>
                    <p class="mb-1"><?= h($order->shipping_suburb) ?>, <?= h($order->shipping_state) ?> <?= h($order->shipping_postcode) ?></p>
                    <p class="mb-1"><?= h($order->shipping_country === 'AU' ? 'Australia' : $order->shipping_country) ?></p>
                    <p class="mb-1"><span class="font-semibold">Phone:</span> <?= h($order->shipping_phone) ?></p>
                </div>
            </div>

        <!-- Items Ordered -->
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Items Ordered</h2>
            <?php if (!empty($order->artwork_orders)) : ?>
                <ul class="space-y-4">
                    <?php foreach ($order->artwork_orders as $item) : ?>
                        <li class="flex items-center justify-between border-b pb-3">
                            <div class="flex items-center">
                                <?= $this->Html->image(
                                    $item->artwork->image_url,
                                    ['alt' => $item->artwork->title, 'class' => 'w-16 h-16 object-cover rounded-lg mr-4'],
                                ) ?>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg"><?= h($item->artwork->title) ?></p>
                                    <p class="text-gray-600 text-sm">Qty: <?= h($item->quantity) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 text-lg">$<?= number_format($item->price * $item->quantity, 2) ?></p>
                                    <p class="text-gray-600 text-sm">($<?= number_format($item->price, 2) ?> each)</p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Order Totals -->
                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-between mb-2">
                            <span class="font-medium">Subtotal:</span>
                            <span>$<?= number_format($order->total_amount, 2) ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="font-medium">Shipping:</span>
                            <span>$0.00</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-2 mt-2">
                            <span>Total:</span>
                            <span>$<?= number_format($order->total_amount, 2) ?></span>
                        </div>
                    </div>
                <?php else : ?>
                    <p class="text-gray-600 text-base">No items found.</p>
                <?php endif; ?>
            </div>

            <!-- Call to Action -->
            <div class="mt-8 text-center">
                <?= $this->Html->link(
                    'Continue Shopping',
                    ['controller' => 'Artworks', 'action' => 'index'],
                    ['class' => 'inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-md transition-colors duration-200'],
                ) ?>
            </div>
        </div>
    </div>
</div>
