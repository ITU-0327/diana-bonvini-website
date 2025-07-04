<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 * @var \App\Model\Entity\Appointment[] $appointments
 * @var \App\Model\Entity\RequestDocument[] $requestDocuments
 */

use Cake\Utility\Inflector;

$this->assign('title', __('Writing Service Request Details'));

// Include local time converter for proper local time display
echo $this->Html->script('local-time-converter', ['block' => false, 'v' => '1.1']);
// Include payment handling JavaScript
echo $this->Html->script('writing-service-payments', ['block' => true]);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" data-request-id="<?= h($writingServiceRequest->writing_service_request_id) ?>">
    <!-- Timezone Indicator (Hidden) -->
    <div class="hidden mb-4 flex justify-end">
        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-600" id="timezone-indicator">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span id="timezone-text">Loading timezone...</span>
        </div>
    </div>
    
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
                                'in_progress' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
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
                            <span class="created-date" data-server-time="<?= $writingServiceRequest->created_at->jsonSerialize() ?>" data-time-format="datetime">
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
                    <div
                        class="chat-container mb-3"
                        id="chat-messages"
                        style="max-height:470px; overflow-y:auto; scroll-behavior:smooth;"
                    >
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

                                                    // Get all confirmed appointments for this request to check which slots are taken
                                                    $confirmedAppointments = [];
                                                    if (isset($appointments)) {
                                                        foreach ($appointments as $appointment) {
                                                            if (
                                                                $appointment->writing_service_request_id == $writingServiceRequest->writing_service_request_id &&
                                                                $appointment->status != 'cancelled' &&
                                                                $appointment->is_deleted == false
                                                            ) {
                                                                $confirmedAppointments[] = [
                                                                    'date' => $appointment->appointment_date->format('l, F j, Y'),
                                                                    'time' => $appointment->appointment_time->format('g:i A')
                                                                ];
                                                            }
                                                        }
                                                    }

                                                    foreach ($matches as $match) {
                                                        $date = trim($match[1]);
                                                        $time = trim($match[2]);

                                                        // Check if this specific slot is already confirmed
                                                        $isThisSlotConfirmed = false;
                                                        foreach ($confirmedAppointments as $confirmed) {
                                                            if ($confirmed['date'] == $date && substr($confirmed['time'], 0, 7) == substr($time, 0, 7)) {
                                                                $isThisSlotConfirmed = true;
                                                                break;
                                                            }
                                                        }

                                                        // Create timeslot item with modern styling
                                                        echo '<div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border">';
                                                        echo '<div>';
                                                        echo '<div class="text-sm font-medium">' . h($date) . '</div>';
                                                        echo '<div class="text-xs text-gray-500">' . h($time) . '</div>';
                                                        echo '</div>';

                                                        if ($isThisSlotConfirmed) {
                                                            // This slot is already confirmed
                                                            echo '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">';
                                                            echo '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                                                            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                            echo 'Confirmed</span>';
                                                        } else {
                                                            // This slot is available - show Accept button
                                                            echo '<a href="' . $this->Url->build(['controller' => 'Calendar', 'action' => 'acceptTimeSlot', '?' => [
                                                                'date' => urlencode($date),
                                                                'time' => urlencode($time),
                                                                'request_id' => $writingServiceRequest->writing_service_request_id,
                                                                'message_id' => $msg->request_message_id,
                                                                'type' => 'writing'
                                                            ]]) . '" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-200">';
                                                            echo '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                                                            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                            echo 'Accept</a>';
                                                        }

                                                        echo '</div>';
                                                    }

                                                    echo '</div>';
                                                    
                                                    // Add helpful text for customers
                                                    if (count($confirmedAppointments) > 0) {
                                                        echo '<div class="mt-3 p-2 bg-blue-50 rounded text-xs text-blue-700">';
                                                        echo '<strong>Note:</strong> You can accept additional time slots if needed. Confirmed slots show with a green checkmark.';
                                                        echo '</div>';
                                                    } else {
                                                        echo '<div class="mt-3 p-2 bg-blue-50 rounded text-xs text-blue-700">';
                                                        echo '<strong>Choose your preferred time:</strong> Click "Accept" next to your preferred time slot(s). You can select multiple slots if needed.';
                                                        echo '</div>';
                                                    }
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
                                                    <span class="message-timestamp" data-server-time="<?= $msg->created_at->jsonSerialize() ?>" data-time-format="datetime">
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
                        'onsubmit' => 'this.querySelector("button[type=submit]").disabled = true;',
                    ]) ?>
                    <div class="mt-4">
                        <?= $this->Form->textarea('reply_message', [
                            'rows'        => 3,
                            'placeholder' => 'Type your message here...',
                            'class'       => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                            'required'    => true,
                            'id'          => 'message-textarea',
                        ]) ?>
                        <small class="text-gray-500 text-xs mt-1 block">
                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12z"></path>
                            </svg>
                            Tip: Press Ctrl+Enter to send quickly
                        </small>
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
                                                <span class="created-date" data-server-time="<?= $payment->created_at ? $payment->created_at->jsonSerialize() : '' ?>" data-time-format="datetime">
                                                    <?= $payment->created_at ? $payment->created_at->format('M d, Y H:i') : 'Unknown' ?>
                                                </span>
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
                                                        on <span class="payment-date" data-server-time="<?= $payment->payment_date->jsonSerialize() ?>" data-time-format="date">
                                                            <?= $payment->payment_date->format('M d, Y') ?>
                                                        </span>
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
            <!-- Documents Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-purple-700 to-purple-500 px-4 py-3">
                    <h2 class="text-lg font-bold text-white flex justify-between items-center">
                        <span>Documents</span>
                        <?php if (!empty($writingServiceRequest->document) || !empty($requestDocuments)): ?>
                            <span class="bg-white bg-opacity-20 rounded-full px-2 py-1 text-xs">
                                <?= (count($requestDocuments) + (!empty($writingServiceRequest->document) ? 1 : 0)) ?>
                            </span>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="p-4">
                    <!-- Upload Form -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Upload Document</h3>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'uploadDocument', $writingServiceRequest->writing_service_request_id],
                            'type' => 'file',
                            'class' => 'space-y-2',
                            'onsubmit' => 'this.querySelector("button[type=submit]").disabled = true;'
                        ]) ?>
                        <div>
                            <?= $this->Form->file('document', [
                                'class' => 'block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
                                'accept' => '.pdf,.doc,.docx',
                                'required' => true
                            ]) ?>
                            <p class="mt-1 text-xs text-gray-500">PDF and Word docs only</p>
                        </div>
                        <div>
                            <?= $this->Form->button('Upload', [
                                'class' => 'w-full inline-flex justify-center items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500',
                                'type' => 'submit'
                            ]) ?>
                        </div>
                        <?= $this->Form->end() ?>
                    </div>

                    <!-- Documents List -->
                    <?php if (!empty($writingServiceRequest->document) || !empty($requestDocuments)): ?>
                        <div class="space-y-2">
                            <h3 class="text-sm font-semibold text-gray-900">Uploaded Documents</h3>
                            <?php if (!empty($writingServiceRequest->document)): ?>
                                <div class="border border-gray-200 rounded-lg p-2 hover:bg-gray-50">
                                    <div class="flex items-start space-x-2">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-900 truncate mb-1" title="<?= h(basename($writingServiceRequest->document)) ?>">
                                                <?= h(basename($writingServiceRequest->document)) ?>
                                            </p>
                                            <div class="text-xs text-gray-500">
                                                <span><?= h(strtoupper(pathinfo($writingServiceRequest->document, PATHINFO_EXTENSION))) ?></span>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <?= $this->Html->link(
                                                '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> View Document',
                                                '/' . $writingServiceRequest->document,
                                                ['escape' => false, 'target' => '_blank', 'class' => 'w-full inline-flex justify-center items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50']
                                            ) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($requestDocuments as $document): ?>
                                <div class="border border-gray-200 rounded-lg p-2 hover:bg-gray-50">
                                    <div class="flex items-start space-x-2">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <?php
                                            $iconClass = match (true) {
                                                str_contains($document->file_type, 'pdf') => 'text-red-500',
                                                str_contains($document->file_type, 'word') || str_contains($document->file_type, 'doc') => 'text-blue-700',
                                                default => 'text-gray-500'
                                            };
                                            ?>
                                            <svg class="w-6 h-6 <?= $iconClass ?>" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-900 truncate mb-1" title="<?= h($document->document_name) ?>">
                                                <?= h($document->document_name) ?>
                                            </p>
                                            <div class="text-xs text-gray-500 space-y-0.5">
                                                <div class="flex items-center justify-between">
                                                    <span><?= h(strtoupper(pathinfo($document->document_name, PATHINFO_EXTENSION))) ?></span>
                                                    <span><?= formatFileSize($document->file_size) ?></span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span>
                                                        <?php if (!empty($document->created_at)): ?>
                                                            <span class="created-date" data-server-time="<?= $document->created_at->jsonSerialize() ?>" data-time-format="date">
                                                                <?= $document->created_at->format('M d, Y') ?>
                                                            </span>
                                                        <?php else: ?>
                                                            Unknown date
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="text-xs">
                                                        <?= $document->uploaded_by === 'admin' ? 'Admin' : 'You' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <?= $this->Html->link(
                                                    '<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> View Document',
                                                    '/' . $document->document_path,
                                                    ['escape' => false, 'target' => '_blank', 'class' => 'w-full inline-flex justify-center items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-purple-500']
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
                            <p class="mt-1 text-xs text-gray-500">Upload documents to share with the admin.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Chat Styles for Client Side */
.chat-container {
    background: #fff;
    border-radius: 8px;
    position: relative;
}

.message-item {
    margin-bottom: 1rem;
    animation: messageSlideIn 0.3s ease-out;
}

.message-item .rounded-lg {
    transition: all 0.2s ease;
}

.message-item:hover .rounded-lg {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Loading animation for send button */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Improved textarea styles */
#message-textarea {
    transition: all 0.2s ease;
    resize: vertical;
    min-height: 80px;
    max-height: 200px;
}

#message-textarea:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
}

/* Enhanced button styles */
#send-message-btn {
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

#send-message-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

#send-message-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Chat loading indicator */
.chat-loading-indicator {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.9);
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    color: #6366f1;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

/* Message status indicators */
.message-sending {
    color: #6366f1 !important;
    font-weight: 500;
}

.message-sent {
    color: #10b981 !important;
}

.message-failed {
    color: #ef4444 !important;
}

/* Improved spacing for message containers */
.space-y-4 > * + * {
    margin-top: 1rem;
}

/* Payment button improvements */
.payment-button {
    transition: all 0.2s ease;
}

.payment-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Responsive improvements */
@media (max-width: 640px) {
    .message-item .max-w-md {
        max-width: 85%;
    }
    
    #message-textarea {
        min-height: 60px;
    }
    
    .chat-container {
        max-height: 400px;
    }
}

/* Better scrollbar for chat */
.chat-container::-webkit-scrollbar {
    width: 6px;
}

.chat-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.chat-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.chat-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Notification style improvements */
.text-indigo-500 {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 Simple chat system initialized');
    
    // Initialize chat container and form elements
    const chatContainer = document.getElementById('chat-messages');
    const messageForm = document.getElementById('message-form');
    const messageTextarea = document.getElementById('message-textarea');
    const sendButton = document.getElementById('send-message-btn');
    const requestId = document.querySelector('[data-request-id]').dataset.requestId;

    // Add Ctrl+Enter shortcut for sending messages (like admin template)
    if (messageTextarea) {
        messageTextarea.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                messageForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Simple form submission handler (exactly like admin template)
    if (messageForm && sendButton) {
        messageForm.addEventListener('submit', function() {
            // Show loading state immediately (like admin template)
            sendButton.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Sending...</span>';
            sendButton.disabled = true;
            
            console.log('📤 Submitting message via form (like admin)');
        });
    }

    // Auto-refresh function to reload chat messages every 10 seconds
    function refreshChat() {
        if (!chatContainer || !requestId) return;
        
        console.log('🔄 Auto-refreshing chat messages...');
        
        fetch(`/writing-service-requests/getMessages/${requestId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.html) {
                // Only update if content has changed
                const currentHTML = chatContainer.innerHTML.trim();
                const newHTML = data.html.trim();
                
                if (currentHTML !== newHTML) {
                    console.log('✅ Chat updated with new messages');
                    chatContainer.innerHTML = data.html;
                    
                    // Update timezone formatting
                    if (window.TimezoneHelper) {
                        window.TimezoneHelper.convertAllTimestamps();
                    }
                    
                    // Scroll to bottom
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                    
                    // Show brief notification if new admin messages
                    const adminMessages = chatContainer.querySelectorAll('.justify-start');
                    if (adminMessages.length > 0) {
                        showNewMessageNotification();
                    }
                }
            }
        })
        .catch(error => {
            console.log('⚠️ Auto-refresh error (normal):', error.message);
        });
    }

    // Start auto-refresh every 10 seconds
    setInterval(refreshChat, 10000);
    
    // Also refresh when page becomes visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(refreshChat, 1000);
        }
    });

    // Simple new message notification
    function showNewMessageNotification() {
        // Create simple notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-indigo-600 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
                <span>New message from admin</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    // Initialize timezone and payment functions
    if (window.TimezoneHelper) {
        const timezoneInfo = window.TimezoneHelper.getUserTimezone();
        const timezoneElement = document.getElementById('timezone-text');
        
        if (timezoneElement && timezoneInfo) {
            let timezoneText = '';
            if (timezoneInfo.isUsingDefault) {
                timezoneText = `Times shown in Melbourne time (${timezoneInfo.abbreviation})`;
            } else {
                const zoneName = timezoneInfo.effectiveTimeZone.split('/').pop().replace('_', ' ');
                timezoneText = `Times shown in your local time: ${zoneName} (${timezoneInfo.abbreviation})`;
            }
            timezoneElement.textContent = timezoneText;
        }
    }

    // Payment status checking (keep existing functionality)
    function checkPaymentStatuses() {
        const paymentButtons = document.querySelectorAll('[data-payment-id]');
        if (paymentButtons.length === 0) return;

        const paymentIds = Array.from(paymentButtons).map(btn => btn.dataset.paymentId);
        const qs = new URLSearchParams({
            paymentIds: JSON.stringify(paymentIds)
        }).toString();

        fetch(`/writing-service-requests/checkPaymentStatus/${requestId}?${qs}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => { 
            if (data.success && data.payments) updatePaymentUI(data.payments); 
        })
        .catch(err => console.error('Error checking payment status:', err));
    }

    function updatePaymentUI(payments) {
        payments.forEach(payment => {
            const container = document.querySelector(`[data-payment-container="${payment.id}"]`);
            if (!container) return;

            const buttonContainer = container.querySelector('.payment-button-container');
            const statusContainer = container.querySelector('.payment-status');
            const statusText = statusContainer.querySelector('.status-text');
            const statusDate = statusContainer.querySelector('.status-date');

            if (payment.isPaid) {
                const button = buttonContainer.querySelector('a');
                button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Payment Complete';
                button.className = 'inline-flex items-center px-4 py-2 rounded bg-green-600 text-white text-sm font-medium payment-button';
                button.href = 'javascript:void(0)';
                button.disabled = true;
                button.removeAttribute('data-payment-id');

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

    // Initialize everything
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    checkPaymentStatuses();
    
    console.log('✅ Simple chat system ready');
});
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
