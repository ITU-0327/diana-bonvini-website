<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */

use Cake\Utility\Inflector;

$this->assign('title', $writingServiceRequest->service_title);
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Back button and request title -->
    <div class="flex justify-between items-center mb-6">
        <?= $this->Html->link(
            '<i class="fas fa-arrow-left mr-2"></i> Back to Requests',
            ['action' => 'index'],
            ['class' => 'text-blue-600 hover:text-blue-800 font-medium', 'escape' => false],
        ) ?>
        <h1 class="text-2xl font-bold text-gray-800"><?= h($writingServiceRequest->service_title) ?></h1>
        <div class="bg-<?= getStatusBadgeColor($writingServiceRequest->request_status) ?> py-1 px-3 rounded-full text-sm font-medium text-white">
            <?= h(Inflector::humanize($writingServiceRequest->request_status)) ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chat Section - takes up 2/3 of the space on large screens -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col h-[600px]">
                <!-- Chat Header -->
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <h2 class="font-semibold text-gray-800">Conversation with Admin</h2>
                    </div>
                    <div class="text-sm text-gray-500">
                        Request ID: <?= h($writingServiceRequest->writing_service_request_id) ?>
                    </div>
                </div>
                
                <!-- Chat Messages - scrollable area -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
                    <?php if (!empty($writingServiceRequest->request_messages)) : ?>
                        <?php foreach ($writingServiceRequest->request_messages as $msg) : ?>
                            <?php
                            $isAdmin = isset($msg->user) && strtolower($msg->user->user_type) === 'admin';
                            $msgClasses = $isAdmin
                                ? 'bg-blue-50 border-blue-100 ml-6 lg:ml-12'
                                : 'bg-green-50 border-green-100 mr-6 lg:mr-12';
                            $avatarClasses = $isAdmin
                                ? 'bg-blue-100 text-blue-500'
                                : 'bg-green-100 text-green-500';
                            $avatarInitial = $isAdmin ? 'A' : substr($msg->user->first_name ?? 'U', 0, 1);
                            $alignmentClasses = $isAdmin ? 'items-start' : 'items-end flex-row-reverse';
                            ?>
                            <div class="flex <?= $alignmentClasses ?> gap-3" data-message-id="<?= h($msg->request_message_id) ?>">
                                <!-- Avatar -->
                                <div class="flex-shrink-0 <?= $avatarClasses ?> w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm">
                                    <?= h($avatarInitial) ?>
                                </div>
                                
                                <!-- Message Content -->
                                <div class="max-w-[80%] <?= $msgClasses ?> p-3 rounded-lg border">
                                    <div class="flex flex-col">
                                        <div class="font-semibold text-gray-800 text-sm">
                                            <?= h($isAdmin ? 'Admin' : ($msg->user->first_name . ' ' . $msg->user->last_name)) ?>
                                        </div>
                                        <div class="text-gray-700 mt-1 break-words whitespace-pre-wrap">
                                            <?= nl2br(h($msg->message)) ?>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 self-end">
                                            <?php if (!empty($msg->created_at)) : ?>
                                                <span class="local-time" data-datetime="<?= h($msg->created_at->format('c')) ?>"></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="flex items-center justify-center h-full flex-col gap-3 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            <p>No messages yet. Start the conversation with your request details.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Message Input Form -->
                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <?= $this->Form->create(null, [
                        'url' => ['action' => 'view', $writingServiceRequest->writing_service_request_id],
                        'id' => 'message-form',
                        'class' => 'flex gap-2',
                    ]) ?>
                    <?= $this->Form->textarea('reply_message', [
                        'class' => 'flex-1 border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none',
                        'rows' => '2',
                        'placeholder' => 'Type your message here...',
                        'required' => true,
                    ]) ?>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
        
        <!-- Request Details Panel - takes up 1/3 of the space on large screens -->
        <div class="space-y-6">
            <!-- Service Info Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Service Information</h3>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Service Type:</span>
                        <span class="font-medium text-gray-900"><?= h(Inflector::humanize($writingServiceRequest->service_type)) ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-600">Submitted:</span>
                        <span class="font-medium text-gray-900">
                            <?php if (!empty($writingServiceRequest->created_at)) : ?>
                                <span class="local-time" data-datetime="<?= h($writingServiceRequest->created_at->format('c')) ?>"></span>
                            <?php else : ?>
                                <span>N/A</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Final Price:</span>
                        <span class="font-medium text-gray-900">
                            <?= $writingServiceRequest->final_price === null ? 'Pending Quote' : '$' . number_format($writingServiceRequest->final_price, 2) ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Document Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Documents</h3>
                </div>
                <div class="p-4">
                    <?php if (!empty($writingServiceRequest->document)) : ?>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?= h(basename($writingServiceRequest->document)) ?>
                                </p>
                            </div>
                            <div>
                                <?= $this->Html->link(
                                    '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>',
                                    '/' . $writingServiceRequest->document,
                                    ['class' => 'text-blue-600 hover:text-blue-800 p-1', 'target' => '_blank', 'escape' => false],
                                ) ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-4 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-sm">No documents attached</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Notes Card -->
            <?php if (!empty($writingServiceRequest->notes)) : ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Your Notes</h3>
                </div>
                <div class="p-4">
                    <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                        <p class="text-gray-700 whitespace-pre-wrap"><?= nl2br(h($writingServiceRequest->notes)) ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Format dates to local time
        function formatLocalTimes() {
            const timeElements = document.querySelectorAll('.local-time');
            timeElements.forEach(el => {
                const isoTime = el.dataset.datetime;
                const date = new Date(isoTime);
                el.textContent = date.toLocaleString(undefined, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                });
            });
        }
        
        formatLocalTimes();

        // Scroll chat to bottom
        const chatMessages = document.getElementById('chat-messages');
        function scrollChatToBottom() {
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }
        
        scrollChatToBottom();

        // Auto resize the textarea as user types
        const textarea = document.querySelector('textarea[name="reply_message"]');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Focus the textarea when the page loads
            textarea.focus();
        }

        // Form submission animation
        const messageForm = document.getElementById('message-form');
        if (messageForm) {
            messageForm.addEventListener('submit', function() {
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            });
        }
        
        // Poll for new messages
        const requestId = "<?= h($writingServiceRequest->writing_service_request_id) ?>";
        let lastMessageId = null;
        
        // Get the ID of the last message in the chat
        const allMessages = document.querySelectorAll('#chat-messages .flex');
        if (allMessages && allMessages.length > 0) {
            const lastMessage = allMessages[allMessages.length - 1];
            lastMessageId = lastMessage.dataset.messageId || null;
        }
        
        // Function to add a new message to the chat
        function addMessageToChat(message) {
            const isAdmin = message.sender === 'admin';
            const msgClasses = isAdmin 
                ? 'bg-blue-50 border-blue-100 ml-6 lg:ml-12' 
                : 'bg-green-50 border-green-100 mr-6 lg:mr-12';
            const avatarClasses = isAdmin 
                ? 'bg-blue-100 text-blue-500' 
                : 'bg-green-100 text-green-500';
            const avatarInitial = isAdmin ? 'A' : message.senderName.substr(0, 1);
            const alignmentClasses = isAdmin ? 'items-start' : 'items-end flex-row-reverse';
            
            const newMessageHtml = `
                <div class="flex ${alignmentClasses} gap-3" data-message-id="${message.id}">
                    <div class="flex-shrink-0 ${avatarClasses} w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm">
                        ${avatarInitial}
                    </div>
                    <div class="max-w-[80%] ${msgClasses} p-3 rounded-lg border">
                        <div class="flex flex-col">
                            <div class="font-semibold text-gray-800 text-sm">
                                ${message.senderName}
                            </div>
                            <div class="text-gray-700 mt-1 break-words whitespace-pre-wrap">
                                ${message.content}
                            </div>
                            <div class="text-xs text-gray-500 mt-1 self-end">
                                <span class="local-time" data-datetime="${message.created_at}">${message.timestamp}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // If there are no messages, clear the "no messages" placeholder
            if (allMessages.length === 0) {
                chatMessages.innerHTML = '';
            }
            
            // Add the new message to the chat
            chatMessages.insertAdjacentHTML('beforeend', newMessageHtml);
            
            // Update last message ID
            lastMessageId = message.id;
            
            // Scroll to the bottom
            scrollChatToBottom();
            
            // Play notification sound for admin messages
            if (isAdmin) {
                playNotificationSound();
            }
        }
        
        // Create audio element for notification sound
        const notificationSound = new Audio('/webroot/sounds/notification.mp3');
        function playNotificationSound() {
            notificationSound.play().catch(e => {
                console.log('Audio playback failed:', e);
            });
        }
        
        // Function to fetch new messages
        function fetchNewMessages() {
            const url = `/writing-service-requests/fetch-messages/${requestId}${lastMessageId ? '/' + lastMessageId : ''}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            addMessageToChat(message);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                });
        }
        
        // Poll for new messages every 5 seconds
        const pollingInterval = setInterval(fetchNewMessages, 5000);
        
        // Clear interval when page is unloaded
        window.addEventListener('beforeunload', function() {
            clearInterval(pollingInterval);
        });
    });
</script>

<?php
/**
 * Helper function to get the appropriate badge color for status
 */
function getStatusBadgeColor(string $status): string
{
    return match ($status) {
        'pending' => 'yellow-500',
        'in_progress' => 'blue-500',
        'completed' => 'green-500',
        'cancelled' => 'red-500',
        default => 'gray-500'
    };
}
?>