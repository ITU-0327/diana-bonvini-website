<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */
use Cake\Utility\Inflector;
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-pen mr-2"></i><?= __('Writing Service Request Details') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Writing Requests'), ['action' => 'index']) ?></li>
                        <li class="breadcrumb-item active"><?= __('Request Details') ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Request Information</h6>
                    <span class="badge bg-<?= getStatusClass($writingServiceRequest->status) ?> py-2 px-3">
                        <?= ucfirst(str_replace('_', ' ', h($writingServiceRequest->status))) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold"><?= h($writingServiceRequest->service_title) ?></h5>
                            <p class="text-muted">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Created: <?= $writingServiceRequest->created->format('F j, Y h:i A') ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-tag mr-2"></i>
                                Service Type: <?= h(Inflector::humanize($writingServiceRequest->service_type)) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">Client Information</h6>
                                    <?php if (isset($writingServiceRequest->user) && $writingServiceRequest->user) : ?>
                                        <p class="mb-1">
                                            <i class="fas fa-user mr-2"></i>
                                            <?= h($writingServiceRequest->user->first_name . ' ' . $writingServiceRequest->user->last_name) ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-envelope mr-2"></i>
                                            <?= h($writingServiceRequest->user->email) ?>
                                        </p>
                                        <?php if ($writingServiceRequest->user->phone) : ?>
                                            <p class="mb-1">
                                                <i class="fas fa-phone mr-2"></i>
                                                <?= h($writingServiceRequest->user->phone) ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <p class="mb-1">
                                            <i class="fas fa-user mr-2"></i>
                                            <?= h($writingServiceRequest->client_name ?? 'Name not provided') ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-envelope mr-2"></i>
                                            <?= h($writingServiceRequest->client_email ?? 'Email not provided') ?>
                                        </p>
                                        <?php if (isset($writingServiceRequest->client_phone)) : ?>
                                            <p class="mb-1">
                                                <i class="fas fa-phone mr-2"></i>
                                                <?= h($writingServiceRequest->client_phone) ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Request Details -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="font-weight-bold mb-3">Request Details</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light w-25">Word Count</th>
                                            <td><?= $writingServiceRequest->word_count ? number_format($writingServiceRequest->word_count) : 'Not specified' ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Field/Subject</th>
                                            <td><?= h($writingServiceRequest->subject_area) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Deadline</th>
                                            <td>
                                                <?php if ($writingServiceRequest->deadline) : ?>
                                                    <?= $writingServiceRequest->deadline->format('F j, Y h:i A') ?>
                                                    <?php
                                                    $now = new DateTime();
                                                    $deadline = $writingServiceRequest->deadline;
                                                    $isPast = $now > $deadline;

                                                    if ($isPast) : ?>
                                                        <span class="badge bg-danger ml-2">Past Due</span>
                                                    <?php else :
                                                        $interval = $now->diff($deadline);
                                                        $daysRemaining = $interval->days;
                                                        $badgeClass = $daysRemaining < 2 ? 'danger' : ($daysRemaining < 5 ? 'warning' : 'success');
                                                        ?>
                                                        <span class="badge bg-<?= $badgeClass ?> ml-2">
                                                            <?= $daysRemaining ?> days remaining
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    Not specified
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Price</th>
                                            <td>
                                                <?php if ($writingServiceRequest->final_price) : ?>
                                                    <span class="font-weight-bold">$<?= number_format($writingServiceRequest->final_price, 2) ?></span>
                                                <?php else : ?>
                                                    <span class="badge bg-warning">Pending Quote</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Description</th>
                                            <td><?= nl2br(h($writingServiceRequest->description)) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Attachments</th>
                                            <td>
                                                <?php if (!empty($writingServiceRequest->attachment_paths)) : ?>
                                                    <ul class="list-unstyled">
                                                        <?php foreach ($writingServiceRequest->attachment_paths as $path) : ?>
                                                            <li>
                                                                <i class="fas fa-file-alt mr-2"></i>
                                                                <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'download', $path]) ?>" target="_blank" class="text-primary">
                                                                    <?= h(basename($path)) ?>
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else : ?>
                                                    <p class="text-muted mb-0">No attachments</p>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages and Communication Log -->
            <div class="card shadow mb-4" id="messages">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Messages</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($writingServiceRequest->request_messages)) : ?>
                        <div class="messages-container">
                            <?php foreach ($writingServiceRequest->request_messages as $message) : ?>
                                <div class="message card mb-3 <?= $message->is_admin ? 'border-primary ml-5' : 'border-success mr-5' ?>">
                                    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>
                                                <?= $message->is_admin ? 'Admin' : ($writingServiceRequest->user ? $writingServiceRequest->user->first_name : 'Client') ?>
                                            </strong>
                                            <small class="ml-2 text-muted">
                                                <?= $message->created->format('M j, Y g:i A') ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-body py-3 px-3">
                                        <?= nl2br(h($message->message_text)) ?>
                                        
                                        <?php if (!empty($message->attachment_path)) : ?>
                                            <div class="mt-2 pt-2 border-top">
                                                <i class="fas fa-paperclip mr-1"></i>
                                                <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'downloadMessage', $message->request_message_id]) ?>" class="text-primary">
                                                    <?= h(basename($message->attachment_path)) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p>No messages yet. Start the conversation with the client.</p>
                        </div>
                    <?php endif; ?>

                    <!-- New Message Form -->
                    <div class="new-message-form mt-4 pt-3 border-top">
                        <h6 class="font-weight-bold mb-3">Send New Message</h6>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'addMessage', $writingServiceRequest->writing_service_request_id],
                            'type' => 'file',
                        ]) ?>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->label('message_text', 'Message', ['class' => 'form-label']) ?>
                            <?= $this->Form->textarea('message_text', [
                                'rows' => 4,
                                'class' => 'form-control',
                                'placeholder' => 'Type your message here...',
                                'required' => true,
                            ]) ?>
                        </div>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->label('attachment', 'Attachment (optional)', ['class' => 'form-label']) ?>
                            <div class="input-group">
                                <?= $this->Form->file('attachment', [
                                    'class' => 'form-control',
                                    'id' => 'customFile',
                                ]) ?>
                            </div>
                        </div>
                        
                        <div class="form-group mb-0 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-1"></i>
                                Send Message
                            </button>
                        </div>
                        
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with Actions -->
        <div class="col-lg-4">
            <!-- Action Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <!-- Update Status -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Update Status</h6>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'updateStatus', $writingServiceRequest->writing_service_request_id],
                            'id' => 'statusForm',
                        ]) ?>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->label('status', 'Status', ['class' => 'form-label']) ?>
                            <?= $this->Form->select('status', [
                                'pending' => 'Pending',
                                'pending_quote' => 'Pending Quote',
                                'scheduled' => 'Scheduled',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ], [
                                'default' => $writingServiceRequest->status,
                                'class' => 'form-select form-control',
                            ]) ?>
                        </div>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->label('admin_notes', 'Notes (optional)', ['class' => 'form-label']) ?>
                            <?= $this->Form->textarea('admin_notes', [
                                'class' => 'form-control',
                                'rows' => 3,
                                'placeholder' => 'Add notes about this status change',
                            ]) ?>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                Update Status
                            </button>
                        </div>
                        
                        <?= $this->Form->end() ?>
                    </div>
                    
                    <!-- Set Price -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Set Price</h6>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'setPrice', $writingServiceRequest->writing_service_request_id],
                            'id' => 'priceForm',
                        ]) ?>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->label('final_price', 'Price Amount', ['class' => 'form-label']) ?>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <?= $this->Form->number('final_price', [
                                    'step' => '0.01',
                                    'min' => '0',
                                    'default' => $writingServiceRequest->final_price,
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter amount',
                                ]) ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-block">
                                Set Price & Notify Client
                            </button>
                        </div>
                        
                        <?= $this->Form->end() ?>
                    </div>
                    
                    <!-- Schedule Appointment -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Schedule Meeting</h6>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'scheduleMeeting', $writingServiceRequest->writing_service_request_id],
                            'id' => 'scheduleForm',
                        ]) ?>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->label('meeting_date', 'Meeting Date & Time', ['class' => 'form-label']) ?>
                            <?= $this->Form->dateTime('meeting_date', [
                                'type' => 'datetime-local',
                                'class' => 'form-control',
                                'placeholder' => 'Select meeting date & time',
                            ]) ?>
                        </div>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->label('meeting_type', 'Meeting Type', ['class' => 'form-label']) ?>
                            <?= $this->Form->select('meeting_type', [
                                'in_person' => 'In Person',
                                'zoom' => 'Zoom Meeting',
                                'phone' => 'Phone Call',
                            ], [
                                'class' => 'form-select form-control',
                                'empty' => 'Select meeting type',
                            ]) ?>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-info btn-block">
                                Schedule & Notify Client
                            </button>
                        </div>
                        
                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
            
            <!-- Timeline Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Request Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Request Created</h6>
                                <p class="small text-muted mb-0">
                                    <?= $writingServiceRequest->created->format('M j, Y g:i A') ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if ($writingServiceRequest->final_price) : ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Price Set</h6>
                                    <p class="small text-muted mb-0">
                                        $<?= number_format($writingServiceRequest->final_price, 2) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($writingServiceRequest->status !== 'pending') : ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?= getStatusClass($writingServiceRequest->status) ?>"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Status Updated</h6>
                                    <p class="small text-muted mb-0">
                                        <?= ucfirst(str_replace('_', ' ', h($writingServiceRequest->status))) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($writingServiceRequest->modified) : ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-secondary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Last Updated</h6>
                                    <p class="small text-muted mb-0">
                                        <?= $writingServiceRequest->modified->format('M j, Y g:i A') ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Back Button Card -->
            <div class="card shadow mb-4">
                <div class="card-body p-3">
                    <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Writing Requests
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 25px;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        left: 9px;
        height: 100%;
        width: 2px;
        background-color: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -25px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background-color: #2A9D8F;
        top: 0;
    }
    
    .timeline-content {
        padding-left: 5px;
    }
    
    /* Message styles */
    .messages-container {
        max-height: 400px;
        overflow-y: auto;
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
    
    .btn-block {
        display: block;
        width: 100%;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.05);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll to messages section if URL has #messages anchor
        if (window.location.hash === "#messages") {
            const messagesElement = document.getElementById('messages');
            if (messagesElement) {
                messagesElement.scrollIntoView();
            }
        }
        
        // Initialize file input with custom behavior
        const fileInput = document.getElementById('customFile');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const fileName = this.files[0]?.name;
                const fileLabel = document.querySelector('label[for="customFile"]');
                if (fileLabel && fileName) {
                    fileLabel.textContent = fileName;
                }
            });
        }
    });
</script>

<?php
// Helper function for determining badge colors
function getStatusClass(string $status): string
{
    return match ($status) {
        'pending', 'pending_quote' => 'warning',
        'scheduled' => 'info',
        'in_progress' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}
?>