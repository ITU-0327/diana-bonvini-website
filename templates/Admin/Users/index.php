<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 * @var int $totalUsers
 * @var int $activeUsers
 * @var int $customerUsers
 * @var int $adminUsers
 */
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-2"></i><?= __('User Management') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Users') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- User Stats Cards -->
    <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $totalUsers ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $activeUsers ?></h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $customerUsers ?></h3>
                        <p>Customers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $adminUsers ?></h3>
                        <p>Administrators</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-filter mr-1"></i> Filter Users
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <select id="role-filter" class="form-control">
                                    <option value="all">All Roles</option>
                                    <option value="customer">Customers</option>
                                    <option value="admin">Administrators</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <select id="status-filter" class="form-control">
                                    <option value="all">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <select id="sort-order" class="form-control">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="name_asc">Name (A-Z)</option>
                                    <option value="name_desc">Name (Z-A)</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-input" class="form-control" placeholder="Search users...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
                    </div>
                    <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0) : ?>
                                <?php foreach ($users as $user) : ?>
                                <tr class="user-row" data-role="<?= h($user->user_type) ?>" data-status="<?= isset($user->active) && $user->active ? 'active' : 'inactive' ?>">
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2" style="width: 32px; height: 32px; background-color: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #666; font-weight: 600;">
                                                <?= substr($user->first_name ?? '', 0, 1) ?><?= substr($user->last_name ?? '', 0, 1) ?>
                                            </div>
                                            <div>
                                                <?= h($user->first_name . ' ' . $user->last_name) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?= h($user->email) ?></td>
                                    <td class="align-middle">
                                        <?php if ($user->user_type === 'admin') : ?>
                                            <span class="badge bg-primary">Administrator</span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">Customer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (isset($user->active) && $user->active) : ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else : ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (isset($user->created_at)) : ?>
                                            <?= $user->created_at->format('M d, Y') ?>
                                        <?php elseif (isset($user->created)) : ?>
                                            <?= $user->created->format('M d, Y') ?>
                                        <?php else : ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (isset($user->last_login) && $user->last_login) : ?>
                                            <?= $user->last_login->format('M d, Y H:i') ?>
                                        <?php else : ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <div class="btn-group">
                                            <a href="<?= $this->Url->build(['action' => 'view', $user->user_id]) ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= $this->Url->build(['action' => 'edit', $user->user_id]) ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (isset($user->active)) : ?>
                                                <?php if ($user->active) : ?>
                                                    <?= $this->Form->postLink(
                                                        '<i class="fas fa-user-slash"></i>',
                                                        ['action' => 'deactivate', $user->user_id],
                                                        [
                                                            'confirm' => 'Are you sure you want to deactivate this user?',
                                                            'class' => 'btn btn-sm btn-warning',
                                                            'escape' => false,
                                                            'title' => 'Deactivate',
                                                        ],
                                                    ) ?>
                                                <?php else : ?>
                                                    <?= $this->Form->postLink(
                                                        '<i class="fas fa-user-check"></i>',
                                                        ['action' => 'activate', $user->user_id],
                                                        [
                                                            'confirm' => 'Are you sure you want to activate this user?',
                                                            'class' => 'btn btn-sm btn-success',
                                                            'escape' => false,
                                                            'title' => 'Activate',
                                                        ],
                                                    ) ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?= $this->Paginator->prev('« Previous') ?>
                            <?= $this->Paginator->numbers() ?>
                            <?= $this->Paginator->next('Next »') ?>
                        </ul>
                    </nav>
                    <p class="text-center">
                        <?= $this->Paginator->counter('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total') ?>
                    </p>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Role filter
        document.getElementById('role-filter').addEventListener('change', function() {
            filterUsers();
        });

        // Status filter
        document.getElementById('status-filter').addEventListener('change', function() {
            filterUsers();
        });

        // Search functionality
        document.getElementById('search-input').addEventListener('keyup', function() {
            filterUsers();
        });

        // Function to filter users
        function filterUsers() {
            const roleFilter = document.getElementById('role-filter').value;
            const statusFilter = document.getElementById('status-filter').value;
            const searchTerm = document.getElementById('search-input').value.toLowerCase();

            document.querySelectorAll('.user-row').forEach(function(row) {
                let display = true;

                // Role filtering
                if (roleFilter !== 'all' && row.getAttribute('data-role') !== roleFilter) {
                    display = false;
                }

                // Status filtering
                if (statusFilter !== 'all' && row.getAttribute('data-status') !== statusFilter) {
                    display = false;
                }

                // Search filtering
                if (searchTerm !== '') {
                    const nameCell = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    const emailCell = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    if (!nameCell.includes(searchTerm) && !emailCell.includes(searchTerm)) {
                        display = false;
                    }
                }

                // Show/hide row
                row.style.display = display ? '' : 'none';
            });
        }
    });
</script>

<style>
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }

    .small-box .inner {
        padding: 10px;
    }

    .small-box h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px;
        padding: 0;
        white-space: nowrap;
    }

    .small-box p {
        font-size: 1rem;
    }

    .small-box .icon {
        color: rgba(0,0,0,.15);
        font-size: 70px;
        position: absolute;
        right: 15px;
        top: 15px;
        z-index: 0;
    }

    .bg-info {
        background-color: #17a2b8!important;
        color: #fff;
    }

    .bg-success {
        background-color: #28a745!important;
        color: #fff;
    }

    .bg-warning {
        background-color: #ffc107!important;
        color: #1f2d3d;
    }

    .bg-danger {
        background-color: #dc3545!important;
        color: #fff;
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

    .badge.bg-warning {
        color: #212529;
    }

    .float-sm-end {
        float: right !important;
    }
</style>
