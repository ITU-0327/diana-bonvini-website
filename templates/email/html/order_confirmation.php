<?php
use Cake\Routing\Router;
?>
<!-- src/Template/Email/html/order_confirmation.ctp -->
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
    <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #008080;">
        <h1 style="color: #008080; margin: 0;">Diana Bonvini</h1>
        <p style="color: #9370DB; margin: 5px 0;">Art & Writing Services</p>
    </div>

    <div style="padding: 20px;">
        <h2>Order Confirmation</h2>
        <p>Dear <?= isset($customer_name) ? h($customer_name) : 'Valued Customer' ?>,</p>
        <p>Thank you for your order! We're delighted to confirm that your order has been received and is being processed.</p>

        <div style="background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h3 style="margin-top: 0; color: #008080;">Order Summary</h3>
            <p><strong>Order Number:</strong> #<?= isset($order_number) ? h($order_number) : 'N/A' ?></p>
            <p><strong>Order Date:</strong> <?= isset($order_date) ? h($order_date) : date('F j, Y') ?></p>
            <p><strong>Payment Method:</strong> <?= isset($payment_method) ? h($payment_method) : 'Credit Card' ?></p>
            <p><strong>Delivery Method:</strong> <?= isset($delivery_method) ? h($delivery_method) : 'Standard Shipping' ?></p>
            <p><strong>Estimated Delivery:</strong> <?= isset($estimated_delivery) ? h($estimated_delivery) : 'To be determined' ?></p>
        </div>

        <h3 style="color: #008080;">Order Details</h3>
        <?php if (isset($orderItems) && !empty($orderItems)) : ?>
            <?php
            $calculatedSubtotal = 0;
            foreach ($orderItems as $item) :
                // Different properties may be available depending on order association structure
                $itemPrice = $item->price ?? (isset($item->artwork) ? $item->artwork->price : 0);
                $itemQuantity = $item->quantity ?? 1;
                $itemSubtotal = $item->subtotal ?? $itemPrice * $itemQuantity;
                $calculatedSubtotal += $itemSubtotal;

                // Get artwork title
                $itemTitle = isset($item->artwork->title) ? h($item->artwork->title) :
                    (isset($item->product->name) ? h($item->product->name) : 'Product');

                // Handle image paths
                $imagePath = null;
                if (isset($item->artwork->embedded_image_cid)) {
                    // Use embedded CID reference if available - this is preferred
                    $imagePath = $item->artwork->embedded_image_cid;
                } elseif (isset($item->artwork->full_image_path)) {
                    // Use full image path if available (absolute URL)
                    $imagePath = $item->artwork->full_image_path;
                } elseif (isset($item->artwork->image_path)) {
                    // Fallback to constructing URL if needed
                    $baseUrl = Router::url('/', true);
                    $imagePath = $baseUrl . h($item->artwork->image_path);
                }
                ?>
                <div style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 80px; vertical-align: top;">
                                <?php if (isset($item->artwork->embedded_image_cid)) : ?>
                                    <!-- Use Content-ID reference for embedded image -->
                                    <img src="<?= h($item->artwork->embedded_image_cid) ?>" alt="<?= h($itemTitle) ?>" style="width: 80px; height: auto; border: 1px solid #ddd; object-fit: cover;">
                                <?php elseif (isset($item->artwork->full_image_path)) : ?>
                                    <!-- Use absolute URL as fallback -->
                                    <img src="<?= h($item->artwork->full_image_path) ?>" alt="<?= h($itemTitle) ?>" style="width: 80px; height: auto; border: 1px solid #ddd; object-fit: cover;">
                                <?php else : ?>
                                    <div style="width: 80px; height: 80px; background-color: #f5f5f5; border: 1px solid #ddd; text-align: center; line-height: 80px; color: #aaa;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td style="padding-left: 15px; vertical-align: top;">
                                <div style="font-weight: bold; margin-bottom: 5px;"><?= $itemTitle ?></div>
                                <?php if (isset($item->artwork->dimensions) && !empty($item->artwork->dimensions)) : ?>
                                    <div style="color: #666; margin-bottom: 5px;">Dimensions: <?= h($item->artwork->dimensions) ?></div>
                                <?php endif; ?>
                                <div style="color: #666; margin-bottom: 5px;">Quantity: <?= h($itemQuantity) ?></div>
                                <div style="color: #666;">Price: $<?= number_format($itemPrice, 2) ?></div>
                            </td>
                            <td style="vertical-align: top; text-align: right; white-space: nowrap;">
                                <div style="font-weight: bold;">$<?= number_format($itemSubtotal, 2) ?></div>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endforeach; ?>

            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <tr>
                    <td style="text-align: right; padding: 5px 0;">Subtotal:</td>
                    <td style="text-align: right; width: 100px; padding: 5px 0;">$<?= number_format($calculatedSubtotal, 2) ?></td>
                </tr>
                <?php if (isset($order->shipping_cost) && $order->shipping_cost > 0) : ?>
                    <tr>
                        <td style="text-align: right; padding: 5px 0;">Shipping:</td>
                        <td style="text-align: right; padding: 5px 0;">$<?= number_format($order->shipping_cost, 2) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (isset($order->tax) && $order->tax > 0) : ?>
                    <tr>
                        <td style="text-align: right; padding: 5px 0;">Tax:</td>
                        <td style="text-align: right; padding: 5px 0;">$<?= number_format($order->tax, 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="text-align: right; padding: 10px 0; font-weight: bold; border-top: 2px solid #eee;">Total:</td>
                    <td style="text-align: right; padding: 10px 0; font-weight: bold; border-top: 2px solid #eee;">$<?= number_format($order_total ?? $calculatedSubtotal + ($order->shipping_cost ?? 0) + ($order->tax ?? 0), 2) ?></td>
                </tr>
            </table>
        <?php else : ?>
            <p>Your order details are being processed.</p>
        <?php endif; ?>

        <?php if (isset($shipping_address) && !empty($shipping_address)) : ?>
            <h3 style="color: #008080;">Shipping Address</h3>
            <p style="margin-bottom: 20px;"><?= nl2br(h($shipping_address)) ?></p>
        <?php endif; ?>

        <p>If you have any questions about your order, please don't hesitate to contact us at
            <a href="mailto:contact@dianabonvini.com" style="color: #008080;">contact@dianabonvini.com</a>.</p>

        <p>Thank you for supporting my art and writing services!</p>

        <p>Warm regards,<br>Diana Bonvini</p>
    </div>

    <div style="text-align: center; padding: 20px; background-color: #f9f9f9; border-top: 2px solid #008080; color: #666; font-size: 14px;">
        <p>&copy; <?= date('Y') ?> Diana Bonvini. All rights reserved.</p>
        <p>
            <a href="https://www.dianabonvini.com" style="color: #008080; text-decoration: none; margin: 0 10px;">Website</a> |
            <a href="https://www.dianabonvini.com/contact" style="color: #008080; text-decoration: none; margin: 0 10px;">Contact</a>
        </p>
    </div>
</div>
