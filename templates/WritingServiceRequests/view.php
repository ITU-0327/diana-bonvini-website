<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */

use Cake\Utility\Inflector;

$this->assign('title', $writingServiceRequest->service_title);
?>
<?php
// Include payment handling JavaScript
echo $this->Html->script('writing-service-payments', ['block' => true]);
?>
<div class="max-w-6xl mx-auto px-4 py-6" data-request-id="<?= h($writingServiceRequest->writing_service_request_id) ?>">
    <!-- Back button and request title -->
    <div class="flex justify-between items-center mb-4">
        <?= $this->Html->link(
            '<i class="fas fa-arrow-left mr-2"></i> Back to Requests',
            ['action' => 'index'],
            ['class' => 'text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200', 'escape' => false],
        ) ?>
        <h1 class="text-2xl font-bold text-gray-800"><?= h($writingServiceRequest->service_title) ?></h1>
        <div class="py-1.5 px-3.5 rounded-full text-sm font-medium text-white bg-<?= getStatusBadgeColor($writingServiceRequest->request_status) ?> shadow-sm">
            <?= h(Inflector::humanize($writingServiceRequest->request_status)) ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chat Section - takes up 2/3 of the space on large screens -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-col h-[650px] border border-gray-100">
                <!-- Chat Header -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-5 py-4 border-b flex justify-between items-center text-white">
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <div class="w-2.5 h-2.5 rounded-full bg-green-300 absolute right-0 bottom-0 animate-pulse"></div>
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                <i class="fas fa-headset text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="font-bold text-lg">Service Support</h2>
                            <p class="text-xs text-blue-100 opacity-80">We typically reply within 1-2 hours</p>
                        </div>
                    </div>
                    <div class="text-sm bg-white/10 px-3 py-1.5 rounded-full">
                        ID: <?= h(substr($writingServiceRequest->writing_service_request_id, 0, 8)) ?>...
                    </div>
                </div>

                <!-- Chat Messages - scrollable area -->
                <div class="flex-1 overflow-y-auto p-1 space-y-0.5 bg-gray-50" id="chat-messages">
                    <?php if (!empty($writingServiceRequest->request_messages)) : ?>
                        <?php foreach ($writingServiceRequest->request_messages as $msg) : ?>
                            <?php
                            $isAdmin = isset($msg->user) && strtolower($msg->user->user_type) === 'admin';
                            // Extremely tight bubbles with minimal padding
                            $msgClasses = $isAdmin
                                ? 'bg-gray-200 border-0 ml-0 lg:ml-1'
                                : 'bg-blue-600 text-white border-0 mr-0 lg:mr-1';
                            $textColor = $isAdmin ? 'text-gray-800' : 'text-white';
                            $timeColor = $isAdmin ? 'text-gray-400' : 'text-blue-100';
                            $alignmentClasses = $isAdmin ? 'items-start' : 'items-end flex-row-reverse';
                            ?>
                            <div class="flex <?= $alignmentClasses ?>" data-message-id="<?= h($msg->request_message_id) ?>">
                                <!-- Message Content - tight bubbles with minimal padding -->
                                <div class="max-w-[90%] <?= $msgClasses ?> px-2 py-0.5 rounded-xl shadow-sm">
                                    <div class="flex flex-col">
                                        <div class="<?= $textColor ?> text-sm break-words whitespace-pre-wrap message-content leading-tight text-center">
                                            <?= nl2br(h($msg->message)) ?>
                                        </div>
                                        <div class="text-[8px] <?= $timeColor ?> self-end opacity-70">
                                            <?php if (!empty($msg->created_at)) : ?>
                                                <span class="local-time" data-datetime="<?= h($msg->created_at->format('c')) ?>"></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="flex items-center justify-center h-full flex-col gap-4 text-gray-400">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <p class="text-center">No messages yet.<br>Start the conversation with your request details.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Message Input Form -->
                <div class="px-3 py-2 border-t border-gray-200 bg-white">
                    <?= $this->Form->create(null, [
                        'url' => ['action' => 'view', $writingServiceRequest->writing_service_request_id],
                        'id' => 'message-form',
                        'class' => 'flex items-end gap-2',
                    ]) ?>
                    <div class="flex-1 relative">
                        <?= $this->Form->textarea('reply_message', [
                            'class' => 'w-full border border-gray-200 rounded-3xl p-2 pr-8 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 resize-none shadow-sm text-sm',
                            'rows' => '1',
                            'placeholder' => 'iMessage',
                            'required' => true,
                        ]) ?>
                        <div class="absolute right-3 bottom-2 text-gray-400">
                            <i class="fas fa-paperclip opacity-60 text-sm"></i>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-2 flex items-center justify-center transition-colors shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" transform="rotate(90, 12, 12)" />
                        </svg>
                    </button>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>

        <!-- Request Details Panel - takes up 1/3 of the space on large screens -->
        <div class="space-y-5">
            <!-- Service Info Card -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        Service Information
                    </h3>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Service Type:</span>
                        <span class="font-medium text-gray-900"><?= h(Inflector::humanize($writingServiceRequest->service_type)) ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500">Submitted:</span>
                        <span class="font-medium text-gray-900">
                            <?php if (!empty($writingServiceRequest->created_at)) : ?>
                                <span class="local-time" data-datetime="<?= h($writingServiceRequest->created_at->format('c')) ?>"></span>
                            <?php else : ?>
                                <span>N/A</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Payment Status:</span>
                        <?php
                        // Get all writing service payments
                        $pendingPayments = 0;
                        $paidPayments = 0;
                        $totalPaid = 0;
                        $totalPending = 0;

                        if (!empty($writingServiceRequest->writing_service_payments)) {
                            foreach ($writingServiceRequest->writing_service_payments as $payment) {
                                if ($payment->status === 'paid') {
                                    $paidPayments++;
                                    $totalPaid += $payment->amount;
                                } else {
                                    $pendingPayments++;
                                    $totalPending += $payment->amount;
                                }
                            }
                        }

                        if ($pendingPayments > 0) {
                            // Show pending payment badge if there are pending payments
                            ?>
                            <span class="py-1 px-2.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                <?= $pendingPayments ?> Pending Payment<?= $pendingPayments > 1 ? 's' : '' ?>
                            </span>
                            <?php
                        } elseif ($paidPayments === 0) {
                            // No payments yet
                            ?>
                            <span class="py-1 px-2.5 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">No Payments</span>
                            <?php
                        } else {
                            // All payments are paid
                            ?>
                            <span class="py-1 px-2.5 bg-green-100 text-green-800 rounded-full text-xs font-medium">All Payments Complete</span>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <?php if ($paidPayments > 0 || $pendingPayments > 0): ?>
                    <div class="mt-3 space-y-2 pt-3 border-t border-gray-100">
                        <?php if ($paidPayments > 0): ?>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i> 
                                Paid (<?= $paidPayments ?>):
                            </span>
                            <span class="font-bold text-green-600">$<?= number_format($totalPaid, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($pendingPayments > 0): ?>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">
                                <i class="fas fa-clock text-yellow-500 mr-1"></i>
                                Pending (<?= $pendingPayments ?>):
                            </span>
                            <span class="font-bold text-yellow-600">$<?= number_format($totalPending, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($paidPayments > 0 && $pendingPayments > 0): ?>
                        <div class="flex justify-between items-center text-sm pt-2 border-t border-gray-100">
                            <span class="text-gray-500 font-medium">Total:</span>
                            <span class="font-bold text-gray-900">$<?= number_format($totalPaid + $totalPending, 2) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Document Card -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-file-alt text-blue-500"></i>
                        Documents
                    </h3>
                </div>
                <div class="p-5">
                    <?php if (!empty($writingServiceRequest->document)) : ?>
                        <div class="flex items-center p-3.5 bg-blue-50 rounded-lg border border-blue-100">
                            <div class="bg-blue-500 p-2.5 rounded-lg mr-3 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?= h(basename($writingServiceRequest->document)) ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?= h(strtoupper(pathinfo($writingServiceRequest->document, PATHINFO_EXTENSION))) ?> Document
                                </p>
                            </div>
                            <div>
                                <?= $this->Html->link(
                                    '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>',
                                    '/' . $writingServiceRequest->document,
                                    ['class' => 'text-blue-600 hover:text-blue-800 p-1.5 bg-white rounded-full shadow-sm transition-colors', 'target' => '_blank', 'escape' => false],
                                ) ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="text-center py-6 text-gray-400 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-sm">No documents attached</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes Card -->
            <?php if (!empty($writingServiceRequest->notes)) : ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-sticky-note text-blue-500"></i>
                            Your Notes
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                            <p class="text-gray-700 whitespace-pre-wrap text-sm leading-relaxed"><?= nl2br(h($writingServiceRequest->notes)) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Payment Status Card - Will be shown when there's an active payment -->
            <div id="payment-status-card" class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-credit-card text-blue-500"></i>
                        Payment Status
                    </h3>
                </div>
                <div class="p-5" id="payment-status-content">
                    <!-- Will be populated dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Success Toast -->
<div id="payment-success-toast" class="fixed bottom-6 right-6 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-lg shadow-xl transform translate-y-20 opacity-0 transition-all duration-500 flex items-center z-50 hidden">
    <div class="bg-white/20 p-2 rounded-full mr-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    </div>
    <div>
        <div class="font-bold text-lg">Payment Successful!</div>
        <div class="text-sm text-green-100">Your payment has been processed and your request is now being handled.</div>
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
                // Super simple time format (1:41 pm)
                el.textContent = date.toLocaleTimeString(undefined, {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                }).toLowerCase();
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
            messageForm.addEventListener('submit', function(e) {
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                
                // Normal form submission - we don't want to show the payment toast
                // No need to prevent default, let the form submit normally
            });
        }

        // Create audio element for notification sound
        const notificationSound = new Audio('/webroot/sounds/notification.mp3');
        function playNotificationSound() {
            notificationSound.play().catch(e => {
                console.log('Audio playback failed:', e);
            });
        }

        // Note: Real-time message fetching is now handled in writing-service-payments.js
        // The functionality has been moved there to keep all AJAX and UI updates in one place
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
