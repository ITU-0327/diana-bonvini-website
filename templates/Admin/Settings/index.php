<?php
/**
 * @var \App\View\AppView $this
 * @var array $settings
 */
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Site Settings</h1>
    </div>

    <?= $this->Form->create(null, [
        'url' => ['action' => 'update'],
        'id' => 'settings-form',
    ]) ?>

    <div class="row">
        <!-- General Settings -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="site_name" class="font-weight-bold">Site Name</label>
                        <input type="text" id="site_name" name="site[site_name]" class="form-control" value="<?= h($settings['site']['site_name']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_tagline" class="font-weight-bold">Site Tagline</label>
                        <input type="text" id="site_tagline" name="site[site_tagline]" class="form-control" value="<?= h($settings['site']['site_tagline']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_email" class="font-weight-bold">Contact Email</label>
                        <input type="email" id="contact_email" name="site[contact_email]" class="form-control" value="<?= h($settings['site']['contact_email']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="font-weight-bold">Phone Number</label>
                        <input type="text" id="phone" name="site[phone]" class="form-control" value="<?= h($settings['site']['phone']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="font-weight-bold">Business Address</label>
                        <textarea id="address" name="site[address]" class="form-control" rows="3"><?= h($settings['site']['address']) ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Business Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Business Settings</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="tax_rate" class="font-weight-bold">Tax Rate (%)</label>
                        <input type="number" id="tax_rate" name="business[tax_rate]" class="form-control" value="<?= h($settings['business']['tax_rate']) ?>" min="0" max="100" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label for="shipping_fee" class="font-weight-bold">Default Shipping Fee</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" id="shipping_fee" name="business[shipping_fee]" class="form-control" value="<?= h($settings['business']['shipping_fee']) ?>" min="0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="currency" class="font-weight-bold">Currency</label>
                        <select id="currency" name="business[currency]" class="form-control">
                            <option value="AUD" <?= $settings['business']['currency'] === 'AUD' ? 'selected' : '' ?>>Australian Dollar (AUD)</option>
                            <option value="USD" <?= $settings['business']['currency'] === 'USD' ? 'selected' : '' ?>>US Dollar (USD)</option>
                            <option value="EUR" <?= $settings['business']['currency'] === 'EUR' ? 'selected' : '' ?>>Euro (EUR)</option>
                            <option value="GBP" <?= $settings['business']['currency'] === 'GBP' ? 'selected' : '' ?>>British Pound (GBP)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="timezone" class="font-weight-bold">Timezone</label>
                        <select id="timezone" name="business[timezone]" class="form-control">
                            <option value="Australia/Melbourne" <?= $settings['business']['timezone'] === 'Australia/Melbourne' ? 'selected' : '' ?>>Melbourne</option>
                            <option value="Australia/Sydney" <?= $settings['business']['timezone'] === 'Australia/Sydney' ? 'selected' : '' ?>>Sydney</option>
                            <option value="Australia/Brisbane" <?= $settings['business']['timezone'] === 'Australia/Brisbane' ? 'selected' : '' ?>>Brisbane</option>
                            <option value="Australia/Perth" <?= $settings['business']['timezone'] === 'Australia/Perth' ? 'selected' : '' ?>>Perth</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Social Media & Other Settings -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Social Media Links</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="facebook" class="font-weight-bold">
                            <i class="fab fa-facebook text-primary mr-2"></i>Facebook Page
                        </label>
                        <input type="url" id="facebook" name="social[facebook]" class="form-control" value="<?= h($settings['social']['facebook']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="instagram" class="font-weight-bold">
                            <i class="fab fa-instagram text-danger mr-2"></i>Instagram Profile
                        </label>
                        <input type="url" id="instagram" name="social[instagram]" class="form-control" value="<?= h($settings['social']['instagram']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="twitter" class="font-weight-bold">
                            <i class="fab fa-twitter text-info mr-2"></i>Twitter Profile
                        </label>
                        <input type="url" id="twitter" name="social[twitter]" class="form-control" value="<?= h($settings['social']['twitter']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="linkedin" class="font-weight-bold">
                            <i class="fab fa-linkedin text-primary mr-2"></i>LinkedIn Profile
                        </label>
                        <input type="url" id="linkedin" name="social[linkedin]" class="form-control" value="<?= h($settings['social']['linkedin']) ?>">
                    </div>
                </div>
            </div>
            
            <!-- Email Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Notification Settings</h6>
                </div>
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="notify_new_order" name="notifications[new_order]" checked>
                        <label class="custom-control-label" for="notify_new_order">Email notification for new orders</label>
                    </div>
                    
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="notify_low_stock" name="notifications[low_stock]" checked>
                        <label class="custom-control-label" for="notify_low_stock">Email notification for low stock</label>
                    </div>
                    
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="notify_new_user" name="notifications[new_user]" checked>
                        <label class="custom-control-label" for="notify_new_user">Email notification for new user registrations</label>
                    </div>
                    
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="notify_writing_request" name="notifications[writing_request]" checked>
                        <label class="custom-control-label" for="notify_writing_request">Email notification for new writing service requests</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Save Settings Button -->
    <div class="text-center mb-5">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-save mr-2"></i>Save Settings
        </button>
    </div>
    
    <?= $this->Form->end() ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.getElementById('settings-form');
        
        form.addEventListener('submit', function(event) {
            // Email validation
            const emailField = document.getElementById('contact_email');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailPattern.test(emailField.value)) {
                event.preventDefault();
                alert('Please enter a valid email address.');
                emailField.focus();
                return false;
            }
            
            // Add any other validation as needed
        });
    });
</script>