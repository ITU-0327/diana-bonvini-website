<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */

use Cake\Utility\Inflector;

// Include local time converter for proper local time display
echo $this->Html->script('local-time-converter', ['block' => false, 'v' => '1.1']);
?>

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
                            if (strpos($msg->message, '[TIME_SLOTS]') !== false && strpos($msg->message, '[/TIME_SLOTS]') !== false) {
                                $messageText = preg_replace_callback(
                                    '/\[TIME_SLOTS\](.*?)\[\/TIME_SLOTS\]/s',
                                    function ($matches) use ($msg, $writingServiceRequest) {
                                        $timeSlotsJson = trim($matches[1]);
                                        $timeSlots = json_decode($timeSlotsJson, true);

                                        if (!$timeSlots || !is_array($timeSlots)) {
                                            return '<p class="text-red-600">Error loading time slots</p>';
                                        }

                                        $output = '<div class="time-slots-container mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">';
                                        $output .= '<h4 class="text-sm font-semibold text-blue-900 mb-2">📅 Available Time Slots</h4>';

                                        // Group slots by date
                                        $slotsByDate = [];
                                        foreach ($timeSlots as $slot) {
                                            $date = $slot['date'] ?? '';
                                            $time = $slot['time'] ?? '';
                                            if ($date && $time) {
                                                if (!isset($slotsByDate[$date])) {
                                                    $slotsByDate[$date] = [];
                                                }
                                                $slotsByDate[$date][] = $time;
                                            }
                                        }

                                        foreach ($slotsByDate as $date => $times) {
                                            $output .= '<div class="mb-2">';
                                            $output .= '<div class="text-sm font-medium text-blue-800 mb-1">' . date('l, F j, Y', strtotime($date)) . '</div>';
                                            $output .= '<div class="flex flex-wrap gap-2">';

                                            foreach ($times as $time) {
                                                // Check if this time slot has been accepted
                                                $appointmentsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Appointments');
                                                $existingAppointment = $appointmentsTable->find()
                                                    ->where([
                                                        'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                                                        'appointment_date' => $date,
                                                        'appointment_time' => $time,
                                                        'is_deleted' => false,
                                                    ])
                                                    ->first();

                                                if ($existingAppointment) {
                                                    // This slot has been accepted - show as booked
                                                    $output .= '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">';
                                                    $output .= '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                                                    $output .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                    $output .= date('g:i A', strtotime($time)) . ' - Booked</span>';
                                                } else {
                                                    // This slot is available - show Accept button
                                                    $output .= '<a href="' . \Cake\Routing\Router::url([
                                                        'controller' => 'Calendar',
                                                        'action' => 'acceptTimeSlot',
                                                        '?' => [
                                                            'date' => urlencode($date),
                                                            'time' => urlencode($time),
                                                            'request_id' => $writingServiceRequest->writing_service_request_id,
                                                            'message_id' => $msg->request_message_id,
                                                            'type' => 'writing'
                                                        ]
                                                    ]) . '" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-200">';
                                                    $output .= '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">';
                                                    $output .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                    $output .= date('g:i A', strtotime($time)) . ' - Accept</a>';
                                                }
                                            }

                                            $output .= '</div>';
                                            $output .= '</div>';
                                        }

                                        // Add helpful text for customers
                                        $output .= '<div class="mt-3 text-xs text-blue-700">';
                                        $output .= '💡 Click "Accept" to book a time slot, or you can also ';
                                        $output .= '<a href="' . \Cake\Routing\Router::url([
                                            'controller' => 'Calendar',
                                            'action' => 'book',
                                            '?' => [
                                                'request_id' => $writingServiceRequest->writing_service_request_id,
                                                'type' => 'writing'
                                            ]
                                        ]) . '" class="text-blue-600 hover:text-blue-800 underline font-medium">choose your own time</a>.';
                                        $output .= '</div>';

                                        $output .= '</div>';

                                        return $output;
                                    },
                                    $messageText
                                );
                            }

                            // Check if this message contains a payment button
                            if (strpos($msg->message, '[PAYMENT_BUTTON]') !== false && strpos($msg->message, '[/PAYMENT_BUTTON]') !== false) {
                                $messageText = preg_replace_callback(
                                    '/\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/s',
                                    function ($matches) use ($writingServiceRequest) {
                                        $paymentId = trim($matches[1]);

                                        return '<div class="payment-container mt-3" data-payment-container="' . h($paymentId) . '">
                                            <div class="payment-button-container">
                                                <a href="' . $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'pay', $writingServiceRequest->writing_service_request_id, '?' => ['payment_id' => $paymentId]]) . '" 
                                                   class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium payment-button" 
                                                   data-payment-id="' . h($paymentId) . '">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                    </svg>
                                                    Pay Now
                                                </a>
                                            </div>
                                            <div class="payment-status hidden mt-2">
                                                <div class="flex items-center text-sm">
                                                    <span class="status-text"></span>
                                                    <span class="status-date ml-2 text-gray-500"></span>
                                                </div>
                                            </div>
                                        </div>';
                                    },
                                    $messageText
                                );
                            }

                            echo $messageText;
                            ?>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            <?= $isAdmin ? 'Admin' : 'You' ?> ·
                            <span class="message-timestamp" data-server-time="<?= $msg->created_at->jsonSerialize() ?>" data-time-format="datetime">
                                <?= $msg->created_at->format('Y-m-d H:i') ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!$isAdmin) : ?>
                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-medium">
                            <?= substr($msg->user->first_name ?? 'Y', 0, 1) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mb-4"></div>
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