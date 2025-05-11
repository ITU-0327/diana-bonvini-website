<?php
/**
 * @var \App\View\AppView $this
 * @var bool $isConnected
 * @var string $authUrl
 * @var int $month
 * @var int $year
 * @var array $calendarData
 * @var \DateTime $today
 * @var \DateTime $startOfWeek
 * @var \DateTime $endOfWeek
 * @var \Cake\ORM\ResultSet $upcomingAppointments
 * @var \Cake\ORM\ResultSet $unscheduledRequests
 */
use Cake\I18n\Time;
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-alt mr-2"></i><?= __('Calendar Dashboard') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Calendar') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$isConnected): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                <div>
                    <h4 class="alert-heading"><?= __('Google Calendar Not Connected') ?></h4>
                    <p class="mb-0">
                        <?= __('Your Google Calendar account is not connected. Connect now to sync appointments.') ?>
                        <a href="<?= h($authUrl) ?>" class="btn btn-sm btn-primary ml-2">
                            <i class="fab fa-google mr-1"></i><?= __('Connect Google Calendar') ?>
                        </a>
                        <?= $this->Html->link(
                            '<i class="fas fa-cog mr-1"></i>' . __('Integration Settings'),
                            ['controller' => 'GoogleAuth', 'action' => 'index'],
                            ['class' => 'btn btn-sm btn-secondary ml-2', 'escape' => false]
                        ) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-9">
            <!-- Calendar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('Monthly Calendar') ?></h6>
                    <div class="calendar-nav d-flex align-items-center">
                        <?php
                        $prevMonth = $month == 1 ? 12 : $month - 1;
                        $prevYear = $month == 1 ? $year - 1 : $year;
                        $nextMonth = $month == 12 ? 1 : $month + 1;
                        $nextYear = $month == 12 ? $year + 1 : $year;
                        ?>
                        <?= $this->Html->link(
                            '<i class="fas fa-chevron-left"></i>',
                            ['action' => 'index', '?' => ['month' => $prevMonth, 'year' => $prevYear]],
                            ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
                        ) ?>
                        
                        <span class="mx-3 font-weight-bold"><?= __(date('F Y', strtotime("$year-$month-01"))) ?></span>
                        
                        <?= $this->Html->link(
                            '<i class="fas fa-chevron-right"></i>',
                            ['action' => 'index', '?' => ['month' => $nextMonth, 'year' => $nextYear]],
                            ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
                        ) ?>
                        
                        <?= $this->Html->link(
                            __('Today'),
                            ['action' => 'index', '?' => ['month' => date('n'), 'year' => date('Y')]],
                            ['class' => 'btn btn-primary btn-sm ml-2']
                        ) ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="calendar">
                        <div class="calendar-header d-flex">
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>
                            <div class="calendar-day-header">Sun</div>
                        </div>
                        <div class="calendar-grid">
                            <?php foreach ($calendarData as $index => $day): ?>
                                <?php
                                $dayClasses = ['calendar-day'];
                                if (!$day['is_current_month']) {
                                    $dayClasses[] = 'other-month';
                                }
                                if ($day['is_today']) {
                                    $dayClasses[] = 'today';
                                }
                                ?>
                                <div class="<?= implode(' ', $dayClasses) ?>">
                                    <div class="day-header d-flex justify-content-between">
                                        <span class="day-number"><?= h($day['day']) ?></span>
                                        
                                        <?php if ($day['is_current_month']): ?>
                                            <?= $this->Html->link(
                                                '<i class="fas fa-plus"></i>',
                                                ['action' => 'add', '?' => ['date' => $day['date']->format('Y-m-d')]],
                                                ['class' => 'btn btn-sm btn-link text-primary p-0', 'escape' => false, 'title' => 'Add Appointment']
                                            ) ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($day['appointment_count'] > 0): ?>
                                        <div class="day-content">
                                            <?php 
                                            $maxToShow = 3;
                                            $counter = 0;
                                            foreach ($day['appointments'] as $appointment): 
                                                if ($counter < $maxToShow):
                                            ?>
                                                <div class="appointment-item">
                                                    <?= $this->Html->link(
                                                        '<i class="fas fa-clock mr-1"></i>' . $appointment->appointment_time->format('g:i A'),
                                                        ['action' => 'edit', $appointment->appointment_id],
                                                        ['class' => 'appointment-link', 'escape' => false]
                                                    ) ?>
                                                </div>
                                            <?php 
                                                endif;
                                                $counter++;
                                            endforeach; 
                                            
                                            if ($day['appointment_count'] > $maxToShow):
                                            ?>
                                                <div class="more-appointments">
                                                    <?= $this->Html->link(
                                                        __('+ {0} more', $day['appointment_count'] - $maxToShow),
                                                        ['action' => 'day', $day['date']->format('Y-m-d')],
                                                        ['class' => 'more-link']
                                                    ) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($day['is_current_month'] && $day['appointment_count'] > 0): ?>
                                        <div class="day-footer">
                                            <?= $this->Html->link(
                                                __('View All'),
                                                ['action' => 'day', $day['date']->format('Y-m-d')],
                                                ['class' => 'btn btn-sm btn-outline-primary btn-block']
                                            ) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-week mr-1"></i>' . __('Week View'),
                                ['action' => 'week', '?' => ['start' => $startOfWeek->format('Y-m-d')]],
                                ['class' => 'btn btn-primary', 'escape' => false]
                            ) ?>
                            
                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-day mr-1"></i>' . __('Today\'s Schedule'),
                                ['action' => 'day', $today->format('Y-m-d')],
                                ['class' => 'btn btn-info ml-2', 'escape' => false]
                            ) ?>
                        </div>
                        <div class="col-md-6 text-right">
                            <?= $this->Html->link(
                                '<i class="fas fa-cog mr-1"></i>' . __('Calendar Settings'),
                                ['controller' => 'GoogleAuth', 'action' => 'index'],
                                ['class' => 'btn btn-secondary', 'escape' => false]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3">
            <!-- Upcoming Appointments Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('Upcoming Appointments') ?></h6>
                </div>
                <div class="card-body">
                    <?php if ($upcomingAppointments->count() > 0): ?>
                        <div class="upcoming-appointments">
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                                <div class="appointment-card mb-3">
                                    <div class="card border-left-primary">
                                        <div class="card-body py-2 px-3">
                                            <div class="mb-1">
                                                <span class="font-weight-bold">
                                                    <?= $appointment->appointment_date->format('M j, Y') ?>
                                                </span>
                                                <span class="ml-2">
                                                    <i class="far fa-clock text-gray-500"></i> 
                                                    <?= $appointment->appointment_time->format('g:i A') ?>
                                                </span>
                                            </div>
                                            
                                            <div class="mb-1">
                                                <span class="text-primary">
                                                    <?= h(ucfirst(str_replace('_', ' ', $appointment->appointment_type))) ?>
                                                </span>
                                                <?php if (!empty($appointment->writing_service_request)): ?>
                                                <span class="badge badge-info ml-1">
                                                    <?= h($appointment->writing_service_request->writing_service_request_id) ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div>
                                                <small class="text-muted">
                                                    <i class="fas fa-user mr-1"></i>
                                                    <?= h($appointment->user->first_name . ' ' . $appointment->user->last_name) ?>
                                                </small>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <?= $this->Html->link(
                                                    __('View'),
                                                    ['action' => 'edit', $appointment->appointment_id],
                                                    ['class' => 'btn btn-sm btn-primary']
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-3">
                            <?= $this->Html->link(
                                __('View All Appointments'),
                                ['controller' => 'Appointments', 'action' => 'index'],
                                ['class' => 'btn btn-outline-primary btn-sm']
                            ) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-600 mb-0"><?= __('No upcoming appointments found.') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Schedule Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('Quick Schedule') ?></h6>
                </div>
                <div class="card-body">
                    <?php if ($unscheduledRequests->count() > 0): ?>
                        <div class="unscheduled-requests">
                            <p class="text-muted mb-3"><?= __('Writing service requests awaiting consultation:') ?></p>
                            
                            <?php foreach ($unscheduledRequests as $request): ?>
                                <div class="request-card mb-3">
                                    <div class="card border-left-info">
                                        <div class="card-body py-2 px-3">
                                            <div class="mb-1">
                                                <span class="font-weight-bold">
                                                    <?= h($request->service_title) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span>
                                                    <span class="badge badge-info">
                                                        <?= h($request->writing_service_request_id) ?>
                                                    </span>
                                                    <span class="badge badge-<?= getStatusClass($request->request_status) ?> ml-1">
                                                        <?= h(ucfirst($request->request_status)) ?>
                                                    </span>
                                                </span>
                                                
                                                <small class="text-muted">
                                                    <?= $request->created_at->format('M j, Y') ?>
                                                </small>
                                            </div>
                                            
                                            <div class="mb-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-user mr-1"></i>
                                                    <?= h($request->user->first_name . ' ' . $request->user->last_name) ?>
                                                </small>
                                            </div>
                                            
                                            <div class="mt-2">
                                                <?= $this->Html->link(
                                                    __('Schedule Appointment'),
                                                    ['action' => 'add', '?' => ['request_id' => $request->writing_service_request_id]],
                                                    ['class' => 'btn btn-sm btn-info']
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-3">
                            <?= $this->Html->link(
                                __('View All Requests'),
                                ['controller' => 'WritingServiceRequests', 'action' => 'index'],
                                ['class' => 'btn btn-outline-info btn-sm']
                            ) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-check fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-600 mb-0"><?= __('No unscheduled requests found.') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('Quick Actions') ?></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <?= $this->Html->link(
                                '<i class="fas fa-plus-circle"></i><span class="ml-2">New Appointment</span>',
                                ['action' => 'add'],
                                ['class' => 'btn btn-primary btn-block', 'escape' => false]
                            ) ?>
                        </div>
                        <div class="col-6 mb-3">
                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-week"></i><span class="ml-2">Week View</span>',
                                ['action' => 'week'],
                                ['class' => 'btn btn-info btn-block', 'escape' => false]
                            ) ?>
                        </div>
                        <div class="col-6 mb-3">
                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-day"></i><span class="ml-2">Today</span>',
                                ['action' => 'day', date('Y-m-d')],
                                ['class' => 'btn btn-success btn-block', 'escape' => false]
                            ) ?>
                        </div>
                        <div class="col-6 mb-3">
                            <?= $this->Html->link(
                                '<i class="fas fa-cog"></i><span class="ml-2">Settings</span>',
                                ['controller' => 'GoogleAuth', 'action' => 'index'],
                                ['class' => 'btn btn-secondary btn-block', 'escape' => false]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Calendar Styles */
.calendar {
    width: 100%;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background-color: #f8f9fc;
}

.calendar-day-header {
    padding: 10px;
    text-align: center;
    font-weight: bold;
    border-bottom: 1px solid #e3e6f0;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    grid-template-rows: repeat(6, minmax(120px, 1fr));
    grid-gap: 1px;
    background-color: #e3e6f0;
}

.calendar-day {
    background-color: white;
    padding: 8px;
    min-height: 120px;
    display: flex;
    flex-direction: column;
}

.calendar-day.other-month {
    background-color: #f8f9fc;
}

.calendar-day.today {
    background-color: #e8f4ff;
}

.day-header {
    margin-bottom: 8px;
}

.day-number {
    font-weight: bold;
}

.day-content {
    flex-grow: 1;
    overflow-y: auto;
    max-height: 80px;
}

.appointment-item {
    margin-bottom: 5px;
    font-size: 0.85rem;
}

.appointment-link {
    display: block;
    padding: 2px 5px;
    background-color: #e8f4ff;
    border-radius: 3px;
    color: #4e73df;
    text-decoration: none;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.appointment-link:hover {
    background-color: #4e73df;
    color: white;
    text-decoration: none;
}

.more-appointments {
    font-size: 0.8rem;
    text-align: center;
    margin-top: 5px;
}

.more-link {
    color: #4e73df;
}

.day-footer {
    margin-top: auto;
    padding-top: 8px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .calendar-grid {
        grid-template-rows: repeat(6, minmax(100px, 1fr));
    }
    
    .calendar-day {
        min-height: 100px;
        padding: 5px;
    }
    
    .day-content {
        max-height: 60px;
    }
}

@media (max-width: 768px) {
    .calendar-grid {
        grid-template-rows: repeat(6, minmax(80px, 1fr));
    }
    
    .calendar-day {
        min-height: 80px;
        padding: 3px;
    }
    
    .appointment-item {
        margin-bottom: 3px;
    }
    
    .day-content {
        max-height: 40px;
    }
    
    .day-footer {
        display: none;
    }
}
</style>

<?php
/**
 * Helper function for getting status badge classes
 */
function getStatusClass(string $status): string
{
    return match ($status) {
        'pending' => 'warning',
        'in_progress' => 'primary',
        'completed' => 'success',
        'cancelled', 'canceled' => 'danger',
        default => 'secondary'
    };
}
?>