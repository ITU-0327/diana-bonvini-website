<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Register');
?>

<div class="users form content">
    <div class="column-responsive column-80">
        <div class="users form">
            <h2 class="text-center mb-4"><?= __('Create an Account') ?></h2>
            <p class="text-center mb-4"><?= __('Join Diana Bonvini\'s Art Community. A verification code will be sent to your email.') ?></p>
            
            <?= $this->Form->create($user, ['url' => ['controller' => 'RegistrationVerify', 'action' => 'index'], 'class' => 'registration-form', 'autocomplete' => 'off']) ?>
            
            <?php if (!empty($user->getErrors())): ?>
                <div class="error-container mb-4">
                    <div class="error-message">
                        <strong><?= __('Please fix the following errors:') ?></strong>
                        <ul>
                            <?php foreach ($user->getErrors() as $field => $errors): ?>
                                <?php foreach ((array)$errors as $error): ?>
                                    <li><?= h($field) ?>: <?= h($error) ?></li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
            <?= $this->Form->control('user_type', ['type' => 'hidden', 'value' => 'customer']) ?>
            
            <div class="registration-section">
                <h4><?= __('Personal Information') ?></h4>
                <div class="row">
                    <div class="column">
                        <?= $this->Form->control('first_name', [
                            'required' => true,
                            'class' => 'form-control',
                            'placeholder' => 'First Name',
                            'autocomplete' => 'off',
                            'value' => ''
                        ]) ?>
                    </div>
                    <div class="column">
                        <?= $this->Form->control('last_name', [
                            'required' => true,
                            'class' => 'form-control',
                            'placeholder' => 'Last Name',
                            'autocomplete' => 'off',
                            'value' => ''
                        ]) ?>
                    </div>
                </div>
                
                <?= $this->Form->control('email', [
                    'required' => true,
                    'class' => 'form-control',
                    'placeholder' => 'Email Address',
                    'autocomplete' => 'off',
                    'value' => ''
                ]) ?>
            </div>
            
            <div class="registration-section">
                <h4><?= __('Create Password') ?></h4>
                <?= $this->Form->control('password', [
                    'required' => true,
                    'type' => 'password',
                    'class' => 'form-control',
                    'placeholder' => 'Password',
                    'autocomplete' => 'new-password',
                    'value' => ''
                ]) ?>
                <?= $this->Form->control('password_confirm', [
                    'required' => true,
                    'type' => 'password',
                    'label' => 'Confirm Password',
                    'class' => 'form-control',
                    'placeholder' => 'Confirm Password',
                    'autocomplete' => 'new-password',
                    'value' => ''
                ]) ?>
                <div class="password-requirements">
                    <small><?= __('Password must be at least 8 characters long and include uppercase, lowercase letters, and a number.') ?></small>
                </div>
            </div>

            <div class="submit-section text-center mt-4">
                <?= $this->Form->button(__('Continue to Verification'), ['class' => 'button primary-button']) ?>
                <?= $this->Form->end() ?>
            </div>
            
            <div class="login-link text-center mt-4">
                <p><?= __('Already have an account?') ?> <?= $this->Html->link(__('Log in here'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'login-link']) ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.users.form {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #333;
    font-weight: 700;
}

.registration-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 6px;
}

.registration-section h4 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #444;
    font-weight: 600;
}

.row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.column {
    flex: 1;
}

input[type="text"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
    border-color: #4a90e2;
    outline: none;
}

.password-requirements {
    margin-top: 0.5rem;
    color: #666;
}

.primary-button {
    background-color: #4a90e2;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
}

.primary-button:hover {
    background-color: #3a7bc8;
}

.login-link a {
    color: #4a90e2;
    text-decoration: none;
    font-weight: 600;
}

.login-link a:hover {
    text-decoration: underline;
}

.error-container {
    background-color: #fff8f8;
    border-left: 4px solid #e74c3c;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 4px;
}

.error-message {
    color: #e74c3c;
}

.error-message ul {
    margin-top: 0.5rem;
    margin-bottom: 0;
    padding-left: 1.5rem;
}
</style>