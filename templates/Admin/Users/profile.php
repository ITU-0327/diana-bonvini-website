<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">My Profile</h1>
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
                        <div class="d-flex justify-content-center">
                            <div class="avatar-circle mb-3" style="width: 150px; height: 150px; background-color: #4a90e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 60px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <?= substr($user->first_name ?? '', 0, 1) ?><?= substr($user->last_name ?? '', 0, 1) ?>
                            </div>
                        </div>
                        <h5 class="mb-0"><?= h($user->first_name . ' ' . $user->last_name) ?></h5>
                        <p class="text-muted">Administrator</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Email</h6>
                        <p><?= h($user->email) ?></p>
                    </div>

                    <?php if (!empty($user->phone_number)) : ?>
                        <div class="mb-3">
                            <h6 class="font-weight-bold">Phone</h6>
                            <p><?= h($user->phone_number) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Account Created</h6>
                        <p><?= $user->created_at ? $user->created_at->format('F j, Y') : 'N/A' ?></p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Last Login</h6>
                        <p><?= $user->last_login ? $user->last_login->format('F j, Y g:i A') : 'N/A' ?></p>
                    </div>

                    <a href="<?= $this->Url->build(['action' => 'edit', $user->user_id]) ?>" class="btn btn-primary btn-block">
                        <i class="fas fa-user-edit mr-1"></i> Edit Profile
                    </a>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item py-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Logged in</h6>
                                <small class="text-muted">3 hours ago</small>
                            </div>
                            <p class="mb-1 small text-muted">Successfully logged in from Chrome on macOS</p>
                        </li>
                        <li class="list-group-item py-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Updated an artwork</h6>
                                <small class="text-muted">Yesterday</small>
                            </div>
                            <p class="mb-1 small text-muted">Modified "Ocean Sunset" artwork details</p>
                        </li>
                        <li class="list-group-item py-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Processed an order</h6>
                                <small class="text-muted">2 days ago</small>
                            </div>
                            <p class="mb-1 small text-muted">Marked order #12345 as completed</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Account Settings -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Profile</h6>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($user, [
                        'url' => ['action' => 'profile'],
                        'id' => 'profileForm',
                    ]) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="font-weight-bold">First Name</label>
                                <?= $this->Form->control('first_name', [
                                    'class' => 'form-control',
                                    'label' => false,
                                    'placeholder' => 'Enter your first name',
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="font-weight-bold">Last Name</label>
                                <?= $this->Form->control('last_name', [
                                    'class' => 'form-control',
                                    'label' => false,
                                    'placeholder' => 'Enter your last name',
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="font-weight-bold">Email Address</label>
                        <?= $this->Form->control('email', [
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Enter your email address',
                        ]) ?>
                    </div>

                    <div class="form-group">
                        <label for="phone_number" class="font-weight-bold">Phone Number</label>
                        <?= $this->Form->control('phone_number', [
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Enter your phone number',
                        ]) ?>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </div>

                    <?= $this->Form->end() ?>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, [
                        'url' => ['action' => 'changePassword'],
                        'id' => 'passwordForm',
                    ]) ?>

                    <div class="form-group">
                        <label for="current_password" class="font-weight-bold">Current Password</label>
                        <?= $this->Form->control('current_password', [
                            'type' => 'password',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Enter your current password',
                        ]) ?>
                    </div>

                    <div class="form-group">
                        <label for="new_password" class="font-weight-bold">New Password</label>
                        <?= $this->Form->control('new_password', [
                            'type' => 'password',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Enter your new password',
                        ]) ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="font-weight-bold">Confirm New Password</label>
                        <?= $this->Form->control('confirm_password', [
                            'type' => 'password',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Confirm your new password',
                        ]) ?>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="showPassword">
                            <label class="custom-control-label" for="showPassword">Show Password</label>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key mr-1"></i> Change Password
                        </button>
                    </div>

                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide password functionality
        const showPasswordCheckbox = document.getElementById('showPassword');
        if (showPasswordCheckbox) {
            const passwordFields = document.querySelectorAll('input[type="password"]');

            showPasswordCheckbox.addEventListener('change', function() {
                passwordFields.forEach(function(field) {
                    field.type = this.checked ? 'text' : 'password';
                }, this);
            });
        }

        // Form validation
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(event) {
                const newPassword = document.querySelector('input[name="new_password"]').value;
                const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

                if (newPassword !== confirmPassword) {
                    event.preventDefault();
                    alert('New password and confirmation do not match.');
                    return false;
                }

                if (newPassword && newPassword.length < 8) {
                    event.preventDefault();
                    alert('New password must be at least 8 characters long.');
                    return false;
                }
            });
        }
    });
</script>
