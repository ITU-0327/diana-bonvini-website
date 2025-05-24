<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */

use Cake\Utility\Inflector;

// Include payment handling JavaScript
echo $this->Html->script('writing-service-payments', ['block' => true]);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" data-request-id="<?= h($writingServiceRequest->writing_service_request_id) ?>">
    <div class="flex flex-wrap -mx-4">
        <!-- Main Content - 2/3 width on large screens -->
        <div class="w-full lg:w-2/3 px-4 mb-8">
            <!-- Request Details Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h1 class="text-xl font-bold text-white">Writing Service Request Details</h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'pending_quote' => 'bg-yellow-100 text-yellow-800',
                                'in_progress' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-gray-100 text-gray-800',
                                'canceled' => 'bg-gray-100 text-gray-800',
                            ];
                            echo $statusColors[$writingServiceRequest->request_status] ?? 'bg-gray-100 text-gray-800';
                            ?>">
                            <?= ucfirst(str_replace('_', ' ', h($writingServiceRequest->request_status))) ?>
                        </span>
        </div>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2"><?= h($writingServiceRequest->service_title) ?></h2>
                        <p class="text-sm text-gray-600 mb-1">
                            <span class="font-medium">Request ID:</span> <?= h($writingServiceRequest->writing_service_request_id) ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-1">
                            <span class="font-medium">Type:</span> <?= h(Inflector::humanize($writingServiceRequest->service_type)) ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-1">
                            <span class="font-medium">Created:</span>
                            <span class="local-time" data-datetime="<?= $writingServiceRequest->created_at->jsonSerialize() ?>">
                                <?= $writingServiceRequest->created_at->format('Y-m-d H:i') ?>
                            </span>
                        </p>
    </div>

                    <?php if (!empty($writingServiceRequest->service_instructions)) : ?>
                        <div class="mb-6">
                            <h3 class="text-md font-semibold text-gray-900 mb-2">Your Instructions</h3>
                            <div class="bg-gray-50 rounded p-3 text-gray-700">
                                <?= nl2br(h($writingServiceRequest->service_instructions)) ?>
                    </div>
                        </div>
                    <?php endif; ?>

                    <!-- Preserve additional functionality but with new styling -->
                    <!-- The remaining code for documents, messages, etc. will be added in subsequent edits -->
                    </div>
                </div>

            <!-- Message Thread Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden" id="messages">
                <div class="bg-gradient-to-r from-indigo-700 to-indigo-500 px-6 py-4">
                    <h2 class="text-lg font-bold text-white">Messages</h2>
                </div>
                <div class="p-6">
                    <!-- Messages Container -->
                    <div class="space-y-4 mb-6" id="chat-messages">
                        <div class="chat-loading-indicator hidden" id="chat-loading">
                            <i class="fas fa-sync-alt fa-spin mr-1"></i> Updating...
                        </div>

                        <?php if (!empty($writingServiceRequest->request_messages)) : ?>
                            <?php foreach ($writingServiceRequest->request_messages as $msg) : ?>
                                <?php
                                $isAdmin = isset($msg->user) && strtolower($msg->user->user_type) === 'admin';
                                $bubbleClass = $isAdmin
                                    ? 'bg-indigo-100 text-gray-800'
                                    : 'bg-blue-600 text-white';
                                $alignClass = $isAdmin ? 'justify-start' : 'justify-end';
                                ?>
                                <div class="flex <?= $alignClass ?>" data-message-id="<?= h($msg->request_message_id) ?>">
                                    <div class="max-w-lg">
                                        <div class="flex items-end space-x-2">
                                            <?php if ($isAdmin) : ?>
                                                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-medium">A</div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="px-4 py-2 rounded-lg <?= $bubbleClass ?>">
                                            <?php
                                                    // Processing for markdown-like format and special elements
                                                    $messageText = nl2br(h($msg->message));

                                                    // Handle bold text with **
                                                    $messageText = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $messageText);

                                            // Check if this message contains time slots
                                            if ($isAdmin && strpos($msg->message, '**Available Time Slots:**') !== false) {
                                                // This is a time slots message, format it specially
                                                $parts = explode('**Available Time Slots:**', $msg->message, 2);

                                                // Process the first part with proper bold formatting
                                                $firstPart = nl2br(h($parts[0]));
                                                $firstPart = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $firstPart);
                                                echo $firstPart . '<br>';

                                                echo '<div class="timeslots-header font-semibold mt-2 mb-1">Available Time Slots:</div>';

                                                // Parse time slots
                                                if (preg_match_all('/- ([^:]+): ([^\n]+)/', $parts[1], $matches, PREG_SET_ORDER)) {
                                                            echo '<div class="time-slots-list space-y-2 mt-2">';

                                                    // Check if ANY appointment exists for this request
                                                    $hasAnyAppointment = false;
                                                    if (isset($appointments)) {
                                                        foreach ($appointments as $appointment) {
                                                            if (
                                                                $appointment->writing_service_request_id == $writingServiceRequest->writing_service_request_id &&
                                                                $appointment->status != 'cancelled' &&
                                                                $appointment->is_deleted == false
                                                            ) {
                                                                $hasAnyAppointment = true;
                                                                break;
                                                            }
                                                        }
                                                    }

                                                    foreach ($matches as $match) {
                                                        $date = trim($match[1]);
                                                        $time = trim($match[2]);

                                                        // Check if this specific slot matches the confirmed appointment
                                                        $isThisSlotBooked = false;
                                                        if (isset($appointments)) {
                                                            foreach ($appointments as $appointment) {
                                                                if (
                                                                    $appointment->appointment_date->format('l, F j, Y') == $date &&
                                                                    $appointment->appointment_time->format('g:i A') == substr($time, 0, 7) &&
                                                                    $appointment->status != 'cancelled' &&
                                                                    $appointment->is_deleted == false
                                                                ) {
                                                                    $isThisSlotBooked = true;
                                                                    break;
                                                                }
                                                            }
                                                        }

                                                                // Create timeslot item with modern styling
                                                                echo '<div class="flex justify-between items-center p-2 bg-gray-50 rounded">';
                                                                echo '<div>';
                                                                echo '<div class="text-sm font-medium">' . h($date) . '</div>';
                                                                echo '<div class="text-xs text-gray-500">' . h($time) . '</div>';
                                                        echo '</div>';

                                                        if ($hasAnyAppointment) {
                                                            // If this is the booked slot, show it as confirmed
                                                            if ($isThisSlotBooked) {
                                                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">';
                                                                        echo '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                                                                        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                                        echo 'Confirmed</span>';
                                                            } else {
                                                                // For other slots, show as unavailable
                                                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">';
                                                                        echo '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                                                                        echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                                                                        echo 'Unavailable</span>';
                                                            }
                                                        } else {
                                                            // No appointment exists yet, show normal accept button
                                                            echo '<a href="' . $this->Url->build(['controller' => 'Calendar', 'action' => 'acceptTimeSlot', '?' => [
                                                                'date' => $date,
                                                                'time' => $time,
                                                                'request_id' => $writingServiceRequest->writing_service_request_id,
                                                                'message_id' => $msg->request_message_id,
                                                                    ]]) . '" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200">';
                                                                    echo '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                                                                    echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                                    echo 'Accept</a>';
                                                        }

                                                        echo '</div>';
                                                    }

                                                    echo '</div>';
                                                }
                                            }
                                                    // Handle payment buttons
                                            elseif (strpos($messageText, '[PAYMENT_BUTTON]') !== false) {
                                                $buttonPattern = '/\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/';
                                                $messageText = preg_replace_callback($buttonPattern, function ($matches) use ($writingServiceRequest) {
                                                    $paymentId = $matches[1];
                                                    $requestId = $writingServiceRequest->writing_service_request_id;

                                                // Check if this payment is already paid
                                                    $isPaid = false;
                                                    if (!empty($writingServiceRequest->writing_service_payments)) {
                                                        foreach ($writingServiceRequest->writing_service_payments as $payment) {
                                                            if ($payment->writing_service_payment_id == $paymentId && $payment->status === 'paid') {
                                                                $isPaid = true;
                                                                break;
                                                            }
                                                        }
                                                    }

                                                            // Create payment container with modern styling
                                                            $containerClass = 'payment-container mt-3';
                                                            $buttonClass = $isPaid ?
                                                        'inline-flex items-center px-4 py-2 rounded bg-green-600 text-white text-sm font-medium payment-button' :
                                                        'inline-flex items-center px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 payment-button';
                                                            $buttonText = $isPaid ? 'Payment Complete' : 'Make Payment';
                                                            $buttonIcon = $isPaid ?
                                                            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' :
                                                            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>';

                                                        // Status class and initial visibility
                                                            $statusClass = $isPaid ? 'payment-status mt-2 text-sm flex items-center payment-completed' : 'payment-status hidden mt-2 text-sm flex items-center';
                                                            $statusIcon = $isPaid ? '✅' : '⏳';
                                                            $statusText = $isPaid ? 'Payment received' : 'Checking payment status...';

                                                            return (function () use (
                                                                $paymentId,
                                                                $requestId,
                                                                $isPaid,
                                                                $containerClass,
                                                                $buttonClass,
                                                                $buttonText,
                                                                $buttonIcon,
                                                                $statusClass,
                                                                $statusIcon,
                                                                $statusText,
                                                            ) {
                                                                /** @var \Cake\View\View $this */

                                                                $payUrl = $isPaid
                                                                    ? 'javascript:void(0)'
                                                                    : $this->Url->build([
                                                                        'controller' => 'WritingServiceRequests',
                                                                        'action'     => 'payDirect',
                                                                        '?' => [
                                                                            'id'        => $requestId,
                                                                            'paymentId' => $paymentId,
                                                                        ],
                                                                    ]);

                                                                return '<div class="' . $containerClass . '" data-payment-container="' . $paymentId . '">
                                                                          <div class="payment-button-container">
                                                                            <a href="' . $payUrl . '"
                                                                               class="' . $buttonClass . '"
                                                                               ' . ($isPaid ? 'disabled="disabled"' : 'data-payment-id="' . $paymentId . '"') . '>
                                                                               ' . $buttonIcon . '
                                                                               ' . $buttonText . '
                                                                            </a>
                                                                          </div>
                                                                          <div class="' . $statusClass . '">
                                                                            <span class="status-icon mr-1">' . $statusIcon . '</span>
                                                                            <span class="status-text ' . ($isPaid ? 'text-green-600 font-medium' : '') . '">' . $statusText . '</span>
                                                                            <span class="status-date ml-2"></span>
                                                                          </div>
                                                                        </div>';
                                                            })();
                                                }, $messageText);

                                                echo $messageText;
                                            } else {
                                                echo $messageText;
                                            }
                                            ?>
                                        </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    <?= $isAdmin ? 'Admin' : 'You' ?> ·
                                                    <span class="local-time" data-datetime="<?= $msg->created_at->jsonSerialize() ?>">
                                                        <?= $msg->created_at->format('Y-m-d H:i') ?>
                                                    </span>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No messages</h3>
                                <p class="mt-1 text-sm text-gray-500">Start the conversation with the admin.</p>
                        </div>
                        <?php endif; ?>
                </div>

                    <!-- Message Input Form -->
                    <?= $this->Form->create(null, [
                        'url' => ['action' => 'view', $writingServiceRequest->writing_service_request_id],
                        'id'  => 'message-form',
                    ]) ?>
                    <div class="mt-4">
                        <?= $this->Form->textarea('reply_message', [
                            'rows'        => 3,
                            'placeholder' => 'Type your message here...',
                            'class'       => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                            'required'    => true,
                            'id'          => 'message-textarea',
                        ]) ?>
                        </div>
                    <div class="mt-3 flex justify-end">
                        <?= $this->Form->button('Send Message', [
                            'class' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
                            'id' => 'send-message-btn',
                        ]) ?>
                        </div>
                        <?= $this->Form->end() ?>
                </div>
            </div>
        </div>

        <!-- Sidebar - 1/3 width on large screens -->
        <div class="w-full lg:w-1/3 px-4">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-700 to-gray-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white">Request Status</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-medium text-gray-600">Current Status:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            <?= $statusColors[$writingServiceRequest->request_status] ?? 'bg-gray-100 text-gray-800' ?>">
                            <?= ucfirst(str_replace('_', ' ', h($writingServiceRequest->request_status))) ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-600">Total Paid:</span>
                        <span class="text-sm font-medium text-gray-900"><?= h($writingServiceRequest->getFormattedTotalPaid()) ?></span>
                    </div>
                        </div>
                        </div>

            <!-- Payment History Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-700 to-blue-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex justify-between items-center">
                        <span>Payment History</span>
                        <?php if (!empty($writingServiceRequest->writing_service_payments)): ?>
                            <span class="bg-white bg-opacity-20 rounded-full px-2 py-1 text-xs">
                                <?= count($writingServiceRequest->writing_service_payments) ?>
                            </span>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (!empty($writingServiceRequest->writing_service_payments)): ?>
                        <div class="space-y-3">
                            <?php 
                            $paymentNumber = 1;
                            foreach ($writingServiceRequest->writing_service_payments as $payment): ?>
                                <div class="border rounded-lg p-3 <?= $payment->status === 'paid' ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50' ?>">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900">
                                                Payment #<?= $paymentNumber ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= $payment->created_at ? $payment->created_at->format('M j, Y g:i A') : 'Unknown' ?>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-gray-900">
                                                $<?= number_format($payment->amount, 2) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <?php if ($payment->status === 'paid'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Paid
                                                </span>
                                                <?php if ($payment->payment_date): ?>
                                                    <span class="ml-2 text-xs text-gray-500">
                                                        on <?= $payment->payment_date->format('M j, Y') ?>
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Pending
                                                </span>
                                                <span class="ml-2 text-xs text-gray-500">Awaiting payment</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($payment->transaction_id): ?>
                                            <div class="text-xs text-gray-400">
                                                ID: <?= h($payment->transaction_id) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php 
                            $paymentNumber++;
                            endforeach; ?>
                        </div>
                        
                        <!-- Payment Summary -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="text-center">
                                    <div class="font-medium text-green-600">
                                        <?php 
                                            $paidCount = 0;
                                            $paidTotal = 0;
                                            foreach ($writingServiceRequest->writing_service_payments as $payment) {
                                                if ($payment->status === 'paid') {
                                                    $paidCount++;
                                                    $paidTotal += $payment->amount;
                                                }
                                            }
                                            echo $paidCount;
                                        ?> Paid
                                    </div>
                                    <div class="text-xs text-gray-500">$<?= number_format($paidTotal, 2) ?></div>
                                </div>
                                <div class="text-center">
                                    <div class="font-medium text-yellow-600">
                                        <?php 
                                            $pendingCount = 0;
                                            $pendingTotal = 0;
                                            foreach ($writingServiceRequest->writing_service_payments as $payment) {
                                                if ($payment->status === 'pending') {
                                                    $pendingCount++;
                                                    $pendingTotal += $payment->amount;
                                                }
                                            }
                                            echo $pendingCount;
                                        ?> Pending
                                    </div>
                                    <div class="text-xs text-gray-500">$<?= number_format($pendingTotal, 2) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <p class="text-sm text-gray-500">No payment requests yet</p>
                            <p class="text-xs text-gray-400 mt-1">Payment requests will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-700 to-gray-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white">Actions</h2>
                        </div>
                <div class="p-6">
                                <?= $this->Html->link(
                                    '<i class="fas fa-arrow-left mr-2"></i> Back to Requests',
                                    ['action' => 'index'],
                                    ['class' => 'w-full mb-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center text-sm', 'escape' => false],
                                ) ?>
                    <?= $this->Html->link(
                        '<i class="fas fa-edit mr-2"></i> Edit Request',
                        ['action' => 'edit', $writingServiceRequest->writing_service_request_id],
                        ['class' => 'w-full bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center text-sm', 'escape' => false],
                    ) ?>
                            </div>
                        </div>
                        </div>
                </div>
            </div>

<!-- Preserving original JavaScript and helper functions at the bottom of the file -->
<!-- Will be preserved in subsequent edits -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Format local times
        const timeElements = document.querySelectorAll('.local-time');
        timeElements.forEach(el => {
            const isoTime = el.dataset.datetime;
            const date = new Date(isoTime);

            el.textContent = date.toLocaleString(undefined, {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            });
        });

        // Scroll to messages section if URL has #messages hash
        if (window.location.hash === '#messages') {
            const messagesSection = document.getElementById('messages');
            if (messagesSection) {
                messagesSection.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Check for payment status updates if there are any payment buttons
        const paymentButtons = document.querySelectorAll('[data-payment-id]');
        if (paymentButtons.length > 0) {
            checkPaymentStatuses();
        }

        // Setup message form submission via AJAX
        const messageForm = document.getElementById('message-form');
        if (messageForm) {
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const textarea = document.getElementById('message-textarea');
                const message = textarea.value.trim();

                if (message === '') {
                    return false;
                }

                const submitButton = document.getElementById('send-message-btn');
                submitButton.disabled = true;

                const formData = new FormData(messageForm);
                const requestId = document.querySelector('[data-request-id]').dataset.requestId;

                const messageForm = document.getElementById('message-form');
                if (messageForm) {
                    messageForm.addEventListener('submit', function (e) {
                        e.preventDefault();

                        const textarea  = document.getElementById('message-textarea');
                        const submitBtn = document.getElementById('send-message-btn');

                        if (textarea.value.trim() === '') return;

                        submitBtn.disabled = true;

                        const url      = messageForm.action;
                        const formData = new FormData(messageForm);

                        fetch(url, {
                            method : 'POST',
                            body   : formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(resp => {
                                if (resp.ok || resp.status === 302) return resp.text();
                                throw new Error('server');
                            })
                            .then(() => {
                                textarea.value = '';
                                loadMessages();
                            })
                            .catch(() => alert('Failed to send message. Please try again.'))
                            .finally(() => { submitBtn.disabled = false; });
                    });
                }

                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        textarea.value = '';
                        loadMessages();
                    } else {
                        alert('Failed to send message. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while sending your message. Please try again.');
                })
                .finally(() => {
                    submitButton.disabled = false;
                });
            });
        }
    });

    // Function to check payment statuses
    function checkPaymentStatuses() {
        const requestId = document.querySelector('[data-request-id]').dataset.requestId;
        const paymentButtons = document.querySelectorAll('[data-payment-id]');

        if (paymentButtons.length === 0) return;

        const paymentIds = Array.from(paymentButtons).map(btn => btn.dataset.paymentId);

        const qs = new URLSearchParams({
            paymentIds: JSON.stringify(paymentIds)      // 把数组放进 query-string
        }).toString();

        fetch(`/writing-service-requests/checkPaymentStatus/${requestId}?${qs}`, {
            method : 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => { if (data.success && data.payments) updatePaymentUI(data.payments); })
            .catch(err => console.error('Error checking payment status:', err));
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payments) {
                updatePaymentUI(data.payments);
            }
        })
        .catch(error => {
            console.error('Error checking payment status:', error);
        });
    }

    // Function to update payment UI based on status
    function updatePaymentUI(payments) {
        payments.forEach(payment => {
            const container = document.querySelector(`[data-payment-container="${payment.id}"]`);
            if (!container) return;

            const buttonContainer = container.querySelector('.payment-button-container');
            const statusContainer = container.querySelector('.payment-status');
            const statusText = statusContainer.querySelector('.status-text');
            const statusDate = statusContainer.querySelector('.status-date');

            if (payment.isPaid) {
                // Update button to show completed state
                const button = buttonContainer.querySelector('a');
                button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Payment Complete';
                button.className = 'inline-flex items-center px-4 py-2 rounded bg-green-600 text-white text-sm font-medium payment-button';
                button.href = 'javascript:void(0)';
                button.disabled = true;
                button.removeAttribute('data-payment-id');

                // Update status
                statusContainer.classList.remove('hidden');
                statusContainer.classList.add('payment-completed');
                statusText.textContent = 'Payment received';
                statusText.classList.add('text-green-600', 'font-medium');

                if (payment.paidDate) {
                    const date = new Date(payment.paidDate);
                    statusDate.textContent = date.toLocaleDateString();
                }
            }
        });
    }

    // Function to load messages via AJAX
    function loadMessages() {
        const requestId = document.querySelector('[data-request-id]').dataset.requestId;
        const messagesContainer = document.getElementById('chat-messages');
        const loadingIndicator = document.getElementById('chat-loading');

        if (!messagesContainer || !loadingIndicator) return;

        loadingIndicator.classList.remove('hidden');

        fetch(`/writing-service-requests/getMessages/${requestId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.html) {
                messagesContainer.innerHTML = data.html;
                // Format timestamps
                const timeElements = messagesContainer.querySelectorAll('.local-time');
                timeElements.forEach(el => {
                    const isoTime = el.dataset.datetime;
                    const date = new Date(isoTime);
                    el.textContent = date.toLocaleString(undefined, {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true,
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        })
        .finally(() => {
            loadingIndicator.classList.add('hidden');
        });
    }
</script>

<?php
// Original helper functions preserved here
function getStatusBadgeColor(string $status): string
{
    return match ($status) {
        'pending', 'pending_quote' => 'green',
        'scheduled' => 'blue',
        'in_progress' => 'indigo',
        'completed' => 'purple',
        'cancelled' => 'red',
        default => 'gray'
    };
}

function getDocumentIconClass(string $mimeType): string
{
    return match (true) {
        str_contains($mimeType, 'pdf') => 'pdf',
        str_contains($mimeType, 'image') => 'image',
        str_contains($mimeType, 'word'), str_contains($mimeType, 'doc') => 'word',
        str_contains($mimeType, 'excel'), str_contains($mimeType, 'sheet') => 'excel',
        default => 'file'
    };
}

function getDocumentIcon(string $mimeType): string
{
    $iconClass = match (true) {
        str_contains($mimeType, 'pdf') => 'pdf text-red-500',
        str_contains($mimeType, 'image') => 'image text-blue-500',
        str_contains($mimeType, 'word'), str_contains($mimeType, 'doc') => 'word text-blue-700',
        str_contains($mimeType, 'excel'), str_contains($mimeType, 'sheet') => 'excel text-green-600',
        default => 'document text-gray-500'
    };

    return '<i class="far fa-file-' . $iconClass . ' fa-lg"></i>';
}

function formatFileSize(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        return round($bytes / (1024 * 1024), 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
