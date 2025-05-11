<?php
/**
 * @var \App\View\AppView $this
 * @var bool $isConnected
 * @var string $authUrl
 * @var \App\Model\Entity\GoogleCalendarSetting|null $settings
 */
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
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= __('Connection Status') ?>
                        <?php if ($isConnected): ?>
                            <span class="badge badge-success ml-2"><?= __('Connected') ?></span>
                        <?php else: ?>
                            <span class="badge badge-secondary ml-2"><?= __('Not Connected') ?></span>
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($isConnected): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?= __('You are currently connected to Google Calendar.') ?>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="font-weight-bold"><?= __('Connection Details') ?></h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-light"><?= __('Calendar ID') ?></th>
                                        <td><?= h($settings->calendar_id) ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light"><?= __('Connected On') ?></th>
                                        <td><?= $settings->created_at->format('F j, Y g:i A') ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light"><?= __('Last Updated') ?></th>
                                        <td><?= $settings->updated_at->format('F j, Y g:i A') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-alt mr-1"></i>' . __('View Calendar'),
                                ['action' => 'viewCalendar'],
                                ['class' => 'btn btn-primary', 'escape' => false]
                            ) ?>
                            
                            <?= $this->Form->postLink(
                                '<i class="fas fa-unlink mr-1"></i>' . __('Disconnect'),
                                ['action' => 'disconnect'],
                                [
                                    'class' => 'btn btn-danger',
                                    'escape' => false,
                                    'confirm' => __('Are you sure you want to disconnect your Google Calendar?')
                                ]
                            ) ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            <?= __('Connect your Google Calendar to enable calendar sync and appointment scheduling.') ?>
                        </div>
                        
                        <p>
                            <?= __('Connecting your Google Calendar allows you to:') ?>
                        </p>
                        
                        <ul class="mb-4">
                            <li><?= __('View your calendar events in the admin dashboard') ?></li>
                            <li><?= __('Send available time slots to clients for appointment booking') ?></li>
                            <li><?= __('Automatically sync appointments with your Google Calendar') ?></li>
                            <li><?= __('Create Google Meet links for virtual consultations') ?></li>
                        </ul>
                        
                        <div class="text-center">
                            <a href="<?= h($authUrl) ?>" class="btn btn-google btn-lg">
                                <i class="fab fa-google mr-2"></i><?= __('Connect Google Calendar') ?>
                            </a>
                            <p class="text-muted mt-2 small">
                                <?= __('You\'ll be asked to grant permission to access your calendar') ?>
                            </p>
                        </div>
                        
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="font-weight-bold"><?= __('Don\'t want to connect right now?') ?></h6>
                            <p>
                                <?= __('You can still use the calendar features in demo mode.') ?>
                            </p>
                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-alt mr-1"></i>' . __('View Demo Calendar'),
                                ['action' => 'viewCalendar'],
                                ['class' => 'btn btn-outline-primary', 'escape' => false]
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('About Google Calendar Integration') ?></h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="font-weight-bold"><?= __('Features') ?></h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="icon-circle bg-primary text-white">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3 ml-3">
                                        <h6 class="font-weight-bold mb-1">Schedule Management</h6>
                                        <p class="text-muted mb-0">View and manage your availability</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="icon-circle bg-primary text-white">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3 ml-3">
                                        <h6 class="font-weight-bold mb-1">Client Booking</h6>
                                        <p class="text-muted mb-0">Allow clients to book appointments</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="icon-circle bg-primary text-white">
                                            <i class="fas fa-sync-alt"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3 ml-3">
                                        <h6 class="font-weight-bold mb-1">Two-way Sync</h6>
                                        <p class="text-muted mb-0">All appointments sync with Google</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="icon-circle bg-primary text-white">
                                            <i class="fas fa-video"></i>
                                        </div>
                                    </div>
                                    <div class="ms-3 ml-3">
                                        <h6 class="font-weight-bold mb-1">Google Meet</h6>
                                        <p class="text-muted mb-0">Automatic meeting link generation</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-light border mb-0">
                        <h6 class="font-weight-bold"><?= __('Need Help?') ?></h6>
                        <p>
                            <?= __('If you\'re having trouble connecting your Google Calendar, please check that:') ?>
                        </p>
                        <ul class="mb-0">
                            <li><?= __('You\'re signed in to the correct Google account') ?></li>
                            <li><?= __('You\'ve granted all the requested permissions') ?></li>
                            <li><?= __('Your Google account has calendar access') ?></li>
                        </ul>
                    </div>
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
