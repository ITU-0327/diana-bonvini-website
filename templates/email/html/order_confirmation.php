<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 * @var string $customerName
 * @var string $orderDate
 * @var string $shippingAddress
 * @var string $deliveryMethod
 * @var string $estimatedDelivery
 */
?>

<div style="font-family: Arial, sans-serif; max-width:600px; margin:0 auto; color:#333;">
    <header style="text-align:center; padding:20px 0; border-bottom:2px solid #008080;">
        <h1 style="color:#008080; margin:0;">Diana Bonvini</h1>
        <p style="color:#9370DB; margin:5px 0;">Art &amp; Writing Services</p>
    </header>

    <section style="padding:20px;">
        <h2 style="margin-top:0;">Order Confirmation</h2>
        <p>Dear <?= h($customerName) ?>,</p>
        <p>Thank you for your order! We're processing it and will update you once it's shipped.</p>

        <aside style="background:#f9f9f9; padding:15px; margin:20px 0; border-radius:5px;">
            <h3 style="color:#008080; margin-top:0;">Order Summary</h3>
            <ul style="list-style:none; padding:0; margin:0;">
                <li><strong>Order #:</strong> <?= h($order->order_id) ?></li>
                <li><strong>Date:</strong> <?= h($orderDate) ?></li>
                <li><strong>Payment Method:</strong> <?= h(ucwords($order->payment->payment_method)) ?></li>
                <li><strong>Delivery Method:</strong> <?= h($deliveryMethod) ?></li>
                <li><strong>Estimated Delivery:</strong> <?= h($estimatedDelivery) ?></li>
            </ul>
        </aside>

        <h3 style="color:#008080;">Details</h3>
        <?php if (!empty($order->artwork_orders)) : ?>
            <?php foreach ($order->artwork_orders as $item) : ?>
                <div style="display:flex; padding:10px 0; border-bottom:1px solid #eee;">
                    <div style="flex:0 0 80px;">
                        <?php if (!empty($item->image_url)) : ?>
                            <img src="<?= h($item->image_url) ?>" alt="<?= h($item->artwork->title) ?>" style="width:80px; height:auto; border:1px solid #ddd; object-fit:cover;">
                        <?php else : ?>
                            <div style="width:80px; height:80px; background:#f5f5f5; border:1px solid #ddd; text-align:center; line-height:80px; color:#aaa;">No Image</div>
                        <?php endif ?>
                    </div>
                    <div style="flex:1; padding-left:15px;">
                        <p style="margin:0 0 5px; font-weight:bold;"><?= h($item->artwork->title) ?></p>
                        <?php if (!empty($item['dimensions'])) : ?>
                            <p style="margin:0 0 5px; color:#666;">Dim: <?= h($item['dimensions']) ?></p>
                        <?php endif ?>
                        <p style="margin:0 0 5px; color:#666;">Qty: <?= h($item->quantity) ?></p>
                        <p style="margin:0; color:#666;">Unit: $<?= number_format($item->price, 2) ?></p>
                    </div>
                    <div style="flex:0 0 100px; text-align:right;">
                        <p style="margin:0; font-weight:bold;">$<?= number_format($item->subtotal, 2) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <table style="width:100%; margin-top:15px;">
                <tr>
                    <td style="text-align:right;">Subtotal:</td>
                    <td style="text-align:right; width:100px;">$<?= number_format($order->total_amount, 2) ?></td>
                </tr>
                <?php if (!empty($order->shipping_cost)) : ?>
                    <tr>
                        <td style="text-align:right;">Shipping:</td>
                        <td style="text-align:right;">$<?= number_format($order->shipping_cost, 2) ?></td>
                    </tr>
                <?php endif ?>
                <?php if (!empty($order->tax)) : ?>
                    <tr>
                        <td style="text-align:right;">Tax:</td>
                        <td style="text-align:right;">$<?= number_format($order->tax, 2) ?></td>
                    </tr>
                <?php endif ?>
                <tr>
                    <td style="text-align:right; font-weight:bold; border-top:2px solid #eee; padding-top:10px;">Total:</td>
                    <td style="text-align:right; font-weight:bold; border-top:2px solid #eee; padding-top:10px;">$<?= number_format($order->total_amount, 2) ?></td>
                </tr>
            </table>
        <?php else : ?>
            <p>No items found in this order.</p>
        <?php endif ?>

        <?php if (!empty($shippingAddress)) : ?>
            <h3 style="color:#008080; margin-top:20px;">Shipping Address</h3>
            <p style="white-space:pre-line; margin:0 0 20px; color:#666;"><?= h($shippingAddress) ?></p>
        <?php endif ?>

        <p>If you have any questions, contact us at <a href="mailto:contact@dianabonvini.com" style="color:#008080;">contact@dianabonvini.com</a>.</p>
        <p>Best regards,<br>Diana Bonvini</p>
    </section>

    <footer style="text-align:center; padding:15px 0; background:#f9f9f9; border-top:2px solid #008080; color:#666; font-size:14px;">
        <p>&copy; <?= date('Y') ?> Diana Bonvini. All rights reserved.</p>
        <p><a href="https://www.dianabonvini.com" style="color:#008080; text-decoration:none;">Website</a> | <a href="https://www.dianabonvini.com/contact" style="color:#008080; text-decoration:none;">Contact</a></p>
    </footer>
</div>
