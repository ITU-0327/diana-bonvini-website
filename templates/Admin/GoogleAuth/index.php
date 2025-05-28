<?php
/**
 * @var \App\View\AppView $this
 * @var bool $isConnected
 * @var string $authUrl
 * @var \App\Model\Entity\GoogleCalendarSetting|null $settings
 */

$this->assign('title', __('Google Calender Authentication'));
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-google mr-2"></i><?= __('Google Calendar Integration') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Google Calendar') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= __('Connection Status') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($isConnected): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?= __('Your Google Calendar is connected and appointments will be synced automatically.') ?>
                        </div>

                        <dl class="row">
                            <dt class="col-sm-4"><?= __('Connected Calendar') ?></dt>
                            <dd class="col-sm-8"><?= h($settings->calendar_id) ?></dd>

                            <dt class="col-sm-4"><?= __('Last Updated') ?></dt>
                            <dd class="col-sm-8"><?= h($settings->updated_at) ?></dd>
                        </dl>

                        <div class="mt-3">
                            <?= $this->Form->postLink(
                                '<i class="fas fa-unlink mr-1"></i>' . __('Disconnect'),
                                ['action' => 'disconnect'],
                                [
                                    'class' => 'btn btn-warning',
                                    'escape' => false,
                                    'confirm' => __('Are you sure you want to disconnect your Google Calendar? New appointments will not be synced until you reconnect.')
                                ]
                            ) ?>

                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-alt mr-1"></i>' . __('View Calendar'),
                                ['action' => 'viewCalendar'],
                                ['class' => 'btn btn-primary ml-2', 'escape' => false]
                            ) ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?= __('Your Google Calendar is not connected.') ?>
                        </div>

                        <p>
                            <?= __('Connect your Google Calendar to enable automatic syncing of appointments when customers book time slots. This ensures you never miss an appointment and improves your scheduling efficiency.') ?>
                        </p>

                        <a href="<?= h($authUrl) ?>" class="btn btn-primary">
                            <i class="fab fa-google mr-1"></i><?= __('Connect Google Calendar') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= __('Troubleshooting Calendar Sync Issues') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <h5><?= __('Common Issues') ?></h5>
                    <ul>
                        <li><strong><?= __('Appointments not appearing in calendar:') ?></strong>
                            <?= __('You need to connect your Google Calendar first. Use the button above to connect.') ?></li>
                        <li><strong><?= __('Calendar syncs but no Google Meet links:') ?></strong>
                            <?= __('Make sure your Google Workspace account has Google Meet enabled.') ?></li>
                        <li><strong><?= __('Authentication errors:') ?></strong>
                            <?= __('If you get authentication errors, try disconnecting and reconnecting your calendar.') ?></li>
                    </ul>

                    <h5 class="mt-4"><?= __('How to Connect Your Calendar') ?></h5>
                    <ol>
                        <li><?= __('Click the "Connect Google Calendar" button above') ?></li>
                        <li><?= __('Sign in to your Google account when prompted') ?></li>
                        <li><?= __('Grant permission to manage your calendar and create meetings') ?></li>
                        <li><?= __('You will be redirected back to this page when connection is complete') ?></li>
                    </ol>

                    <div class="alert alert-info mt-3" role="alert">
                        <i class="fas fa-info-circle mr-2"></i>
                        <?= __('Note: You must use a Google account that has Google Calendar enabled. If you use Google Workspace, make sure your administrator has enabled the Google Calendar API.') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= __('Calendar Sync Benefits') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            <strong><?= __('Automatic appointment syncing') ?></strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            <strong><?= __('Google Meet links generated automatically') ?></strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            <strong><?= __('Customers see your real-time availability') ?></strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            <strong><?= __('Automatic reminders for meetings') ?></strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            <strong><?= __('Access appointments from any device') ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-google {
        color: #fff;
        background-color: #4285F4;
        border-color: #4285F4;
    }
    .btn-google:hover {
        color: #fff;
        background-color: #3367D6;
        border-color: #3367D6;
    }
    .icon-circle {
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .bg-primary {
        background-color: #4e73df \!important;
    }
</style>
