<?php
/**
 * @var \App\View\AppView $this
 * @var string $date
 * @var \DateTime $dateObj
 * @var \Cake\ORM\ResultSet $appointments
 * @var bool $isConnected
 * @var array $googleEvents
 * @var array $writingServiceRequests
 */
use Cake\I18n\Time;
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-calendar-day mr-2"></i><?= __('Daily Schedule: {0}', $dateObj->format('F j, Y')) ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Calendar'), ['action' => 'index']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Daily View') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="day-navigation card shadow">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <?php
                            $prevDate = clone $dateObj;
                            $prevDate->modify('-1 day');
                            
                            $nextDate = clone $dateObj;
                            $nextDate->modify('+1 day');
                            ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-chevron-left mr-1"></i>' . $prevDate->format('M j, Y'),
                                ['action' => 'day', $prevDate->format('Y-m-d')],
                                ['class' => 'btn btn-outline-primary', 'escape' => false]
                            ) ?>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <div class="btn-group">
                                <?= $this->Html->link(
                                    '<i class="fas fa-calendar-alt mr-1"></i>' . __('Month'),
                                    ['action' => 'index', '?' => ['month' => $dateObj->format('n'), 'year' => $dateObj->format('Y')]],
                                    ['class' => 'btn btn-primary', 'escape' => false]
                                ) ?>
                                
                                <?= $this->Html->link(
                                    '<i class="fas fa-calendar-week mr-1"></i>' . __('Week'),
                                    ['action' => 'week', '?' => ['start' => (clone $dateObj)->modify('monday this week')->format('Y-m-d')]],
                                    ['class' => 'btn btn-primary', 'escape' => false]
                                ) ?>
                                
                                <?php if ($dateObj->format('Y-m-d') !== date('Y-m-d')): ?>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-calendar-day mr-1"></i>' . __('Today'),
                                        ['action' => 'day', date('Y-m-d')],
                                        ['class' => 'btn btn-primary', 'escape' => false]
                                    ) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-right">
                            <?= $this->Html->link(
                                $nextDate->format('M j, Y') . ' <i class="fas fa-chevron-right ml-1"></i>',
                                ['action' => 'day', $nextDate->format('Y-m-d')],
                                ['class' => 'btn btn-outline-primary', 'escape' => false]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Appointments for the day -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('Appointments for {0}', $dateObj->format('F j, Y')) ?></h6>
                    
                    <div>
                        <?= $this->Html->link(
                            '<i class="fas fa-plus-circle mr-1"></i>' . __('Add Appointment'),
                            ['action' => 'add', '?' => ['date' => $date]],
                            ['class' => 'btn btn-primary btn-sm', 'escape' => false]
                        ) ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($appointments->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th><?= __('Time') ?></th>
                                        <th><?= __('Type') ?></th>
                                        <th><?= __('Client') ?></th>
                                        <th><?= __('Related Request') ?></th>
                                        <th><?= __('Status') ?></th>
                                        <th class="text-center"><?= __('Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr class="appointment-row">
                                            <td class="align-middle">
                                                <span class="font-weight-bold">
                                                    <?= $appointment->appointment_time->format('g:i A') ?>
                                                </span>
                                                <div class="small text-muted">
                                                    <?= $appointment->duration ?> min
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <?= h(ucfirst(str_replace('_', ' ', $appointment->appointment_type))) ?>
                                            </td>
                                            <td class="align-middle">
                                                <?= h($appointment->user->first_name . ' ' . $appointment->user->last_name) ?>
                                                <div class="small text-muted">
                                                    <i class="far fa-envelope"></i> <?= h($appointment->user->email) ?>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <?php if (!empty($appointment->writing_service_request)): ?>
                                                    <?= $this->Html->link(
                                                        h($appointment->writing_service_request->writing_service_request_id),
                                                        ['controller' => 'WritingServiceRequests', 'action' => 'view', $appointment->writing_service_request->writing_service_request_id],
                                                        ['class' => 'badge badge-info']
                                                    ) ?>
                                                    <div class="small">
                                                        <?= h($appointment->writing_service_request->service_title) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= __('None') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-<?= getStatusClass($appointment->status) ?>">
                                                    <?= h(ucfirst($appointment->status)) ?>
                                                </span>
                                                <?php if ($appointment->is_google_synced): ?>
                                                    <span class="badge badge-light" title="Synced with Google Calendar">
                                                        <i class="fab fa-google text-primary"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group">
                                                    <?= $this->Html->link(
                                                        '<i class="fas fa-edit"></i>',
                                                        ['action' => 'edit', $appointment->appointment_id],
                                                        ['class' => 'btn btn-sm btn-primary', 'escape' => false, 'title' => 'Edit']
                                                    ) ?>
                                                    
                                                    <?php if (!empty($appointment->meeting_link)): ?>
                                                        <a href="<?= h($appointment->meeting_link) ?>" target="_blank" class="btn btn-sm btn-success" title="Join Meeting">
                                                            <i class="fas fa-video"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?= $this->Form->postLink(
                                                        '<i class="fas fa-trash"></i>',
                                                        ['action' => 'delete', $appointment->appointment_id],
                                                        [
                                                            'class' => 'btn btn-sm btn-danger',
                                                            'escape' => false,
                                                            'title' => 'Delete',
                                                            'confirm' => __('Are you sure you want to delete this appointment?')
                                                        ]
                                                    ) ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-day fa-4x text-gray-300 mb-3"></i>
                            <p class="lead text-gray-500 mb-0"><?= __('No appointments scheduled for this day.') ?></p>
                            
                            <div class="mt-4">
                                <?= $this->Html->link(
                                    '<i class="fas fa-plus-circle mr-1"></i>' . __('Schedule New Appointment'),
                                    ['action' => 'add', '?' => ['date' => $date]],
                                    ['class' => 'btn btn-primary', 'escape' => false]
                                ) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($isConnected && !empty($googleEvents)): ?>
            <!-- Google Calendar Events -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fab fa-google mr-2"></i><?= __('Google Calendar Events') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th><?= __('Time') ?></th>
                                    <th><?= __('Event') ?></th>
                                    <th><?= __('Details') ?></th>
                                    <th class="text-center"><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($googleEvents as $event): ?>
                                    <?php 
                                    $startTime = new DateTime($event['start']);
                                    $endTime = new DateTime($event['end']);
                                    ?>
                                    <tr>
                                        <td class="align-middle">
                                            <span class="font-weight-bold">
                                                <?= $startTime->format('g:i A') ?>
                                            </span>
                                            <div class="small text-muted">
                                                to <?= $endTime->format('g:i A') ?>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <?= h($event['title']) ?>
                                        </td>
                                        <td class="align-middle">
                                            <?php if (!empty($event['location'])): ?>
                                                <div class="small">
                                                    <i class="fas fa-map-marker-alt text-primary mr-1"></i>
                                                    <?= h($event['location']) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($event['description'])): ?>
                                                <div class="small text-muted">
                                                    <?= h(mb_substr($event['description'], 0, 50)) ?>
                                                    <?= (mb_strlen($event['description']) > 50) ? '...' : '' ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <?php if (!empty($event['meetLink'])): ?>
                                                <a href="<?= h($event['meetLink']) ?>" target="_blank" class="btn btn-sm btn-success" title="Join Meeting">
                                                    <i class="fas fa-video"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($event['htmlLink'])): ?>
                                                <a href="<?= h($event['htmlLink']) ?>" target="_blank" class="btn btn-sm btn-info" title="Open in Google Calendar">
                                                    <i class="fab fa-google"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <!-- Quick Schedule Widget -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('Quick Schedule') ?></h6>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, [
                        'url' => ['action' => 'add'],
                        'class' => 'quick-appointment-form',
                        'onsubmit' => 'this.querySelector("button[type=submit]").disabled = true;',
                    ]) ?>
                    
                    <?= $this->Form->hidden('appointment_date', ['value' => $date]) ?>
                    
                    <div class="form-group">
                        <label for="appointment-time"><?= __('Time') ?></label>
                        <?= $this->Form->time('appointment_time', [
                            'class' => 'form-control',
                            'id' => 'appointment-time',
                            'required' => true,
                        ]) ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment-type"><?= __('Type') ?></label>
                        <?= $this->Form->select('appointment_type', [
                            'initial_consultation' => 'Initial Consultation',
                            'follow_up' => 'Follow-up Meeting',
                            'project_review' => 'Project Review',
                            'delivery' => 'Final Delivery',
                        ], [
                            'class' => 'form-control',
                            'id' => 'appointment-type',
                            'empty' => 'Select Type',
                            'required' => true,
                        ]) ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="writing-service-request-id"><?= __('Request') ?></label>
                        <?= $this->Form->select('writing_service_request_id', $writingServiceRequests, [
                            'class' => 'form-control',
                            'id' => 'writing-service-request-id',
                            'empty' => 'Select Request (Optional)',
                        ]) ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration"><?= __('Duration (minutes)') ?></label>
                        <?= $this->Form->select('duration', [
                            '15' => '15 min',
                            '30' => '30 min',
                            '45' => '45 min',
                            '60' => '60 min',
                            '90' => '90 min',
                        ], [
                            'class' => 'form-control',
                            'id' => 'duration',
                            'value' => '30',
                            'required' => true,
                        ]) ?>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-plus-circle mr-1"></i><?= __('Create Quick Appointment') ?>
                        </button>
                    </div>
                    
                    <?= $this->Form->end() ?>
                </div>
            </div>
            
            <!-- Available Time Slots (if Google Calendar connected) -->
            <?php if ($isConnected): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('Available Time Slots') ?></h6>
                </div>
                <div class="card-body">
                    <div id="time-slots-loading" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted"><?= __('Loading available time slots...') ?></p>
                    </div>
                    
                    <div id="time-slots-container" class="d-none">
                        <p class="text-muted mb-3"><?= __('Click a time slot to quickly schedule an appointment:') ?></p>
                        
                        <div id="time-slots-list" class="list-group mb-3">
                            <!-- Time slots will be loaded here via JavaScript -->
                        </div>
                        
                        <div id="no-slots-message" class="text-center py-3 d-none">
                            <i class="far fa-clock fa-2x text-gray-400 mb-2"></i>
                            <p class="text-gray-600 mb-0"><?= __('No available time slots for this day.') ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Navigation and Tools -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?= __('Navigation & Tools') ?></h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-alt mr-1"></i>' . __('Month View'),
                                ['action' => 'index', '?' => ['month' => $dateObj->format('n'), 'year' => $dateObj->format('Y')]],
                                ['class' => 'btn btn-outline-primary btn-block', 'escape' => false]
                            ) ?>
                        </div>
                        <div class="col-6">
                            <?= $this->Html->link(
                                '<i class="fas fa-calendar-week mr-1"></i>' . __('Week View'),
                                ['action' => 'week', '?' => ['start' => (clone $dateObj)->modify('monday this week')->format('Y-m-d')]],
                                ['class' => 'btn btn-outline-primary btn-block', 'escape' => false]
                            ) ?>
                        </div>
                    </div>
                    
                    <div class="list-group">
                        <?= $this->Html->link(
                            '<i class="fas fa-cog mr-2"></i>' . __('Calendar Settings'),
                            ['controller' => 'GoogleAuth', 'action' => 'index'],
                            ['class' => 'list-group-item list-group-item-action', 'escape' => false]
                        ) ?>
                        
                        <?= $this->Html->link(
                            '<i class="fas fa-clipboard-list mr-2"></i>' . __('Writing Service Requests'),
                            ['controller' => 'WritingServiceRequests', 'action' => 'index'],
                            ['class' => 'list-group-item list-group-item-action', 'escape' => false]
                        ) ?>
                        
                        <?= $this->Html->link(
                            '<i class="fas fa-users mr-2"></i>' . __('Customer List'),
                            ['controller' => 'Users', 'action' => 'index'],
                            ['class' => 'list-group-item list-group-item-action', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isConnected): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAvailableTimeSlots();
    
    function loadAvailableTimeSlots() {
        fetch('/admin/calendar/get-time-slots?date=<?= $date ?>')
            .then(response => response.json())
            .then(data => {
                document.getElementById('time-slots-loading').classList.add('d-none');
                document.getElementById('time-slots-container').classList.remove('d-none');
                
                if (data.success && data.timeSlots && data.timeSlots.length > 0) {
                    const timeSlotsList = document.getElementById('time-slots-list');
                    timeSlotsList.innerHTML = '';
                    
                    data.timeSlots.forEach(slot => {
                        const listItem = document.createElement('a');
                        listItem.href = `/admin/calendar/add?date=${slot.date}&time=${slot.start}`;
                        listItem.className = 'list-group-item list-group-item-action';
                        listItem.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="far fa-clock mr-2"></i>${slot.formatted}</span>
                                <span class="badge badge-primary">Available</span>
                            </div>
                        `;
                        timeSlotsList.appendChild(listItem);
                    });
                } else {
                    document.getElementById('time-slots-list').classList.add('d-none');
                    document.getElementById('no-slots-message').classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error loading time slots:', error);
                document.getElementById('time-slots-loading').classList.add('d-none');
                document.getElementById('time-slots-container').classList.remove('d-none');
                document.getElementById('time-slots-list').classList.add('d-none');
                document.getElementById('no-slots-message').classList.remove('d-none');
                document.getElementById('no-slots-message').innerHTML = `
                    <i class="fas fa-exclamation-circle fa-2x text-danger mb-2"></i>
                    <p class="text-danger mb-0">Error loading time slots. Please try again.</p>
                `;
            });
    }
});
</script>
<?php endif; ?>

<?php
/**
 * Helper function for getting status badge classes
 */
function getStatusClass(string $status): string
{
    return match ($status) {
        'pending' => 'warning',
        'confirmed' => 'primary',
        'completed' => 'success',
        'cancelled', 'canceled' => 'danger',
        default => 'secondary'
    };
}
?>