<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest
 * @var \App\Model\Entity\Appointment[] $appointments
 * @var \App\Model\Entity\CoachingRequestDocument[] $coachingRequestDocuments
 */

use Cake\I18n\FrozenTime;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-wrap -mx-4">
        <!-- Main Content - 2/3 width on large screens -->
        <div class="w-full lg:w-2/3 px-4 mb-8">
            <!-- Request Details Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h1 class="text-xl font-bold text-white">Coaching Request Details</h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                            <?php 
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'in_progress' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'canceled' => 'bg-gray-100 text-gray-800',
                                'cancelled' => 'bg-gray-100 text-gray-800',
                            ];
                            echo $statusColors[$coachingServiceRequest->request_status] ?? 'bg-gray-100 text-gray-800';
                            ?>">
                            <?= ucfirst(str_replace('_', ' ', h($coachingServiceRequest->request_status))) ?>
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2"><?= h($coachingServiceRequest->service_title) ?></h2>
                        <p class="text-sm text-gray-600 mb-1">
                            <span class="font-medium">Request ID:</span> <?= h($coachingServiceRequest->coaching_service_request_id) ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-1">
                            <span class="font-medium">Type:</span> <?= h($coachingServiceRequest->service_type) ?>
                        </p>
                        <p class="text-sm text-gray-600 mb-1">
                            <span class="font-medium">Created:</span> 
                            <span class="local-time" data-datetime="<?= $coachingServiceRequest->created_at->jsonSerialize() ?>">
                                <?= $coachingServiceRequest->created_at->format('Y-m-d H:i') ?>
                            </span>
                        </p>
                    </div>

                    <?php if (!empty($coachingServiceRequest->notes)): ?>
                        <div class="mb-6">
                            <h3 class="text-md font-semibold text-gray-900 mb-2">Your Notes</h3>
                            <div class="bg-gray-50 rounded p-3 text-gray-700">
                                <?= nl2br(h($coachingServiceRequest->notes)) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($coachingServiceRequest->document)): ?>
                        <div class="mb-6">
                            <h3 class="text-md font-semibold text-gray-900 mb-2">Attached Document</h3>
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-600">
                                        <?= $this->Html->link(
                                            basename($coachingServiceRequest->document),
                                            '/' . $coachingServiceRequest->document,
                                            ['target' => '_blank', 'class' => 'hover:underline']
                                        ) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Documents Section -->
                    <?php if (!empty($coachingRequestDocuments)): ?>
                        <div class="mb-6">
                            <h3 class="text-md font-semibold text-gray-900 mb-2">Documents</h3>
                            <div class="bg-gray-50 rounded-lg border border-gray-200">
                                <ul class="divide-y divide-gray-200">
                                    <?php foreach ($coachingRequestDocuments as $document): ?>
                                        <li class="px-4 py-3">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <?php
                                                    $iconClass = 'text-gray-400';
                                                    $fileType = strtolower(pathinfo($document->document_name, PATHINFO_EXTENSION));
                                                    
                                                    if (in_array($fileType, ['pdf'])) {
                                                        $fileIcon = '<svg class="h-6 w-6 ' . $iconClass . '" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>';
                                                    } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                        $fileIcon = '<svg class="h-6 w-6 ' . $iconClass . '" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path></svg>';
                                                    } elseif (in_array($fileType, ['doc', 'docx'])) {
                                                        $fileIcon = '<svg class="h-6 w-6 ' . $iconClass . '" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>';
                                                    } else {
                                                        $fileIcon = '<svg class="h-6 w-6 ' . $iconClass . '" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>';
                                                    }
                                                    ?>
                                                    <div class="flex-shrink-0">
                                                        <?= $fileIcon ?>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900"><?= h($document->document_name) ?></p>
                                                        <p class="text-xs text-gray-500">
                                                            <?= h(ucfirst($document->uploaded_by)) ?> · 
                                                            <span class="local-time" data-datetime="<?= $document->created_at->jsonSerialize() ?>">
                                                                <?= $document->created_at->format('Y-m-d H:i') ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <?= $this->Html->link(
                                                        '<span class="sr-only">Download</span><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>',
                                                        '/' . $document->document_path,
                                                        ['escape' => false, 'target' => '_blank', 'class' => 'text-blue-600 hover:text-blue-800']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="flex justify-between">
                        <div>
                            <?= $this->Html->link(
                                '<i class="fas fa-arrow-left mr-1"></i> Back to List',
                                ['action' => 'index'],
                                ['class' => 'bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded inline-flex items-center text-sm', 'escape' => false]
                            ) ?>
                        </div>
                        <div>
                            <?= $this->Html->link(
                                '<i class="fas fa-edit mr-1"></i> Edit Request',
                                ['action' => 'edit', $coachingServiceRequest->coaching_service_request_id],
                                ['class' => 'bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold py-2 px-4 rounded inline-flex items-center text-sm', 'escape' => false]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Thread Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden" id="messages">
                <div class="bg-gradient-to-r from-indigo-700 to-indigo-500 px-6 py-4">
                    <h2 class="text-lg font-bold text-white">Messages</h2>
                </div>
                <div class="p-6">
                    <!-- Messages Container -->
                    <div class="space-y-4 mb-6">
                        <?php if (!empty($coachingServiceRequest->coaching_request_messages)): ?>
                            <?php foreach ($coachingServiceRequest->coaching_request_messages as $message): ?>
                                <?php 
                                $isAdmin = isset($message->user) && $message->user->user_type === 'admin';
                                $bubbleClass = $isAdmin 
                                    ? 'bg-indigo-100 text-gray-800' 
                                    : 'bg-blue-600 text-white';
                                $alignClass = $isAdmin ? 'justify-start' : 'justify-end';
                                ?>
                                <div class="flex <?= $alignClass ?>">
                                    <div class="max-w-lg">
                                        <div class="flex items-end space-x-2">
                                            <?php if ($isAdmin): ?>
                                                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-medium">A</div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="px-4 py-2 rounded-lg <?= $bubbleClass ?>">
                                                    <?php 
                                                    // Processing for markdown-like format
                                                    $messageText = nl2br(h($message->message));
                                                    
                                                    // Handle bold text with **
                                                    $messageText = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $messageText);
                                                    
                                                    // Check if this message contains time slots
                                                    if ($isAdmin && strpos($message->message, '**Available Time Slots:**') !== false) {
                                                        // This is a time slots message, format it specially
                                                        $parts = explode('**Available Time Slots:**', $message->message, 2);
                                                        
                                                        // Process the first part with proper bold formatting
                                                        $firstPart = nl2br(h($parts[0]));
                                                        $firstPart = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $firstPart);
                                                        echo $firstPart . '<br>';
                                                        
                                                        echo '<div class="font-semibold mt-2 mb-1 text-indigo-800">Available Time Slots:</div>';
                                                        
                                                        // Parse time slots
                                                        if (preg_match_all('/- ([^:]+): ([^\n]+)/', $parts[1], $matches, PREG_SET_ORDER)) {
                                                            echo '<div class="space-y-2 mt-2">';
                                                            
                                                            // Check if ANY appointment exists for this request
                                                            $hasAnyAppointment = false;
                                                            if (isset($appointments)) {
                                                                foreach ($appointments as $appointment) {
                                                                    if ($appointment->coaching_service_request_id == $coachingServiceRequest->coaching_service_request_id &&
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
                                                                        'date' => urlencode($date),
                                                                        'time' => urlencode($time),
                                                                        'request_id' => $coachingServiceRequest->coaching_service_request_id,
                                                                        'message_id' => $message->coaching_request_message_id,
                                                                        'type' => 'coaching'
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
                                                    // Handle payment button special markup
                                                    else if (strpos($message->message, '[PAYMENT_BUTTON]') !== false) {
                                                        $buttonPattern = '/\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/s';
                                                        $messageText = preg_replace_callback($buttonPattern, function($matches) use ($coachingServiceRequest) {
                                                            $paymentId = $matches[1];
                                                            $requestId = $coachingServiceRequest->coaching_service_request_id;

                                                            // Check if this payment is already paid
                                                            $isPaid = false;
                                                            if (!empty($coachingServiceRequest->coaching_service_payments)) {
                                                                foreach ($coachingServiceRequest->coaching_service_payments as $payment) {
                                                                    if ($payment->payment_id == $paymentId && $payment->status === 'paid') {
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
                                                            $statusClass = $isPaid ? 'payment-status mt-2 text-sm flex items-center payment-completed' : 'payment-status mt-2 text-sm flex items-center hidden';
                                                            $statusIcon = $isPaid ? '✅' : '⏳';
                                                            $statusText = $isPaid ? 'Payment received' : 'Checking payment status...';

                                                            return '<div class="'.$containerClass.'" data-payment-container="'.$paymentId.'">
                                                                <!-- Payment button -->
                                                                <div class="payment-button-container">
                                                                    <a href="'.($isPaid ? 'javascript:void(0)' : '/coaching-service-requests/pay/'.$requestId.'/'.$paymentId).'"
                                                                       class="'.$buttonClass.'"
                                                                       '.($isPaid ? 'disabled="disabled"' : 'data-payment-id="'.$paymentId.'').'>
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
                                                        }, $messageText);
                                                        
                                                        echo $messageText;
                                                    } else {
                                                        echo $messageText;
                                                    }
                                                    ?>
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500">
                                                    <?= $isAdmin ? 'Admin' : 'You' ?> · 
                                                    <span class="local-time" data-datetime="<?= $message->created_at->jsonSerialize() ?>">
                                                        <?= $message->created_at->format('Y-m-d H:i') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
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
                        'url' => ['controller' => 'CoachingServiceRequests', 'action' => 'view', $coachingServiceRequest->coaching_service_request_id],
                        'id' => 'reply-form',
                    ]) ?>
                    <div class="mt-4">
                        <?= $this->Form->textarea('reply_message', [
                            'rows' => 3, 
                            'placeholder' => 'Type your message here...',
                            'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <?= $this->Form->button('Send Message', [
                            'class' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
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
                    <h2 class="text-lg font-bold text-white">Service Status</h2>
                </div>
                <div class="p-6">
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-<?= $coachingServiceRequest->request_status === 'pending' ? 'yellow' : 'gray' ?>-100 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-<?= $coachingServiceRequest->request_status === 'pending' ? 'yellow' : 'gray' ?>-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium <?= $coachingServiceRequest->request_status === 'pending' ? 'text-yellow-700' : 'text-gray-500' ?>">Pending</span>
                        </li>
                        <li class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-<?= $coachingServiceRequest->request_status === 'in_progress' ? 'blue' : 'gray' ?>-100 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-<?= $coachingServiceRequest->request_status === 'in_progress' ? 'blue' : 'gray' ?>-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium <?= $coachingServiceRequest->request_status === 'in_progress' ? 'text-blue-700' : 'text-gray-500' ?>">In Progress</span>
                        </li>
                        <li class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-<?= $coachingServiceRequest->request_status === 'completed' ? 'green' : 'gray' ?>-100 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-<?= $coachingServiceRequest->request_status === 'completed' ? 'green' : 'gray' ?>-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium <?= $coachingServiceRequest->request_status === 'completed' ? 'text-green-700' : 'text-gray-500' ?>">Completed</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Upload Document Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-700 to-gray-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white">Upload Document</h2>
                </div>
                <div class="p-6">
                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'CoachingServiceRequests', 'action' => 'uploadDocument', $coachingServiceRequest->coaching_service_request_id],
                        'type' => 'file',
                    ]) ?>
                    <div class="space-y-4">
                        <div>
                            <label for="document" class="block text-sm font-medium text-gray-700 mb-1">Select File</label>
                            <?= $this->Form->file('document', [
                                'class' => 'block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100',
                                'required' => true,
                                'accept' => '.pdf,.jpg,.jpeg,.docx',
                            ]) ?>
                            <p class="mt-1 text-xs text-gray-500">Upload PDF, JPG or DOCX files</p>
                        </div>
                        <div>
                            <?= $this->Form->button('Upload Document', [
                                'class' => 'w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
                            ]) ?>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        if(window.location.hash === '#messages') {
            const messagesSection = document.getElementById('messages');
            if(messagesSection) {
                messagesSection.scrollIntoView();
            }
        }
        
        // Auto-check for payment status updates on page load
        checkPaymentStatuses();
        
        // Setup regular payment status checking
        setInterval(checkPaymentStatuses, 30000); // Check every 30 seconds
    });
    
    // Function to check payment statuses
    function checkPaymentStatuses() {
        const requestId = '<?= $coachingServiceRequest->coaching_service_request_id ?>';
        const paymentButtons = document.querySelectorAll('[data-payment-id]');
        
        if (paymentButtons.length === 0) return;
        
        const paymentIds = Array.from(paymentButtons).map(btn => btn.dataset.paymentId);
        
        fetch(`/coaching-service-requests/checkPaymentStatus/${requestId}`, {
            method: 'POST',
            body: JSON.stringify({ paymentIds }),
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
            }
        })
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
            if (!payment.isPaid) return; // Only update if payment is now paid
            
            const container = document.querySelector(`[data-payment-container="${payment.id}"]`);
            if (!container) return;
            
            // Update button to show completed state
            const button = container.querySelector('.payment-button');
            if (button) {
                button.outerHTML = `
                    <div class="inline-flex items-center px-4 py-2 rounded bg-green-600 text-white text-sm font-medium payment-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Payment Complete
                    </div>
                `;
            }
            
            // Show payment status message
            const statusDiv = container.querySelector('.payment-status');
            if (statusDiv) {
                statusDiv.classList.remove('hidden');
                statusDiv.classList.add('payment-completed');
                
                const statusText = statusDiv.querySelector('.status-text');
                if (statusText) {
                    statusText.textContent = 'Payment received';
                    statusText.classList.add('text-green-600', 'font-medium');
                }
                
                const statusDate = statusDiv.querySelector('.status-date');
                if (statusDate && payment.paidDate) {
                    const paidDate = new Date(payment.paidDate);
                    statusDate.textContent = paidDate.toLocaleDateString();
                }
            }
        });
    }
</script> 