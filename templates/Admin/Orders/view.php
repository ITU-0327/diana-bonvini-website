<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */

$this->assign('title', __('View Order'));
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-receipt mr-2"></i><?= __('Order #') . h($order->order_id) ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Orders'), ['controller' => 'Orders', 'action' => 'index']) ?></li>
                        <li class="breadcrumb-item active"><?= __('View Order') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-4 mb-4">
            <!-- Order Status Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0">Status:</h5>
                        <?php
                        $status = $order->order_status ?? 'pending';
                        $statusClass = match ($status) {
                            'pending' => 'warning',
                            'processing' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'confirmed' => 'primary',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge badge-<?= $statusClass ?> px-3 py-2"><?= ucfirst(h($status)) ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="m-0">Order Date:</h5>
                        <span>
                            <?php if (isset($order->created_at) && $order->created_at) : ?>
                                <?= $order->created_at->format('M d, Y H:i') ?>
                            <?php elseif (isset($order->order_date) && $order->order_date) : ?>
                                <?= $order->order_date->format('M d, Y H:i') ?>
                            <?php else : ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Total Amount:</h5>
                        <span class="fw-bold fs-5">$<?= $this->Number->format($order->total_amount, ['precision' => 2]) ?></span>
                    </div>

                    <hr>

                    <div class="d-grid gap-2 mt-3">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateStatusModal">
                            <i class="fas fa-edit mr-2"></i>Update Status
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customer Information Card -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                </div>
                <div class="card-body">
                    <?php if (isset($order->user) && $order->user) : ?>
                        <h5>User Account</h5>
                        <p>
                            <strong>Name:</strong> <?= h($order->user->first_name . ' ' . $order->user->last_name) ?><br>
                            <strong>Email:</strong> <?= h($order->user->email) ?><br>
                            <?php if (!empty($order->user->phone)) : ?>
                                <strong>Phone:</strong> <?= h($order->user->phone) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <h5>Billing Details</h5>
                    <p>
                        <strong>Name:</strong> <?= h($order->billing_first_name . ' ' . $order->billing_last_name) ?><br>
                        <strong>Email:</strong> <?= h($order->billing_email) ?><br>
                        <?php if (!empty($order->billing_company)) : ?>
                            <strong>Company:</strong> <?= h($order->billing_company) ?>
                        <?php endif; ?>
                    </p>

                    <h5>Shipping Address</h5>
                    <p>
                        <?= h($order->shipping_address1) ?><br>
                        <?php if (!empty($order->shipping_address2)) : ?>
                            <?= h($order->shipping_address2) ?><br>
                        <?php endif; ?>
                        <?= h($order->shipping_suburb . ', ' . $order->shipping_state . ' ' . $order->shipping_postcode) ?><br>
                        <?= h($order->shipping_country) ?><br>
                        <strong>Phone:</strong> <?= h($order->shipping_phone) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-8">
            <!-- Order Items Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($order->artwork_variant_orders) && count($order->artwork_variant_orders) > 0) : ?>
                                    <?php foreach ($order->artwork_variant_orders as $item) : ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (isset($item->artwork_variant) && $item->artwork_variant && isset($item->artwork_variant->artwork)) : ?>
                                                        <?php if (isset($item->artwork_variant->artwork->image_url)) : ?>
                                                            <img src="<?= h($item->artwork_variant->artwork->image_url) ?>" alt="<?= h($item->artwork_variant->artwork->title) ?>" class="mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-0"><?= h($item->artwork_variant->artwork->title) ?></h6>
                                                            <small class="text-muted"><?= $this->Html->link('View Artwork', ['controller' => 'Artworks', 'action' => 'edit', $item->artwork_variant->artwork->artwork_id, 'prefix' => 'Admin']) ?></small>
                                                            <small class="text-muted d-block">Variant: <?= h($item->artwork_variant->dimension) ?></small>
                                                        </div>
                                                    <?php else : ?>
                                                        <div>
                                                            <h6 class="mb-0">Artwork Variant #<?= h($item->artwork_variant_id) ?></h6>
                                                            <small class="text-muted">(Artwork details not available)</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= h($item->quantity) ?></td>
                                            <td class="text-end">$<?= $this->Number->format($item->price, ['precision' => 2]) ?></td>
                                            <td class="text-end">$<?= $this->Number->format($item->price * $item->quantity, ['precision' => 2]) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No items found for this order.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end">$<?= $this->Number->format($order->total_amount, ['precision' => 2]) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Information Card -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
                </div>
                <div class="card-body">
                    <?php if (isset($order->payment) && $order->payment) : ?>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Payment Method:</strong> <?= h(ucfirst($order->payment->payment_method)) ?></p>
                                <p><strong>Payment Status:</strong>
                                    <?php $paymentStatusClass = match ($order->payment->status) {
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'completed' => 'info',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    }; ?>
                                    <span class="badge badge-<?= $paymentStatusClass ?>"><?= ucfirst(h($order->payment->status)) ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Payment Date:</strong>
                                    <?php if (isset($order->payment->payment_date) && $order->payment->payment_date) : ?>
                                        <?= $order->payment->payment_date->format('M d, Y H:i') ?>
                                    <?php else : ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Transaction ID:</strong>
                                    <?php if (!empty($order->payment->transaction_id)) : ?>
                                        <?= h($order->payment->transaction_id) ?>
                                        <?php if (strpos($order->payment->transaction_id, 'pi_') === 0) : ?>
                                            <br><small class="text-muted">Stripe Payment Intent ID</small>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php else : ?>
                        <p class="text-center text-muted">No payment information available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Notes Card -->
            <?php if (!empty($order->order_notes)) : ?>
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Notes</h6>
                </div>
                <div class="card-body">
                    <p><?= nl2br(h($order->order_notes)) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, ['url' => ['action' => 'updateStatus'], 'id' => 'updateStatusForm']) ?>
                <?= $this->Form->hidden('order_id', ['value' => $order->order_id]) ?>
                <div class="form-group">
                    <?= $this->Form->label('status', 'Status') ?>
                    <?= $this->Form->select('status', [
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                    ], [
                        'class' => 'form-control',
                        'value' => $order->order_status,
                        'required' => true,
                    ]); ?>
                </div>
                <div class="form-group">
                    <?= $this->Form->label('notes', 'Notes') ?>
                    <?= $this->Form->textarea('notes', [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Add notes about this status change (optional)',
                    ]); ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateStatusSubmit">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Submit status update
        document.getElementById('updateStatusSubmit').addEventListener('click', function() {
            document.getElementById('updateStatusForm').submit();
        });
    });
</script>
