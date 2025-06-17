<?php
/**
 * @var \App\View\AppView $this
 * @var string $email
 * @var string|null $testingCode
 * @var array $debugInfo
 */
$this->assign('title', __('Verification Required'));
?>

<div class="users form content">
    <div class="column-responsive column-80">
        <div class="users form verification-form">
            <div class="verification-header">
                <h2 class="text-center"><?= __('Two-Factor Authentication') ?></h2>
                <div class="verification-email">
                    <p><?= __('For your security, we sent a verification code to:') ?></p>
                    <div class="email-display">
                        <i class="email-icon">✉</i>
                        <strong><?= h($email) ?></strong>
                    </div>
                </div>
            </div>

            <!-- TESTING CODE SECTION - Always show for debugging -->
            <div class="testing-code-display">
                <h3 style="color: #e74c3c; font-weight: bold; text-align: center; margin-bottom: 10px;">
                     TESTING MODE ONLY
                </h3>
                <div style="background-color: #fff3cd; border: 2px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">
                    <?php if (!empty($testingCode)): ?>
                        <p style="color: #856404; font-weight: bold; margin-bottom: 10px;">
                            For testing purposes, your verification code is:
                        </p>
                        <div style="font-size: 28px; font-weight: bold; letter-spacing: 8px; color: #d63031; background-color: #ffffff; padding: 15px; border-radius: 6px; border: 2px dashed #e74c3c; display: inline-block; min-width: 200px;">
                            <?= h($testingCode) ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #d63031; font-weight: bold; margin-bottom: 10px;">
                            ⚠️ No valid verification code found in database!
                        </p>
                        <div style="font-size: 14px; color: #856404; text-align: left; background-color: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 10px;">
                            <strong>Debug Info:</strong><br>
                            <?php foreach ($debugInfo as $key => $value): ?>
                                <div><?= h($key) ?>: <?= h(is_bool($value) ? ($value ? 'true' : 'false') : $value) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <p style="color: #856404; font-size: 14px; margin-top: 10px; font-style: italic;">
                        This code display is only visible in review mode and should be removed in production!
                    </p>
                </div>
            </div>

            <?= $this->Form->create(null, ['url' => ['controller' => 'TwoFactorAuth', 'action' => 'verify'], 'class' => 'code-verification-form']) ?>
            <div class="verification-code-container">
                <?= $this->Form->control('verification_code', [
                    'label' => __('Enter Verification Code'),
                    'type' => 'text',
                    'autofocus' => true,
                    'required' => true,
                    'class' => 'verification-code-input',
                    'maxlength' => 6,
                    'placeholder' => '000000',
                ]) ?>
                <div class="code-hint">
                    <small><?= __('Enter the 6-digit code we sent to your email') ?></small>
                </div>

                <div class="trust-device-option">
                    <?= $this->Form->control('trust_device', [
                        'label' => __('Trust this device for 30 days'),
                        'type' => 'checkbox',
                    ]) ?>
                </div>
            </div>

            <div class="verification-actions">
                <?= $this->Form->button(__('Verify & Sign In'), ['class' => 'button primary-button']) ?>
                <?= $this->Form->end() ?>
            </div>

            <div class="verification-options">
                <div class="option-section">
                    <h4><?= __("Didn't receive a code?") ?></h4>
                    <?= $this->Form->create(null, ['url' => ['controller' => 'TwoFactorAuth', 'action' => 'resendCode'], 'class' => 'resend-form']) ?>
                    <?= $this->Form->button(__('Resend Code'), ['class' => 'button secondary-button']) ?>
                    <?= $this->Form->end() ?>
                    <p class="hint"><?= __('Please check your spam folder if you don\'t see it in your inbox') ?></p>
                </div>

                <div class="option-section">
                    <h4><?= __('Having trouble?') ?></h4>
                    <p><?= $this->Html->link(__('Return to login'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'back-link']) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.verification-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.verification-header {
    margin-bottom: 2rem;
}

.verification-header h2 {
    color: #333;
    font-weight: 700;
    margin-bottom: 1.5rem;
}

.verification-email {
    background-color: #f5f9ff;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1.5rem;
}

.email-display {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 0.5rem;
    font-size: 1.1rem;
}

.email-icon {
    margin-right: 0.5rem;
    color: #4a90e2;
    font-style: normal;
}

.testing-code-display {
    margin: 1.5rem 0;
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from {
        box-shadow: 0 0 5px rgba(231, 76, 60, 0.3);
    }
    to {
        box-shadow: 0 0 20px rgba(231, 76, 60, 0.6);
    }
}

.verification-code-container {
    margin: 2rem 0;
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 6px;
}

.verification-code-input {
    font-size: 1.5rem;
    letter-spacing: 4px;
    padding: 0.75rem;
    text-align: center;
    width: 100%;
    max-width: 200px;
    margin: 0 auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: border-color 0.3s;
}

.verification-code-input:focus {
    border-color: #4a90e2;
    outline: none;
}

.code-hint {
    margin-top: 0.5rem;
    color: #666;
}

.trust-device-option {
    margin-top: 1.5rem;
    text-align: center;
}

.verification-actions {
    margin-bottom: 2rem;
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

.verification-options {
    border-top: 1px solid #eee;
    padding-top: 1.5rem;
    margin-top: 1.5rem;
}

.option-section {
    margin-bottom: 1.5rem;
}

.option-section h4 {
    color: #555;
    margin-bottom: 0.5rem;
}

.secondary-button {
    background-color: transparent;
    color: #4a90e2;
    border: 1px solid #4a90e2;
    padding: 0.5rem 1.5rem;
    font-size: 0.9rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.secondary-button:hover {
    background-color: #f0f7ff;
}

.hint {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: #888;
}

.back-link {
    color: #4a90e2;
    text-decoration: none;
    font-weight: 600;
}

.back-link:hover {
    text-decoration: underline;
}
</style>
