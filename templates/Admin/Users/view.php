<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */

$this->assign('title', __('User Details'));
?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">User Details</h1>
        <div>
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                <li class="breadcrumb-item"><?= $this->Html->link(__('Users'), ['action' => 'index']) ?></li>
                <li class="breadcrumb-item active"><?= __('View User') ?></li>
            </ol>
        </div>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; background-color: #4e73df; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 600;">
                            <?= substr($user->first_name ?? '', 0, 1) ?><?= substr($user->last_name ?? '', 0, 1) ?>
                        </div>
                        <h5 class="mb-0"><?= h($user->first_name . ' ' . $user->last_name) ?></h5>
                        <p class="text-muted">
                            <?php if ($user->user_type === 'admin') : ?>
                                <span class="badge bg-primary">Administrator</span>
                            <?php else : ?>
                                <span class="badge bg-secondary">Customer</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Status</h6>
                        <p>
                            <?php
                            // Check if logged in within the last 30 days
                            $isActive = false;
                            if (isset($user->last_login) && $user->last_login) {
                                $thirtyDaysAgo = new \Cake\I18n\DateTime('-30 days');
                                $isActive = $user->last_login >= $thirtyDaysAgo;
                            }
                            ?>
                            <?php if ($isActive) : ?>
                                <span class="badge bg-success">Active</span>
                            <?php else : ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Email</h6>
                        <p><?= h($user->email) ?></p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Phone</h6>
                        <p><?= !empty($user->phone_number) ? h($user->phone_number) : '<span class="text-muted">Not provided</span>' ?></p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Account Created</h6>
                        <p>
                            <?php if (isset($user->created_at)) : ?>
                                <?= $user->created_at->format('F j, Y') ?>
                            <?php elseif (isset($user->created)) : ?>
                                <?= $user->created->format('F j, Y') ?>
                            <?php else : ?>
                                <span class="text-muted">Unknown</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Last Login</h6>
                        <p>
                            <?php if (isset($user->last_login) && $user->last_login) : ?>
                                <?= $user->last_login->format('F j, Y g:i A') ?>
                            <?php else : ?>
                                <span class="text-muted">Never</span>
                            <?php endif; ?>
                        </p>
                    </div>

                </div>
            </div>

            <!-- Address Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Address Information</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($user->street_address)) : ?>
                        <address>
                            <strong><?= h($user->first_name . ' ' . $user->last_name) ?></strong><br>
                            <?= h($user->street_address) ?><br>
                            <?php if (!empty($user->street_address2)) : ?>
                                <?= h($user->street_address2) ?><br>
                            <?php endif; ?>
                            <?= h($user->suburb ?? '') . (!empty($user->suburb) && !empty($user->state) ? ', ' : '') . h($user->state ?? '') . ' ' . h($user->postcode ?? '') ?><br>
                            <?= h($user->country ?? '') ?>
                        </address>
                    <?php else : ?>
                        <p class="text-muted">No address information provided.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Account Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h6 class="font-weight-bold">Account Type</h6>
                            <p><?= $user->user_type === 'admin' ? 'Administrator' : 'Customer' ?></p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <h6 class="font-weight-bold">Registration Date</h6>
                            <p>
                                <?php if (isset($user->created_at)) : ?>
                                    <?= $user->created_at->format('F j, Y') ?>
                                <?php elseif (isset($user->created)) : ?>
                                    <?= $user->created->format('F j, Y') ?>
                                <?php else : ?>
                                    <span class="text-muted">Unknown</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <h6 class="font-weight-bold">Account ID</h6>
                            <p><?= $this->Format->userId($user->user_id) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Tab -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Orders</h6>
                </div>
                <div class="card-body">
                    <?php if (isset($user->orders) && count($user->orders) > 0) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user->orders as $order) : ?>
                                        <tr>
                                            <td class="align-middle"><?= $this->Format->orderId($order->order_id) ?></td>
                                            <td class="align-middle">
                                                <?php if (isset($order->created_at)) : ?>
                                                    <?= $order->created_at->format('M d, Y') ?>
                                                <?php elseif (isset($order->order_date)) : ?>
                                                    <?= $order->order_date->format('M d, Y') ?>
                                                <?php else : ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">$<?= $this->Number->format($order->total_amount, ['precision' => 2]) ?></td>
                                            <td class="align-middle">
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
                                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst(h($status)) ?></span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group d-flex justify-content-center">
                                                    <a href="<?= $this->Url->build(['controller' => 'Orders', 'action' => 'view', $order->order_id]) ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <p class="text-muted">No orders found for this user.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }

    .bg-primary {
        background-color: #4e73df !important;
        color: #fff;
    }

    .bg-secondary {
        background-color: #858796 !important;
        color: #fff;
    }

    .bg-success {
        background-color: #1cc88a !important;
        color: #fff;
    }

    .bg-danger {
        background-color: #e74a3b !important;
        color: #fff;
    }

    .bg-warning {
        background-color: #f6c23e !important;
        color: #212529;
    }

    .bg-info {
        background-color: #36b9cc !important;
        color: #fff;
    }
</style>
