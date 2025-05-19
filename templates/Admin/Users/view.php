<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */

$this->assign('title', __('User Details'));
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user me-2"></i><?= __('User Details') ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                    <li class="breadcrumb-item"><?= $this->Html->link(__('Users'), ['action' => 'index']) ?></li>
                    <li class="breadcrumb-item active"><?= __('View User') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <!-- User Profile Card -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; background-color: #007bff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 600;">
                                <?= substr($user->first_name ?? '', 0, 1) ?><?= substr($user->last_name ?? '', 0, 1) ?>
                            </div>
                            <h3 class="profile-username text-center"><?= h($user->first_name . ' ' . $user->last_name) ?></h3>
                            <p class="text-muted text-center">
                                <?php if ($user->user_type === 'admin') : ?>
                                    <span class="badge bg-primary">Administrator</span>
                                <?php else : ?>
                                    <span class="badge bg-secondary">Customer</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Status</b> 
                                <span class="float-right">
                                    <?php if (isset($user->active) && $user->active) : ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <b>Email</b> <span class="float-right"><?= h($user->email) ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Phone</b> <span class="float-right"><?= !empty($user->phone_number) ? h($user->phone_number) : '<span class="text-muted">Not provided</span>' ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Joined</b> 
                                <span class="float-right">
                                    <?php if (isset($user->created_at)) : ?>
                                        <?= $user->created_at->format('M d, Y') ?>
                                    <?php elseif (isset($user->created)) : ?>
                                        <?= $user->created->format('M d, Y') ?>
                                    <?php else : ?>
                                        <span class="text-muted">Unknown</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                <b>Last Login</b> 
                                <span class="float-right">
                                    <?php if (isset($user->last_login) && $user->last_login) : ?>
                                        <?= $user->last_login->format('M d, Y H:i') ?>
                                    <?php else : ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $this->Url->build(['action' => 'edit', $user->user_id]) ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            
                            <?php if (isset($user->active)) : ?>
                                <?php if ($user->active) : ?>
                                    <?= $this->Form->postLink(
                                        '<i class="fas fa-user-slash me-1"></i> Deactivate',
                                        ['action' => 'deactivate', $user->user_id],
                                        [
                                            'confirm' => 'Are you sure you want to deactivate this user?',
                                            'class' => 'btn btn-warning btn-block',
                                            'escape' => false,
                                        ],
                                    ) ?>
                                <?php else : ?>
                                    <?= $this->Form->postLink(
                                        '<i class="fas fa-user-check me-1"></i> Activate',
                                        ['action' => 'activate', $user->user_id],
                                        [
                                            'confirm' => 'Are you sure you want to activate this user?',
                                            'class' => 'btn btn-success btn-block',
                                            'escape' => false,
                                        ],
                                    ) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Address Information</h3>
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

            <div class="col-md-8">
                <!-- Orders Tab -->
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">User Orders</h3>
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
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($user->orders as $order) : ?>
                                            <tr>
                                                <td><?= h($order->order_id) ?></td>
                                                <td>
                                                    <?php if (isset($order->created_at)) : ?>
                                                        <?= $order->created_at->format('M d, Y') ?>
                                                    <?php elseif (isset($order->order_date)) : ?>
                                                        <?= $order->order_date->format('M d, Y') ?>
                                                    <?php else : ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>$<?= $this->Number->format($order->total_amount, ['precision' => 2]) ?></td>
                                                <td>
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
                                                <td>
                                                    <a href="<?= $this->Url->build(['controller' => 'Orders', 'action' => 'view', $order->order_id]) ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
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

                <!-- Account Activity -->
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Account Information</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">Account Type</dt>
                            <dd class="col-sm-8"><?= $user->user_type === 'admin' ? 'Administrator' : 'Customer' ?></dd>
                            
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                <?php if (isset($user->active) && $user->active) : ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else : ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Registration Date</dt>
                            <dd class="col-sm-8">
                                <?php if (isset($user->created_at)) : ?>
                                    <?= $user->created_at->format('F d, Y H:i:s') ?>
                                <?php elseif (isset($user->created)) : ?>
                                    <?= $user->created->format('F d, Y H:i:s') ?>
                                <?php else : ?>
                                    <span class="text-muted">Unknown</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Last Login</dt>
                            <dd class="col-sm-8">
                                <?php if (isset($user->last_login) && $user->last_login) : ?>
                                    <?= $user->last_login->format('F d, Y H:i:s') ?>
                                <?php else : ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Account ID</dt>
                            <dd class="col-sm-8"><?= h($user->user_id) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.btn-block {
    display: block;
    width: 100%;
}

.float-right {
    float: right !important;
}

.card-outline {
    border-top: 3px solid;
}

.card-primary.card-outline {
    border-top-color: #007bff;
}

.card-success.card-outline {
    border-top-color: #28a745;
}

.card-info.card-outline {
    border-top-color: #17a2b8;
}

.card-secondary.card-outline {
    border-top-color: #6c757d;
}

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
</style>