<!-- src/Template/Email/text/order_confirmation.ctp -->
DIANA BONVINI
Art & Writing Services

ORDER CONFIRMATION

Dear <?= $customer_name ?? 'Valued Customer' ?>,

Thank you for your order! We're delighted to confirm that your order has been received and is being processed.

ORDER SUMMARY
-------------
Order Number: #<?= $order_number ?? 'N/A' ?>
Order Date: <?= $order_date ?? date('F j, Y') ?>
Payment Method: <?= $payment_method ?? 'N/A' ?>
Delivery Method: <?= $delivery_method ?? 'Standard Shipping' ?>
Estimated Delivery: <?= $estimated_delivery ?? 'To be determined' ?>

<?php if (isset($orderItems) && !empty($orderItems)) : ?>
    ORDER DETAILS
    -------------
    <?php foreach ($orderItems as $item) : ?>
        Item: <?= $item->artwork->title ?? ($item->product->name ?? 'Product') ?>
        Quantity: <?= $item->quantity ?? '1' ?>
        Price: $<?= isset($item->price) ? number_format($item->price, 2) : (isset($item->unit_price) ? number_format($item->unit_price, 2) : '0.00') ?>
        Subtotal: $<?= isset($item->subtotal) ? number_format($item->subtotal, 2) : (isset($item->price) && isset($item->quantity) ? number_format($item->price * $item->quantity, 2) : '0.00') ?>

    <?php endforeach; ?>

    SUMMARY
    -------
    <?php
    $subtotal = 0;
    foreach ($orderItems as $item) {
        $itemPrice = $item->price ?? ($item->unit_price ?? 0);
        $itemQty = $item->quantity ?? 1;
        $subtotal += $itemPrice * $itemQty;
    }
    ?>
    Subtotal: $<?= number_format($subtotal, 2) ?>
    <?php if (isset($order->shipping_cost) && $order->shipping_cost > 0) : ?>
        Shipping: $<?= number_format($order->shipping_cost, 2) ?>
    <?php endif; ?>
    <?php if (isset($order->tax) && $order->tax > 0) : ?>
        Tax: $<?= number_format($order->tax, 2) ?>
    <?php endif; ?>
    TOTAL: $<?= isset($order_total) ? number_format($order_total, 2) : number_format($subtotal + ($order->shipping_cost ?? 0) + ($order->tax ?? 0), 2) ?>
<?php else : ?>
    Your order details are being processed.
<?php endif; ?>

<?php if (isset($shipping_address) && !empty($shipping_address)) : ?>
    SHIPPING ADDRESS
    ----------------
    <?= $shipping_address ?>

<?php endif; ?>

If you have any questions about your order, please don't hesitate to contact us at contact@dianabonvini.com.

Thank you for supporting my art and writing services!

Warm regards,
Diana Bonvini

© <?= date('Y') ?> Diana Bonvini. All rights reserved.
Website: https://www.dianabonvini.com | Contact: https://www.dianabonvini.com/contact
