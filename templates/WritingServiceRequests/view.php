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
            <!-- Messages and Communication Log -->
            <div class="card shadow mb-4" id="messages">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-gradient-primary text-white" style="background: linear-gradient(120deg, #4e73df 30%, #224abe 100%);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-comments mr-2 fa-lg"></i>
                        <h6 class="m-0 font-weight-bolder text-uppercase letter-spacing-1" style="font-weight: 800;">Writing Service Enquiry</h6>
                    </div>
                    <div class="d-flex items-center">
                        <div class="request-id-badge">
                            <div class="request-id-label">REQUEST ID</div>
                            <div class="request-id-value"><?= h(substr($writingServiceRequest->writing_service_request_id, 0, 12)) ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="chat-container" style="max-height: 500px; overflow-y: auto; scroll-behavior: smooth; scroll-padding: 10px; overscroll-behavior: contain;" id="chat-messages">
                        <div class="chat-loading-indicator" id="chat-loading">
                            <i class="fas fa-sync-alt fa-spin mr-1"></i> Updating...
                        </div>
                    <?php if (!empty($writingServiceRequest->request_messages)) : ?>
                            <div class="chat-messages">
                        <?php foreach ($writingServiceRequest->request_messages as $msg) : ?>
                            <?php
                            $isAdmin = isset($msg->user) && strtolower($msg->user->user_type) === 'admin';
                                    ?>
                                    <div class="chat-message <?= $isAdmin ? 'admin-message' : 'client-message' ?>" data-message-id="<?= h($msg->request_message_id) ?>">
                                        <div class="message-header d-flex align-items-center mb-1">
                                            <div class="message-avatar mr-2">
                                                <?php if ($isAdmin) : ?>
                                                    <div class="avatar admin-avatar">A</div>
                                                <?php else : ?>
                                                    <div class="avatar client-avatar">
                                                        <?= substr($msg->user->first_name ?? 'C', 0, 1) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="message-info">
                                                <span class="message-sender">
                                                    <?= $isAdmin ? 'Admin' : 'You' ?>
                                                </span>
                                                <span class="message-time">
                                                    <?php if (!empty($msg->created_at)) : ?>
                                                        <?= h($msg->created_at->format('M j, Y h:i A')) ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            <div class="message-bubble">
                                                <div class="message-text">
                                            <?php
                                            // Check if this message contains time slots
                                            $messageText = $msg->message;
                                            if ($isAdmin && strpos($messageText, '**Available Time Slots:**') !== false) {
                                                // This is a time slots message, format it specially
                                                $parts = explode('**Available Time Slots:**', $messageText, 2);

                                                // Process the first part with proper bold formatting
                                                $firstPart = nl2br(h($parts[0]));
                                                $firstPart = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $firstPart);
                                                echo $firstPart . '<br>';

                                                echo '<div class="timeslots-header">Available Time Slots:</div>';

                                                // Parse time slots
                                                if (preg_match_all('/- ([^:]+): ([^\n]+)/', $parts[1], $matches, PREG_SET_ORDER)) {
                                                    echo '<div class="time-slots-list">';

                                                    // Check if ANY appointment exists for this request
                                                    $hasAnyAppointment = false;
                                                    if (isset($appointments)) {
                                                        foreach ($appointments as $appointment) {
                                                            if ($appointment->writing_service_request_id == $writingServiceRequest->writing_service_request_id &&
                                                                $appointment->status != 'cancelled' &&
                                                                $appointment->is_deleted == false) {
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
                                                                if ($appointment->appointment_date->format('l, F j, Y') == $date &&
                                                                    $appointment->appointment_time->format('g:i A') == substr($time, 0, 7) &&
                                                                    $appointment->status != 'cancelled' &&
                                                                    $appointment->is_deleted == false) {
                                                                    $isThisSlotBooked = true;
                                                                    break;
                                                                }
                                                            }
                                                        }

                                                        // Create accept button for each time slot
                                                        echo '<div class="timeslot-item">';
                                                        echo '<div class="timeslot-info">';
                                                        echo '<div class="timeslot-date">' . h($date) . '</div>';
                                                        echo '<div class="timeslot-time">' . h($time) . '</div>';
                                                        echo '</div>';

                                                        if ($hasAnyAppointment) {
                                                            // If this is the booked slot, show it as confirmed
                                                            if ($isThisSlotBooked) {
                                                                echo '<a href="javascript:void(0)" class="timeslot-confirmed-button">';
                                                                echo '<i class="fas fa-calendar-check"></i> Booking Confirmed';
                                                                echo '</a>';
                                                            } else {
                                                                // For other slots, show as unavailable
                                                                echo '<a href="javascript:void(0)" class="timeslot-unavailable-button">';
                                                                echo '<i class="fas fa-calendar-times"></i> Unavailable';
                                                                echo '</a>';
                                                            }
                                                        } else {
                                                            // No appointment exists yet, show normal accept button
                                                            echo '<a href="' . $this->Url->build(['controller' => 'Calendar', 'action' => 'acceptTimeSlot', '?' => [
                                                                'date' => urlencode($date),
                                                                'time' => urlencode($time),
                                                                'request_id' => $writingServiceRequest->writing_service_request_id,
                                                                'message_id' => $msg->request_message_id
                                                            ]]) . '" class="timeslot-accept">';
                                                            echo '<i class="fas fa-check"></i> Accept';
                                                            echo '</a>';
                                                        }

                                                        echo '</div>';
                                                    }

                                                    echo '</div>';

                                                    // Skip the calendar booking link part
                                                    if (strpos($parts[1], '[CALENDAR_BOOKING_LINK]') !== false) {
                                                        $bookingLinkParts = explode('[CALENDAR_BOOKING_LINK]', $parts[1], 2);
                                                        if (isset($bookingLinkParts[1]) && strpos($bookingLinkParts[1], '[/CALENDAR_BOOKING_LINK]') !== false) {
                                                            $afterBookingLink = explode('[/CALENDAR_BOOKING_LINK]', $bookingLinkParts[1], 2);
                                                            if (isset($afterBookingLink[1])) {
                                                                echo '<div class="timeslot-footer">' . nl2br(h(trim($afterBookingLink[1]))) . '</div>';
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    // If parsing fails, just show the raw text
                                                    $secondPart = nl2br(h($parts[1]));
                                                    $secondPart = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $secondPart);
                                                    echo $secondPart;
                                                }
                                            } else {
                                                // Standard message, process for bold formatting
                                                $messageContent = nl2br(h($messageText));
                                                // Convert **bold** to actual bold text
                                                $messageContent = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $messageContent);

                                                // Process payment buttons
                                                if (strpos($messageContent, '[PAYMENT_BUTTON]') !== false) {
                                                    $buttonPattern = '/\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/';
                                                    $messageContent = preg_replace_callback($buttonPattern, function($matches) use ($writingServiceRequest) {
                                                        $paymentId = $matches[1];
                                                        $requestId = $writingServiceRequest->writing_service_request_id;
                                                        $baseUrl = '';  // Base URL will be determined by JS

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

                                                        // Create payment container HTML that matches what the JS expects
                                                        // but with additional classes for paid status if needed
                                                        $containerClass = $isPaid ? 'payment-container mt-3 paid-payment' : 'payment-container mt-3';
                                                        $buttonClass = $isPaid ?
                                                            'bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded inline-flex items-center text-sm payment-button' :
                                                            'bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center text-sm payment-button';
                                                        $buttonText = $isPaid ? 'Payment Complete' : 'Make Payment';
                                                        $buttonIcon = $isPaid ?
                                                            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' :
                                                            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>';

                                                        // Status class and initial visibility
                                                        $statusClass = $isPaid ? 'payment-status mt-2 text-sm flex items-center payment-completed' : 'payment-status hidden mt-2 text-sm flex items-center';
                                                        $statusIcon = $isPaid ? '✅' : '⏳';
                                                        $statusText = $isPaid ? 'Payment received' : 'Checking payment status...';

                                                        return '<div class="'.$containerClass.'" data-payment-container="'.$paymentId.'">
                                                            <!-- Payment button -->
                                                            <div class="payment-button-container '.($isPaid ? 'paid-container' : '').'">
                                                                <a href="'.($isPaid ? 'javascript:void(0)' : '/writing-service-requests/payDirect?id='.$requestId.'&paymentId='.urlencode($paymentId)).'"
                                                                   class="'.$buttonClass.'"
                                                                   '.($isPaid ? 'disabled="disabled"' : 'data-payment-id="'.$paymentId.'"').'>
                                                                    '.$buttonIcon.'
                                                                    '.$buttonText.'
                                                                </a>
                                                            </div>
                                                            <!-- Payment status indicator -->
                                                            <div class="'.$statusClass.'">
                                                                <span class="status-icon mr-1">'.$statusIcon.'</span>
                                                                <span class="status-text '.($isPaid ? 'text-green-600 font-medium' : '').'">'.$statusText.'</span>
                                                                <span class="status-date ml-2"></span>
                                                            </div>
                                                        </div>';
                                                    }, $messageContent);
                                                }

                                                // Process payment confirmations
                                                if (strpos($messageContent, '[PAYMENT_CONFIRMATION]') !== false) {
                                                    $confirmPattern = '/\[PAYMENT_CONFIRMATION\](.*?)\[\/PAYMENT_CONFIRMATION\]/s';
                                                    $messageContent = preg_replace_callback($confirmPattern, function($matches) {
                                                        $content = $matches[1];
                                                        $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
                                                        return '<div class="payment-confirmation">
                                                            <div class="payment-confirmation-header">
                                                                <i class="fas fa-check-circle"></i>
                                                                <span>Payment Confirmation</span>
                                                                <span class="payment-confirmation-status">PAID</span>
                                                            </div>
                                                            <div class="payment-confirmation-body">
                                                                <div class="payment-confirmation-content">'.$content.'</div>
                                                            </div>
                                                        </div>';
                                                    }, $messageContent);
                                                }

                                                echo $messageContent;
                                            }
                                            ?>
                                        </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                    <?php else : ?>
                            <div class="empty-chat">
                                <div class="empty-chat-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <p>No messages yet. Start the conversation with your request details.</p>
                        </div>
                    <?php endif; ?>
                </div>

                    <!-- New Message Form -->
                    <div class="message-form-container">
                    <?= $this->Form->create(null, [
                        'url' => ['action' => 'view', $writingServiceRequest->writing_service_request_id],
                        'id' => 'message-form',
                        ]) ?>

                        <div class="message-textarea-container">
                            <?= $this->Form->textarea('reply_message', [
                                'rows' => 3,
                                'class' => 'message-textarea',
                                'placeholder' => 'Type your message here...',
                                'required' => true,
                                'id' => 'messageText',
                            ]) ?>
                        </div>

                        <div class="message-submit-container">
                            <button type="submit" class="message-submit-button" id="sendButton">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>
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
                    <!-- Upload Document Form -->
                    <div class="mb-4">
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'uploadDocument', $writingServiceRequest->writing_service_request_id],
                            'type' => 'file',
                            'class' => 'document-upload-form',
                        ]) ?>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4">
                            <div class="font-medium text-blue-800 mb-2">Upload New Document</div>
                            <div class="flex flex-wrap items-end gap-3">
                                <?= $this->Form->control('document', [
                                    'type' => 'file',
                                    'class' => 'form-control py-1.5 px-2 border border-gray-300 rounded text-sm',
                                    'label' => false,
                                    'required' => true,
                                    'accept' => '.pdf,.doc,.docx,.txt',
                                ]) ?>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-4 rounded text-sm inline-flex items-center">
                                    <i class="fas fa-upload mr-2"></i> Upload
                                </button>
                            </div>
                            <div class="text-xs text-blue-700 mt-2">
                                Accepted file types: PDF, Word (DOCX), or TXT files.
                            </div>
                        </div>
                        <?= $this->Form->end() ?>
                    </div>

                    <!-- Document List -->
                    <?php if (isset($requestDocuments) && !empty($requestDocuments)): ?>
                        <div class="space-y-3">
                            <?php foreach($requestDocuments as $document): ?>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                                    <div class="<?= getDocumentIconClass($document->file_type) ?> p-2.5 rounded-lg mr-3 text-white">
                                        <i class="<?= getDocumentIcon($document->file_type) ?>"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?= h($document->document_name) ?>
                                        </p>
                                        <div class="flex items-center text-xs text-gray-500">
                                            <span class="mr-2"><?= h(strtoupper($document->file_extension)) ?></span>
                                            <span class="mr-2">&bull;</span>
                                            <span><?= h($document->formatted_size) ?></span>
                                            <span class="mr-2">&bull;</span>
                                            <span>
                                                <?php
                                                if (!empty($document->created_at)) {
                                                    echo h($document->created_at->format('M j, Y h:i A'));
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <a href="<?= '/' . h($document->document_path) ?>"
                                           class="text-blue-600 hover:text-blue-800 p-1.5 bg-white rounded-full shadow-sm transition-colors mr-2"
                                           target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($writingServiceRequest->document)): ?>
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
                    <?php else: ?>
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
        // Removed local time formatting function since we now use server-side formatting

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

                // After form submission, let's refresh the chat to show the new message immediately
                // This will help keep the chat in sync
                setTimeout(function() {
                    if (window.refreshChat) {
                        window.refreshChat();
                    }
                }, 3000); // Wait for the form to be submitted and processed
            });
        }

        // Manual refresh button handler
        const refreshButton = document.getElementById('refresh-chat-button');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                this.classList.add('animate-spin');

                // Use the global refresh function
                if (window.refreshChat) {
                    window.refreshChat();

                    // Stop spinning after a short delay
                    setTimeout(() => {
                        this.classList.remove('animate-spin');
                    }, 1000);
                }
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

<style>
    /* Updated Chat Styles */
    .chat-container {
        padding: 15px;
        background-color: #f8f9fc;
        background-image: linear-gradient(120deg, #fdfbfb 0%, #f9f7fa 100%);
        border-radius: 8px;
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
        scroll-behavior: smooth;
        overscroll-behavior: contain;
        position: relative;
        transition: all 0.2s ease;
    }

    /* Loading indicator styles for real-time updates */
    .chat-loading-indicator {
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.75);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 10;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .chat-loading-indicator.visible {
        opacity: 1;
    }

    /* New message notification */
    .new-message-notification {
        position: fixed;
        bottom: 25px;
        right: 25px;
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 9999;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }

    .new-message-notification:before {
        content: '🔔';
        margin-right: 8px;
        font-size: 1.2rem;
    }

    .new-message-notification.visible {
        transform: translateY(0);
        opacity: 1;
    }

    /* Animation for new messages */
    @keyframes newMessageHighlight {
        0% { background-color: rgba(78, 115, 223, 0.3); }
        100% { background-color: transparent; }
    }

    .chat-message.new-message {
        animation: newMessageHighlight 2s ease-out;
    }

    /* Card styling */
    #messages.card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        border: none;
        transition: all 0.3s;
    }

    #messages.card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    /* Card header styles */
    .card-header.bg-gradient-primary {
        padding: 14px 20px !important;
        border-bottom: none !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .card-header .text-uppercase {
        font-size: 1rem;
        letter-spacing: 1px;
    }

    /* Request ID Badge styling */
    .request-id-badge {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 4px 12px;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }

    .request-id-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        background: rgba(255, 255, 255, 0.25);
    }

    .request-id-label {
        font-size: 0.6rem;
        font-weight: 700;
        opacity: 0.85;
        letter-spacing: 0.5px;
    }

    .request-id-value {
        font-family: monospace;
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    /* Message styles */
    .chat-message {
        margin-bottom: 16px;
        position: relative;
        transition: all 0.3s ease;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .client-message .message-content {
        margin-right: 20%;
    }

    .admin-message .message-content {
        margin-left: 20%;
    }

    /* Message headers */
    .message-header {
        margin-bottom: 6px;
    }

    .message-sender {
        font-weight: 600;
        font-size: 0.9rem;
        color: #2c3e50;
    }

    /* Updated styles for the message time to make it more visible */
    .message-time {
        font-size: 0.75rem;
        color: #5a6268;
        margin-left: 8px;
        background-color: rgba(0,0,0,0.06);
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 500;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }

    .message-time:hover {
        background-color: rgba(0,0,0,0.1);
    }

    /* Avatar styles */
    .message-avatar {
        margin-right: 6px;
    }

    .avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.7rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        transition: transform 0.2s;
    }

    .avatar:hover {
        transform: scale(1.1);
    }

    .admin-avatar {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
    }

    .client-avatar {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        color: white;
    }

    /* Message bubbles */
    .message-bubble {
        padding: 12px 16px;
        border-radius: 12px;
        position: relative;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        max-width: 100%;
        word-wrap: break-word;
        transition: all 0.2s;
    }

    .message-bubble:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        transform: translateY(-1px);
    }

    .admin-message .message-bubble {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        border-left: 3px solid #4e73df;
    }

    .client-message .message-bubble {
        background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        border-left: 3px solid #1cc88a;
    }

    .message-text {
        font-size: 0.95rem;
        line-height: 1.6;
        color: #34495e;
    }

    .message-text strong {
        font-weight: 700;
        color: #1a1a2e;
    }

    /* Empty chat state */
    .empty-chat {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        text-align: center;
        color: #a0aec0;
    }

    .empty-chat-icon {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.6;
    }

    .empty-chat p {
        font-size: 0.95rem;
    }

    /* New Message Form */
    .message-form-container {
        padding: 16px;
        background: linear-gradient(to bottom, #ffffff, #f8f9fc);
        border-top: 1px solid #e3e6f0;
        border-radius: 0 0 12px 12px;
    }

    .message-textarea-container {
        margin-bottom: 14px;
    }

    .message-textarea {
        width: 100%;
        border: 1px solid #d1d9e6;
        border-radius: 10px;
        padding: 12px 16px;
        resize: none;
        font-size: 0.95rem;
        transition: all 0.25s;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }

    .message-textarea:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.25);
        outline: none;
    }

    .message-submit-container {
        display: flex;
        justify-content: flex-end;
    }

    .message-submit-button {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 10px 24px;
        font-weight: 600;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.25s;
        box-shadow: 0 4px 10px rgba(78, 115, 223, 0.3);
    }

    .message-submit-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(78, 115, 223, 0.35);
        background: linear-gradient(135deg, #4668c5 0%, #1c3ea1 100%);
    }

    .message-submit-button i {
        margin-right: 10px;
        font-size: 1rem;
    }

    .message-submit-button:active {
        transform: translateY(1px);
        box-shadow: 0 2px 6px rgba(78, 115, 223, 0.3);
    }

    /* Time slots styling */
    .timeslots-header {
        font-weight: 700;
        margin: 12px 0 8px;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .time-slots-list {
        margin-top: 8px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .timeslot-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.7);
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }

    .timeslot-info {
        display: flex;
        flex-direction: column;
    }

    .timeslot-date {
        font-weight: 600;
        font-size: 0.85rem;
        color: #2c3e50;
    }

    .timeslot-time {
        font-size: 0.8rem;
        color: #7f8c8d;
    }

    .timeslot-confirmed {
        background-color: #4e73df;
        color: white;
        padding: 5px 10px;
        border-radius: 16px;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .timeslot-confirmed-button {
        background-color: #1cc88a;
        color: white;
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        cursor: default;
        box-shadow: 0 2px 4px rgba(28, 200, 138, 0.25);
    }

    .timeslot-confirmed-button:hover {
        background-color: #1cc88a;
        color: white;
        text-decoration: none;
    }

    .timeslot-accept {
        background-color: #1cc88a;
        color: white;
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .timeslot-accept:hover {
        background-color: #169c6a;
        color: white;
        transform: translateY(-1px);
        text-decoration: none;
    }

    .timeslot-footer {
        margin-top: 8px;
        font-size: 0.8rem;
        color: #7f8c8d;
        font-style: italic;
    }

    /* Payment styling */
    .payment-button-container {
        margin-top: 12px;
        background-color: rgba(255, 231, 217, 0.2);
        border: 1px dashed #f6c23e;
        border-radius: 8px;
        padding: 10px;
        transition: all 0.3s ease;
    }

    /* Paid container styling */
    .payment-button-container.paid-container {
        background-color: rgba(28, 200, 138, 0.1);
        border: 1px dashed #1cc88a;
    }

    /* Paid payment container */
    .paid-payment .payment-button-container {
        border-color: #1cc88a;
        background-color: rgba(28, 200, 138, 0.1);
    }

    /* Payment status when completed */
    .payment-status.payment-completed {
        color: #1cc88a;
        font-weight: 600;
    }

    .payment-status.payment-completed .status-icon {
        color: #1cc88a;
    }

    .payment-status.payment-completed .status-text {
        color: #1cc88a !important;
    }

    .payment-confirmation {
        margin-top: 12px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #1cc88a;
        box-shadow: 0 2px 8px rgba(28, 200, 138, 0.15);
    }

    .payment-confirmation-header {
        background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        display: flex;
        align-items: center;
        padding: 8px 12px;
        font-weight: 600;
        color: #1e824c;
        border-bottom: 1px solid #c8e6c9;
    }

    .payment-confirmation-header i {
        margin-right: 8px;
        color: #1cc88a;
    }

    .payment-confirmation-status {
        margin-left: auto;
        background-color: #1cc88a;
        color: white;
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 12px;
    }

    .payment-confirmation-body {
        padding: 10px 12px;
        background-color: white;
    }

    .payment-confirmation-content {
        font-size: 0.9rem;
        line-height: 1.4;
        color: #2c3e50;
    }

    .payment-confirmation-content strong {
        color: #1cc88a;
        font-weight: 700;
    }

    /* Unavailable time slot button */
    .timeslot-unavailable-button {
        background-color: #e74a3b;
        color: white;
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        cursor: default;
        box-shadow: 0 2px 4px rgba(231, 74, 59, 0.25);
        opacity: 0.9;
    }

    .timeslot-unavailable-button:hover {
        background-color: #e74a3b;
        color: white;
        text-decoration: none;
    }

    /* Refresh button animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    #refresh-chat-button {
        transition: all 0.2s ease;
    }

    #refresh-chat-button:hover {
        transform: scale(1.1);
        background-color: rgba(255, 255, 255, 0.3);
    }

    #refresh-chat-button:active {
        transform: scale(0.95);
    }
</style>

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

/**
 * Get CSS class for document icon based on mime type
 */
function getDocumentIconClass(string $mimeType): string
{
    if (strpos($mimeType, 'pdf') !== false) {
        return 'bg-red-500';
    } elseif (strpos($mimeType, 'word') !== false || strpos($mimeType, 'doc') !== false) {
        return 'bg-blue-600';
    } elseif (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'sheet') !== false) {
        return 'bg-green-600';
    } elseif (strpos($mimeType, 'image') !== false) {
        return 'bg-purple-500';
    } else {
        return 'bg-gray-500';
    }
}

/**
 * Get icon for document based on mime type
 */
function getDocumentIcon(string $mimeType): string
{
    if (strpos($mimeType, 'pdf') !== false) {
        return 'fas fa-file-pdf';
    } elseif (strpos($mimeType, 'word') !== false || strpos($mimeType, 'doc') !== false) {
        return 'fas fa-file-word';
    } elseif (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'sheet') !== false) {
        return 'fas fa-file-excel';
    } elseif (strpos($mimeType, 'image') !== false) {
        return 'fas fa-file-image';
    } else {
        return 'fas fa-file-alt';
    }
}

/**
 * Format file size to human-readable format
 */
function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, 1) . ' ' . $units[$pow];
}
?>
