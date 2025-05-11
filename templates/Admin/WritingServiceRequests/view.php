<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */
use Cake\Utility\Inflector;

$this->assign('title', __('Writing Service Request Details'));
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
        <!-- Main Details and Chat Section -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Request Information</h6>
                    <span class="badge badge-<?= getStatusClass($writingServiceRequest->request_status) ?> py-2 px-3">
                        <?= ucfirst(str_replace('_', ' ', h($writingServiceRequest->request_status))) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold"><?= h($writingServiceRequest->service_title) ?></h5>
                            <p class="text-muted">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Created: <?= $writingServiceRequest->created_at->format('F j, Y h:i A') ?>
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
                                        <?php if (!empty($writingServiceRequest->user->phone_number)) : ?>
                                            <p class="mb-1">
                                                <i class="fas fa-phone mr-2"></i>
                                                <?= h($writingServiceRequest->user->phone_number) ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <p class="mb-1 text-muted">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            No user information available
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Request Notes -->
                    <?php if (!empty($writingServiceRequest->notes)) : ?>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="font-weight-bold mb-3">Client Notes</h6>
                            <div class="card bg-light-yellow">
                                <div class="card-body py-3 px-4">
                                    <p class="card-text"><?= nl2br(h($writingServiceRequest->notes)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Document Display -->
                    <?php if (!empty($writingServiceRequest->document)) : ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="font-weight-bold mb-3">Attached Document</h6>
                            <div class="card bg-light border-left-primary">
                                <div class="card-body py-3 px-4 d-flex align-items-center">
                                    <i class="fas fa-file-alt text-primary fa-2x mr-3"></i>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 font-weight-bold"><?= h(basename($writingServiceRequest->document)) ?></p>
                                    </div>
                                    <a href="<?= '/' . $writingServiceRequest->document ?>" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messages and Communication Log -->
            <div class="card shadow mb-4" id="messages">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Client Conversation</h6>
                    <span class="badge badge-info px-3 py-2"><?= count($writingServiceRequest->request_messages) ?> Messages</span>
                </div>
                <div class="card-body">
                    <div class="chat-container" style="max-height: 500px; overflow-y: auto; scroll-behavior: smooth;">
                        <?php if (!empty($writingServiceRequest->request_messages)) : ?>
                            <div class="chat-messages">
                                <?php foreach ($writingServiceRequest->request_messages as $message) : ?>
                                    <?php
                                    $isAdmin = isset($message->user) && $message->user->user_type === 'admin';
                                    ?>
                                    <div class="chat-message mb-3 <?= $isAdmin ? 'admin-message' : 'client-message' ?>" data-message-id="<?= h($message->request_message_id) ?>">
                                        <div class="message-header d-flex align-items-center mb-1">
                                            <div class="message-avatar mr-2">
                                                <?php if ($isAdmin) : ?>
                                                    <div class="avatar bg-primary text-white">A</div>
                                                <?php else : ?>
                                                    <div class="avatar bg-success text-white">
                                                        <?= substr($message->user->first_name ?? 'C', 0, 1) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="message-info">
                                                <span class="message-sender font-weight-bold">
                                                    <?= $isAdmin ? 'You (Admin)' : h($message->user->first_name . ' ' . $message->user->last_name) ?>
                                                </span>
                                                <span class="message-time text-muted ml-2">
                                                    <i class="far fa-clock"></i> <?= $message->created_at->format('M j, Y g:i A') ?>
                                                </span>
                                                <?php if (!$isAdmin && !$message->is_read) : ?>
                                                <span class="badge badge-warning ml-2">New</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            <div class="message-bubble p-3 rounded">
                                                <?= nl2br(h($message->message)) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <div class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-gray-300 mb-3"></i>
                                <p class="text-gray-500 mb-0">No messages yet. Start the conversation with the client.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- New Message Form -->
                    <div class="new-message-form mt-4 pt-3 border-top">
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'view', $writingServiceRequest->writing_service_request_id],
                            'id' => 'replyForm',
                        ]) ?>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->textarea('message_text', [
                                'rows' => 3,
                                'class' => 'form-control',
                                'placeholder' => 'Type your message here...',
                                'required' => true,
                                'id' => 'messageText',
                            ]) ?>
                        </div>
                        
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-primary px-4" id="sendButton">
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
                    <h6 class="m-0 font-weight-bold text-primary">Request Actions</h6>
                </div>
                <div class="card-body">
                    <!-- Schedule Consultation -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Schedule Consultation</h6>
                        <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#timeSlotsModal">
                            <i class="fas fa-calendar-alt mr-1"></i> Offer Available Time Slots
                        </button>
                        <p class="mt-2 text-sm text-muted">Select and send available time slots to the client</p>
                    </div>
                    <!-- Update Status -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Update Status</h6>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'updateStatus', $writingServiceRequest->writing_service_request_id],
                            'id' => 'statusForm',
                        ]) ?>
                        
                        <div class="form-group mb-3">
                            <?= $this->Form->select('status', [
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ], [
                                'default' => $writingServiceRequest->request_status,
                                'class' => 'form-control',
                                'empty' => false,
                            ]) ?>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sync-alt mr-1"></i> Update Status
                            </button>
                        </div>
                        
                        <?= $this->Form->end() ?>
                    </div>
                    
                    <!-- Set Price -->
                    <div class="mb-4 pt-2 border-top">
                        <h6 class="font-weight-bold mb-2">Set Price</h6>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'setPrice', $writingServiceRequest->writing_service_request_id],
                            'id' => 'priceForm',
                        ]) ?>
                        
                        <div class="form-group mb-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <?= $this->Form->number('final_price', [
                                    'step' => '0.01',
                                    'min' => '0',
                                    'value' => $writingServiceRequest->final_price,
                                    'class' => 'form-control',
                                    'placeholder' => 'Enter amount',
                                    'required' => true,
                                ]) ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-dollar-sign mr-1"></i> Set Price & Notify Client
                            </button>
                        </div>
                        
                        <?= $this->Form->end() ?>
                    </div>
                    
                    <!-- Google Calendar Link -->
                    <div class="mb-4 pt-2 border-top">
                        <h6 class="font-weight-bold mb-2">Google Calendar</h6>
                        <?= $this->Html->link(
                            '<i class="fab fa-google mr-1"></i> View My Calendar',
                            ['controller' => 'GoogleAuth', 'action' => 'viewCalendar'],
                            ['class' => 'btn btn-info btn-block', 'escape' => false]
                        ) ?>
                    </div>
                    
                    <!-- Payment Options Button (Will be implemented later) -->
                    <div class="mb-4 pt-2 border-top">
                        <h6 class="font-weight-bold mb-2">Payment Options</h6>
                        <button type="button" class="btn btn-warning btn-block" id="paymentOptionsBtn" disabled>
                            <i class="fas fa-credit-card mr-1"></i> Send Payment Request
                            <small class="d-block mt-1">(Coming Soon)</small>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <button class="btn btn-outline-primary btn-block" id="sendReplyTemplate" onclick="insertTemplate('I received your request and will review it shortly.')">
                                <i class="fas fa-reply mr-1"></i> Received
                            </button>
                        </div>
                        <div class="col-6 mb-3">
                            <button class="btn btn-outline-primary btn-block" id="sendInfoTemplate" onclick="insertTemplate('I need more information about your request. Could you please provide more details about...')">
                                <i class="fas fa-question-circle mr-1"></i> Need Info
                            </button>
                        </div>
                        <div class="col-6 mb-3">
                            <button class="btn btn-outline-primary btn-block" id="sendDelayTemplate" onclick="insertTemplate('I wanted to let you know that I need a bit more time to work on your request. I expect to have an update for you by...')">
                                <i class="fas fa-clock mr-1"></i> Delay
                            </button>
                        </div>
                        <div class="col-6 mb-3">
                            <button class="btn btn-outline-primary btn-block" id="sendCompleteTemplate" onclick="insertTemplate('I\'ve completed your request. You can find the completed work attached. Please let me know if you need any revisions.')">
                                <i class="fas fa-check mr-1"></i> Complete
                            </button>
                        </div>
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
    /* Chat Styles */
    .chat-container {
        padding: 10px;
    }
    
    .chat-message {
        margin-bottom: 20px;
    }
    
    .client-message .message-content {
        margin-right: 25%;
    }
    
    .admin-message .message-content {
        margin-left: 25%;
    }
    
    .admin-message .message-bubble {
        background-color: #e3f2fd;
        border-left: 4px solid #4e73df;
    }
    
    .client-message .message-bubble {
        background-color: #e8f5e9;
        border-left: 4px solid #1cc88a;
    }
    
    .message-avatar .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
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
    
    .bg-light-yellow {
        background-color: #fff8e1;
    }
    
    /* Form focus styles */
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    /* Quick action buttons hover */
    .btn-outline-primary:hover {
        transform: translateY(-2px);
        transition: transform 0.2s;
    }
</style>

<!-- Load jQuery UI for datepicker -->
<?= $this->Html->css('https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css') ?>
<?= $this->Html->script('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js') ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll to bottom of chat on page load
        const chatContainer = document.querySelector('.chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        // Focus on message input when clicking reply button
        document.getElementById('messageText').focus();
        
        // Animate button on form submit
        const replyForm = document.getElementById('replyForm');
        if (replyForm) {
            replyForm.addEventListener('submit', function() {
                const button = document.getElementById('sendButton');
                button.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Sending...';
                button.disabled = true;
            });
        }
        
        // Handle URL hash for navigating to specific sections
        if (window.location.hash) {
            const targetElement = document.querySelector(window.location.hash);
            if (targetElement) {
                setTimeout(() => {
                    window.scrollTo({
                        top: targetElement.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }, 100);
            }
        }
        
        // Set up real-time message polling
        setupMessagePolling();
    });
    
    // Template insertion function for quick replies
    function insertTemplate(text) {
        const textarea = document.getElementById('messageText');
        textarea.value = text;
        textarea.focus();
    }
    
    // Set up real-time message polling
    function setupMessagePolling() {
        // Get the writing service request ID from the URL
        const urlParts = window.location.pathname.split('/');
        const requestId = urlParts[urlParts.length - 1]; // Last part of the URL
        
        if (!requestId) return; // Exit if no ID found
        
        let lastMessageId = null;
        const chatMessages = document.querySelector('.chat-messages');
        
        // Find the last message ID if messages exist
        if (chatMessages && chatMessages.children.length > 0) {
            const lastMessage = chatMessages.children[chatMessages.children.length - 1];
            lastMessageId = lastMessage.dataset.messageId;
        }
        
        // Function to fetch new messages
        function fetchNewMessages() {
            const url = `/admin/writing-service-requests/fetch-messages/${requestId}` + 
                         (lastMessageId ? `?lastMessageId=${lastMessageId}` : '');
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        // Update the UI with new messages
                        data.messages.forEach(message => {
                            appendMessage(message);
                            // Update lastMessageId for next poll
                            lastMessageId = message.id;
                        });
                        
                        // Scroll to bottom
                        const chatContainer = document.querySelector('.chat-container');
                        if (chatContainer) {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }
                    }
                })
                .catch(error => console.error('Error fetching messages:', error));
        }
        
        // Function to append a new message to the chat
        function appendMessage(message) {
            if (!chatMessages) return;
            
            const isAdmin = message.sender === 'admin';
            
            const messageEl = document.createElement('div');
            messageEl.className = `chat-message mb-3 ${isAdmin ? 'admin-message' : 'client-message'}`;
            messageEl.dataset.messageId = message.id;
            
            messageEl.innerHTML = `
                <div class="message-header d-flex align-items-center mb-1">
                    <div class="message-avatar mr-2">
                        ${isAdmin ? 
                        '<div class="avatar bg-primary text-white">A</div>' : 
                        '<div class="avatar bg-success text-white">' + message.senderName.charAt(0) + '</div>'}
                    </div>
                    <div class="message-info">
                        <span class="message-sender font-weight-bold">
                            ${isAdmin ? 'You (Admin)' : message.senderName}
                        </span>
                        <span class="message-time text-muted ml-2">
                            <i class="far fa-clock"></i> ${message.timestamp}
                        </span>
                        ${!isAdmin && !message.is_read ? '<span class="badge badge-warning ml-2">New</span>' : ''}
                    </div>
                </div>
                <div class="message-content">
                    <div class="message-bubble p-3 rounded">
                        ${message.content.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(messageEl);
        }
        
        // Start polling every 3 seconds
        const pollingInterval = setInterval(fetchNewMessages, 3000);
        
        // Clean up interval when user leaves the page
        window.addEventListener('beforeunload', function() {
            clearInterval(pollingInterval);
        });
    }
</script>

<!-- Time Slots Modal -->
<div class="modal fade" id="timeSlotsModal" tabindex="-1" role="dialog" aria-labelledby="timeSlotsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="timeSlotsModalLabel"><i class="fas fa-calendar-alt mr-2"></i> Select Available Time Slots</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'sendTimeSlots', $writingServiceRequest->writing_service_request_id],
                    'id' => 'timeSlotsForm',
                ]) ?>

                <div class="row">
                    <div class="col-md-5">
                        <!-- Calendar -->
                        <h6 class="font-weight-bold mb-3">Select a Date</h6>
                        <div id="datepicker"></div>

                        <div class="text-center mt-3 mb-3">
                            <button type="button" id="loadTimeSlots" class="btn btn-primary">
                                <i class="fas fa-clock mr-1"></i> Load Time Slots
                            </button>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <!-- Time Slots Selection -->
                        <h6 class="font-weight-bold mb-3">Select Time Slots to Offer</h6>

                        <div id="timeSlots-loading" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading available time slots...</p>
                        </div>

                        <div id="timeSlots-empty" class="text-center py-4">
                            <i class="far fa-clock fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Select a date and click "Load Time Slots" to view available times</p>
                        </div>

                        <div id="timeSlots-list" class="d-none">
                            <div id="selected-date-display" class="font-weight-bold mb-3 text-primary"></div>

                            <div id="time-slots-container">
                                <!-- Time slots will be populated via JavaScript -->
                            </div>

                            <div class="mt-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAllTimeSlots">
                                    <label class="custom-control-label" for="selectAllTimeSlots">Select All Time Slots</label>
                                </div>
                            </div>
                        </div>

                        <div id="timeSlots-none" class="text-center py-4 d-none">
                            <i class="fas fa-calendar-times fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No available time slots for this date</p>
                            <p class="text-sm text-gray-400">Please select another date</p>
                        </div>
                    </div>
                </div>

                <!-- Message Text Area -->
                <div class="form-group mt-4">
                    <h6 class="font-weight-bold mb-2">Message to Client</h6>
                    <textarea name="message_text" class="form-control" rows="3" id="timeSlotMessageText" placeholder="Enter a message to accompany the time slots...">I'd like to schedule a consultation to discuss your writing service request. Here are some available time slots. Please click the link below to book one of these times or select another time that works for you."></textarea>

                    <input type="hidden" name="time_slots" id="selectedTimeSlotsJson" value="">
                </div>

                <?= $this->Form->end() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="sendTimeSlots" class="btn btn-success" disabled>
                    <i class="fas fa-paper-plane mr-1"></i> Send Time Slots
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Time slots selection functionality
        const datepicker = $('#datepicker');
        const loadTimeSlotsBtn = document.getElementById('loadTimeSlots');
        const timeSlotsLoading = document.getElementById('timeSlots-loading');
        const timeSlotsEmpty = document.getElementById('timeSlots-empty');
        const timeSlotsNone = document.getElementById('timeSlots-none');
        const timeSlotsContainer = document.getElementById('time-slots-container');
        const timeSlotsListContainer = document.getElementById('timeSlots-list');
        const selectedDateDisplay = document.getElementById('selected-date-display');
        const selectedTimeSlotsJson = document.getElementById('selectedTimeSlotsJson');
        const selectAllCheckbox = document.getElementById('selectAllTimeSlots');
        const sendTimeSlotsBtn = document.getElementById('sendTimeSlots');

        // Initialize datepicker
        datepicker.datepicker({
            minDate: 0, // Today
            maxDate: '+60d', // Allow up to 60 days in the future
            dateFormat: 'yy-mm-dd',
            firstDay: 1, // Start week on Monday
            showOtherMonths: true,
            selectOtherMonths: true,
            beforeShowDay: $.datepicker.noWeekends // Disable weekends
        });

        // Load time slots when the button is clicked
        loadTimeSlotsBtn.addEventListener('click', function() {
            const selectedDate = datepicker.val();

            if (!selectedDate) {
                alert('Please select a date first');
                return;
            }

            loadTimeSlots(selectedDate);
        });

        // Function to load time slots for a selected date
        function loadTimeSlots(date) {
            // Show loading, hide other elements
            timeSlotsEmpty.classList.add('d-none');
            timeSlotsNone.classList.add('d-none');
            timeSlotsListContainer.classList.add('d-none');
            timeSlotsLoading.classList.remove('d-none');

            // Format the date for display
            const formattedDate = new Date(date);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            selectedDateDisplay.textContent = formattedDate.toLocaleDateString('en-US', options);

            // Fetch available time slots
            fetch(`/admin/writing-service-requests/get-available-time-slots?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    timeSlotsLoading.classList.add('d-none');

                    if (data.success && data.timeSlots && data.timeSlots.length > 0) {
                        // Show time slots container
                        timeSlotsListContainer.classList.remove('d-none');

                        // Populate time slots
                        timeSlotsContainer.innerHTML = '';

                        data.timeSlots.forEach(slot => {
                            const slotDiv = document.createElement('div');
                            slotDiv.className = 'custom-control custom-checkbox time-slot-item mb-2';

                            const id = `slot-${slot.date}-${slot.start.replace(':', '-')}`;

                            slotDiv.innerHTML = `
                                <input type="checkbox" class="custom-control-input time-slot-checkbox" id="${id}" data-slot='${JSON.stringify(slot)}'>
                                <label class="custom-control-label" for="${id}">
                                    ${slot.formatted}
                                </label>
                            `;

                            timeSlotsContainer.appendChild(slotDiv);
                        });

                        // Setup the checkboxes for selecting time slots
                        setupTimeSlotCheckboxes();
                    } else {
                        // Show no time slots message
                        timeSlotsNone.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error loading time slots:', error);
                    timeSlotsLoading.classList.add('d-none');
                    timeSlotsNone.classList.remove('d-none');
                });
        }

        // Setup time slot checkboxes
        function setupTimeSlotCheckboxes() {
            const timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox');

            // Handle individual checkbox changes
            timeSlotCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedTimeSlots);
            });

            // Handle select all checkbox
            selectAllCheckbox.addEventListener('change', function() {
                timeSlotCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });

                updateSelectedTimeSlots();
            });

            // Clear previous selection
            selectAllCheckbox.checked = false;
            updateSelectedTimeSlots();
        }

        // Update selected time slots
        function updateSelectedTimeSlots() {
            const selectedTimeSlots = [];
            const timeSlotCheckboxes = document.querySelectorAll('.time-slot-checkbox:checked');

            timeSlotCheckboxes.forEach(checkbox => {
                const slotData = JSON.parse(checkbox.dataset.slot);
                selectedTimeSlots.push(slotData);
            });

            // Update hidden input with selected time slots
            selectedTimeSlotsJson.value = JSON.stringify(selectedTimeSlots);

            // Enable/disable send button based on selection
            sendTimeSlotsBtn.disabled = selectedTimeSlots.length === 0;

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.time-slot-checkbox');
            selectAllCheckbox.checked = timeSlotCheckboxes.length > 0 && timeSlotCheckboxes.length === allCheckboxes.length;
        }

        // Send time slots button
        sendTimeSlotsBtn.addEventListener('click', function() {
            const messageText = document.getElementById('timeSlotMessageText').value.trim();
            const selectedTimeSlots = selectedTimeSlotsJson.value;

            if (!messageText) {
                alert('Please enter a message to accompany the time slots');
                return;
            }

            if (!selectedTimeSlots || selectedTimeSlots === '[]') {
                alert('Please select at least one time slot');
                return;
            }

            // Submit the form
            document.getElementById('timeSlotsForm').submit();
        });
    });
</script>

<?php
// Helper function for determining badge colors
function getStatusClass(string $status): string
{
    return match ($status) {
        'pending' => 'warning',
        'in_progress' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}
?>