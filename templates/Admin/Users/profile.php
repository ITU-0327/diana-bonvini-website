<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */

$this->assign('title', __('My Profile'));
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

            <!-- Password Reset Link - Replaced Change Password Section -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Password Management</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">Need to reset your password? Use our secure password reset system.</p>

                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'forgotPassword', 'prefix' => false]) ?>" class="btn btn-primary">
                        <i class="fas fa-key mr-1"></i> Reset Password
                    </a>

                    <div class="mt-3 small text-muted">
                        <p>A password reset link will be sent to your email address. Click the link in the email to set a new password.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation for profile form
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', function(event) {
                // Add any profile form validation if needed
            });
        }
    });
</script>
