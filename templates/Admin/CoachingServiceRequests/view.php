<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest
 * @var \App\Model\Entity\CoachingRequestDocument[] $coachingRequestDocuments
 */
use Cake\Utility\Inflector;
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chalkboard-teacher mr-2"></i><?= __('Coaching Service Request Details') ?></h6>
                    <ol class="breadcrumb m-0 bg-transparent p-0">
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Dashboard'), ['controller' => 'Admin', 'action' => 'dashboard']) ?></li>
                        <li class="breadcrumb-item"><?= $this->Html->link(__('Coaching Requests'), ['action' => 'index']) ?></li>
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
                    <div>
                        <span class="badge badge-secondary mr-2">ID: <?= h(substr($coachingServiceRequest->coaching_service_request_id, 0, 12)) ?></span>
                        <span class="badge badge-<?= getStatusClass($coachingServiceRequest->request_status) ?> py-2 px-3">
                            <?= ucfirst(str_replace('_', ' ', h($coachingServiceRequest->request_status))) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold"><?= h($coachingServiceRequest->service_title) ?></h5>
                            <p class="text-muted">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Created: <?= $coachingServiceRequest->created_at->format('F j, Y h:i A') ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-tag mr-2"></i>
                                Service Type: <?= h($coachingServiceRequest->service_type) ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-fingerprint mr-2"></i>
                                Request ID: <?= h($coachingServiceRequest->coaching_service_request_id) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">Client Information</h6>
                                    <?php if (isset($coachingServiceRequest->user) && $coachingServiceRequest->user) : ?>
                                        <p class="mb-1">
                                            <i class="fas fa-user mr-2"></i>
                                            <?= h($coachingServiceRequest->user->first_name . ' ' . $coachingServiceRequest->user->last_name) ?>
                                        </p>
                                        <p class="mb-1">
                                            <i class="fas fa-envelope mr-2"></i>
                                            <?= h($coachingServiceRequest->user->email) ?>
                                        </p>
                                        <?php if (!empty($coachingServiceRequest->user->phone_number)) : ?>
                                            <p class="mb-1">
                                                <i class="fas fa-phone mr-2"></i>
                                                <?= h($coachingServiceRequest->user->phone_number) ?>
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
                    <?php if (!empty($coachingServiceRequest->notes)) : ?>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="font-weight-bold mb-3">Client Notes</h6>
                            <div class="card bg-light-yellow">
                                <div class="card-body py-3 px-4">
                                    <p class="card-text"><?= nl2br(h($coachingServiceRequest->notes)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Document Display -->
                    <?php if (!empty($coachingServiceRequest->document)) : ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="font-weight-bold mb-3">Attached Document</h6>
                            <div class="card bg-light border-left-primary">
                                <div class="card-body py-3 px-4 d-flex align-items-center">
                                    <i class="fas fa-file-alt text-primary fa-2x mr-3"></i>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 font-weight-bold"><?= h(basename($coachingServiceRequest->document)) ?></p>
                                    </div>
                                    <a href="<?= '/' . $coachingServiceRequest->document ?>" target="_blank" class="btn btn-sm btn-primary">
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
                    <div class="d-flex align-items-center">
                        <?php $messageCount = !empty($coachingServiceRequest->coaching_request_messages) ? count($coachingServiceRequest->coaching_request_messages) : 0; ?>
                        <span class="badge badge-info px-3 py-2"><?= $messageCount ?> Messages</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chat-container" style="max-height: 500px; overflow-y: auto; scroll-behavior: smooth; scroll-padding: 10px; overscroll-behavior: contain;" id="chat-messages">
                        <?php if (!empty($coachingServiceRequest->coaching_request_messages)) : ?>
                            <div class="chat-messages">
                                <?php foreach ($coachingServiceRequest->coaching_request_messages as $message) : ?>
                                    <?php
                                    $isAdmin = isset($message->user) && $message->user->user_type === 'admin';
                                    ?>
                                    <div class="chat-message <?= $isAdmin ? 'admin-message' : 'client-message' ?>" data-message-id="<?= h($message->coaching_request_message_id) ?>">
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
                                                <div class="message-text">
                                                    <?php
                                                    // Process message content to properly handle markdown-style formatting
                                                    $messageContent = nl2br(h($message->message));
                                                    // Convert **bold** to actual bold text
                                                    $messageContent = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $messageContent);

                                                    // Handle payment button special markup
                                                    if (strpos($message->message, '[PAYMENT_BUTTON]') !== false) {
                                                        $buttonPattern = '/\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/';
                                                        $match = preg_match($buttonPattern, $message->message, $matches);

                                                        if ($match && isset($matches[1])) {
                                                            $paymentId = $matches[1];
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

                                                            // Create payment button HTML with appropriate status
                                                            $buttonHtml = '
                                                                <div class="mt-2" data-payment-container="'.h($paymentId).'">
                                                                    <span class="text-muted small">Payment request status:</span>
                                                                    <div class="d-flex align-items-center mt-1">
                                                                        <button class="btn '.($isPaid ? 'btn-success' : 'btn-warning').' btn-sm payment-button" disabled>
                                                                            <i class="fas fa-'.($isPaid ? 'check-circle' : 'credit-card').' mr-1"></i>
                                                                            '.($isPaid ? 'Payment Complete' : 'Payment Button').'
                                                                        </button>
                                                                        <span class="badge badge-'.($isPaid ? 'success' : 'light').' ml-2">
                                                                            '.($isPaid ? 'PAID' : 'PENDING').'
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            ';

                                                            // Replace the tag with the button
                                                            $messageContent = preg_replace($buttonPattern, $buttonHtml, $messageContent);
                                                        }
                                                    }

                                                    // Handle payment confirmation
                                                    if (strpos($message->message, '[PAYMENT_CONFIRMATION]') !== false) {
                                                        $confirmPattern = '/\[PAYMENT_CONFIRMATION\](.*?)\[\/PAYMENT_CONFIRMATION\]/s';
                                                        $match = preg_match($confirmPattern, $message->message, $matches);

                                                        if ($match && isset($matches[1])) {
                                                            // Get the content and format it
                                                            $confirmationContent = $matches[1];
                                                            // Format the confirmation message (convert markdown bold to HTML)
                                                            $confirmationContent = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $confirmationContent);

                                                            // Create an elegant payment confirmation card
                                                            $confirmationHtml = '
                                                                <div class="payment-confirmation-card mt-2 border border-success rounded shadow-sm">
                                                                    <div class="payment-confirmation-header d-flex align-items-center bg-success-light border-bottom border-success p-2">
                                                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                                                        <span class="font-weight-bold text-success">Payment Confirmation</span>
                                                                        <span class="badge badge-pill badge-success ml-auto">PAID</span>
                                                                    </div>
                                                                    <div class="payment-confirmation-body p-3">
                                                                        <div class="payment-confirmation-content">
                                                                            ' . $confirmationContent . '
                                                                        </div>
                                                                        <div class="d-flex align-items-center mt-2 pt-2 border-top">
                                                                            <i class="fas fa-info-circle text-primary mr-2"></i>
                                                                            <span class="text-muted small">This payment has been recorded and the client has been notified</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            ';

                                                            // Replace the tag with the confirmation
                                                            $messageContent = preg_replace($confirmPattern, $confirmationHtml, $messageContent);
                                                        }
                                                    }

                                                    echo $messageContent;
                                                    ?>
                                                </div>
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
                    <div class="new-message-form mt-3 pt-3 border-top">
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'sendMessage', $coachingServiceRequest->coaching_service_request_id],
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
                            <small class="form-text text-muted">You can use **text** for bold formatting.</small>
                        </div>

                        <div class="form-group mb-0 d-flex justify-content-between align-items-center">
                            <div class="action-buttons">
                                <!-- Buttons moved to sidebar - Calendar and Payment modal approach -->
                            </div>
                            <button type="submit" class="btn btn-primary px-4" id="sendButton">
                                <i class="fas fa-paper-plane mr-1"></i>
                                Send Message
                            </button>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>

            <!-- Documents Card -->
            <?php if (!empty($coachingRequestDocuments)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Documents</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Document Name</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coachingRequestDocuments as $document): ?>
                                <tr>
                                    <td><?= h($document->document_name) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $document->uploaded_by === 'admin' ? 'primary' : 'info' ?>">
                                            <?= ucfirst(h($document->uploaded_by)) ?>
                                        </span>
                                    </td>
                                    <td><?= $document->created_at->format('M d, Y h:i A') ?></td>
                                    <td>
                                        <a href="<?= $this->Url->build('/' . h($document->document_path), ['fullBase' => true]) ?>"
                                           class="btn btn-sm btn-primary"
                                           target="_blank">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?= $this->Url->build('/' . h($document->document_path), ['fullBase' => true, 'download' => true]) ?>"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> Download
                                        </a>
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

        <!-- Sidebar with Actions -->
        <div class="col-lg-4">
            <!-- Action Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Request Actions</h6>
                </div>
                <div class="card-body">
                    <!-- Status Update Form -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Update Status</h6>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'updateStatus', $coachingServiceRequest->coaching_service_request_id],
                            'class' => 'status-update-form',
                        ]) ?>
                        <div class="form-group">
                            <?= $this->Form->select('status', [
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'canceled' => 'Canceled'
                            ], [
                                'class' => 'form-control',
                                'value' => $coachingServiceRequest->request_status
                            ]) ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sync-alt mr-1"></i> Update Status
                        </button>
                        <?= $this->Form->end() ?>
                    </div>

                    <!-- Document Upload Section -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Document Management</h6>
                        <div class="card bg-light border mb-3">
                            <div class="card-body p-3">
                                <?= $this->Form->create(null, [
                                    'url' => ['action' => 'uploadDocument', $coachingServiceRequest->coaching_service_request_id],
                                    'type' => 'file',
                                    'class' => 'document-upload-form',
                                ]) ?>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Upload Document</label>
                                    <?= $this->Form->control('document', [
                                        'type' => 'file',
                                        'class' => 'form-control-file',
                                        'label' => false,
                                        'required' => true,
                                        'accept' => '.pdf,.doc,.docx,.txt,.jpg,.jpeg',
                                    ]) ?>
                                    <small class="form-text text-muted">Accepted: PDF, Word, TXT, or JPEG files</small>
                                </div>
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fas fa-upload mr-1"></i> Upload Document
                                </button>
                                <?= $this->Form->end() ?>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Request Section -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Payment Management</h6>
                        <button type="button" class="btn btn-warning btn-block" id="paymentOptionsBtn" data-toggle="modal" data-target="#paymentRequestModal">
                            <i class="fas fa-credit-card mr-1"></i> Send Payment Request
                        </button>
                        <p class="text-sm text-muted mt-1">Send a payment request link to the client</p>
                    </div>

                    <!-- Payment History -->
                    <div class="mb-4 pt-2 border-top">
                        <h6 class="font-weight-bold mb-2 d-flex justify-content-between">
                            <span>Payment History</span>
                            <?php if (!empty($coachingServiceRequest->coaching_service_payments)): ?>
                                <span class="badge badge-info"><?= count($coachingServiceRequest->coaching_service_payments) ?></span>
                            <?php endif; ?>
                        </h6>

                        <?php if (!empty($coachingServiceRequest->coaching_service_payments)): ?>
                            <div class="table-responsive">
                                <table id="payment-history-table" class="table table-sm table-hover border">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Payment #</th>
                                            <th>Amount</th>
                                            <th>Date Created</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $paymentNumber = 1;
                                        foreach ($coachingServiceRequest->coaching_service_payments as $payment): ?>
                                            <tr class="<?= $payment->status === 'paid' ? 'table-success' : 'table-warning' ?>">
                                                <td class="small font-weight-bold">
                                                    #<?= $paymentNumber ?>
                                                </td>
                                                <td class="font-weight-bold">
                                                    $<?= number_format($payment->amount, 2) ?>
                                                </td>
                                                <td class="text-muted small">
                                                    <?= $payment->created_at ? $payment->created_at->format('M j, Y g:i A') : 'Unknown' ?>
                                                </td>
                                                <td>
                                                    <?php if ($payment->status === 'paid'): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle mr-1"></i>Paid
                                                        </span>
                                                        <?php if ($payment->payment_date): ?>
                                                            <small class="d-block text-muted mt-1">
                                                                <?= $payment->payment_date->format('M j, Y g:i A') ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock mr-1"></i>Pending
                                                        </span>
                                                        <small class="d-block text-muted mt-1">Awaiting payment</small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php 
                                        $paymentNumber++;
                                        endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">Total Requests:</td>
                                            <td class="font-weight-bold"><?= count($coachingServiceRequest->coaching_service_payments) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- Payment Statistics -->
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-check-circle text-success mr-1"></i>
                                        Paid: <?php 
                                            $paidCount = 0;
                                            foreach ($coachingServiceRequest->coaching_service_payments as $payment) {
                                                if ($payment->status === 'paid') $paidCount++;
                                            }
                                            echo $paidCount;
                                        ?>
                                    </small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">
                                        <i class="fas fa-clock text-warning mr-1"></i>
                                        Pending: <?php 
                                            $pendingCount = 0;
                                            foreach ($coachingServiceRequest->coaching_service_payments as $payment) {
                                                if ($payment->status === 'pending') $pendingCount++;
                                            }
                                            echo $pendingCount;
                                        ?>
                                    </small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-3 bg-light rounded border text-muted">
                                <i class="fas fa-info-circle mr-1"></i> No payment requests yet
                                <small class="d-block mt-1">Use the "Send Payment Request" button above to create payment requests for this service.</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Time Slots Section -->
                    <div class="mb-4 pt-2 border-top">
                        <h6 class="font-weight-bold mb-2">Calendar Management</h6>
                        <?= $this->Html->link(
                            '<i class="fab fa-google mr-1"></i> View My Calendar',
                            ['controller' => 'GoogleAuth', 'action' => 'viewCalendar'],
                            ['class' => 'btn btn-info btn-block', 'escape' => false]
                        ) ?>
                        <p class="text-sm text-muted mt-1">View and manage your Google Calendar appointments</p>

                        <!-- Schedule Consultation -->
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#timeSlotsModal">
                                <i class="fas fa-calendar-alt mr-1"></i> Offer Available Time Slots
                            </button>
                            <p class="text-sm text-muted mt-1">Select and send available time slots to the client</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-light-yellow {
    background-color: rgba(255, 243, 205, 0.5);
}
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.chat-message {
    margin-bottom: 1.5rem;
    max-width: 85%;
}
.admin-message {
    margin-left: auto;
}
.client-message {
    margin-right: auto;
}
.message-bubble {
    position: relative;
}
.admin-message .message-bubble {
    background-color: #e3f2fd;
    color: #0d47a1;
}
.client-message .message-bubble {
    background-color: #f5f5f5;
    color: #212121;
}
.avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.small-box {
    border-radius: 0.35rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    position: relative;
    padding: 1rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.small-box .inner {
    padding: 10px;
}
.small-box h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 5px 0;
    white-space: nowrap;
    padding: 0;
}
.small-box .icon {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 2rem;
    opacity: 0.3;
}
.bg-info { background-color: #36b9cc !important; }
.bg-success { background-color: #1cc88a !important; }
.bg-warning { background-color: #f6c23e !important; }
.bg-primary { background-color: #4e73df !important; }

/* Payment Card Styles */
.payment-confirmation-card {
    transition: all 0.2s ease;
    margin-top: 8px;
    margin-bottom: 8px;
}
.payment-confirmation-card:hover {
    box-shadow: 0 .25rem 0.5rem rgba(0,0,0,.1)!important;
}
.bg-success-light {
    background-color: rgba(28, 200, 138, 0.15);
}
.payment-confirmation-content {
    line-height: 1.4;
    font-size: 0.95rem;
}
.payment-confirmation-content strong {
    color: #28a745;
    font-weight: 600;
}

/* Chat loading indicator */
.chat-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 15px 25px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    z-index: 100;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.chat-loading.active {
    opacity: 1;
}

.chat-loading span {
    margin-top: 10px;
    font-weight: 500;
    color: #4e73df;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* CRITICAL DATEPICKER STYLES - THESE ENSURE THE DATEPICKER IS VISIBLE */
/* Ensure the datepicker container is visible */
#datepicker {
    min-height: 200px !important;
    width: 100% !important;
    background: white !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
}

/* Make inline datepicker fill the container nicely */
#datepicker .ui-datepicker {
    width: 100% !important;
    margin: 0 !important;
    position: static !important;
    display: block !important;
}

/* Fallback styles in case jQuery UI CSS doesn't load */
.ui-datepicker {
    z-index: 9999 !important;
    background: #fff !important;
    border: 1px solid #ddd !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    font-size: 13px !important;
    padding: 0 !important;
    font-family: Arial, sans-serif !important;
}

.ui-datepicker table {
    width: 100% !important;
    margin: 0 !important;
    border-collapse: collapse !important;
}

.ui-datepicker td, .ui-datepicker th {
    padding: 2px !important;
    text-align: center !important;
    border: 1px solid #e0e0e0 !important;
}

.ui-datepicker td a {
    padding: 8px !important;
    text-align: center !important;
    display: block !important;
    text-decoration: none !important;
    color: #333 !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.ui-datepicker td a:hover {
    background: #4e73df !important;
    color: white !important;
}

.ui-datepicker .ui-datepicker-today a {
    background: #f8f9fa !important;
    font-weight: bold !important;
    border: 2px solid #4e73df !important;
}

.ui-datepicker .ui-state-active a {
    background: #4e73df !important;
    color: white !important;
}

.ui-datepicker .ui-datepicker-header {
    background: #4e73df !important;
    color: white !important;
    text-align: center !important;
    padding: 10px !important;
    font-weight: bold !important;
}

.ui-datepicker .ui-datepicker-prev,
.ui-datepicker .ui-datepicker-next {
    cursor: pointer !important;
    color: white !important;
    position: absolute !important;
    top: 10px !important;
    padding: 5px !important;
}

.ui-datepicker .ui-datepicker-prev {
    left: 10px !important;
}

.ui-datepicker .ui-datepicker-next {
    right: 10px !important;
}

.ui-datepicker .ui-datepicker-title {
    text-align: center !important;
    font-weight: bold !important;
}

/* If datepicker still doesn't show, make the container more explicit */
#datepicker .ui-widget-content {
    background: white !important;
    border: 1px solid #ddd !important;
    display: block !important;
    visibility: visible !important;
}

/* Modal z-index fix */
.modal {
    z-index: 1050 !important;
}

.modal-backdrop {
    z-index: 1040 !important;
}
</style>

<!-- Load jQuery UI for datepicker directly -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<?= $this->Html->script('coaching-service-payments.js', ['block' => true]) ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery UI is loaded before proceeding
    console.log('jQuery available:', typeof $ !== 'undefined');
    console.log('jQuery UI available:', typeof $.ui !== 'undefined');

    // Initialize datepicker immediately when modal is shown
        $('#timeSlotsModal').on('shown.bs.modal', function() {
        console.log('Modal opened, initializing datepicker...');
        
        // Wait a bit for modal to fully render
        setTimeout(function() {
            const $datepicker = $('#datepicker');
            console.log('Datepicker element found:', $datepicker.length);
            
            if ($datepicker.length && typeof $.fn.datepicker !== 'undefined') {
                // Destroy existing datepicker if present
                if ($datepicker.hasClass('hasDatepicker')) {
                    $datepicker.datepicker('destroy');
                }

        // Initialize datepicker
                $datepicker.datepicker({
                    minDate: 0,
                    maxDate: '+60d',
                    dateFormat: 'yy-mm-dd',
                    firstDay: 1,
                    showOtherMonths: true,
                    selectOtherMonths: true,
                    changeMonth: true,
                    changeYear: true,
                    inline: true, // Show inline immediately
                    onSelect: function(dateText) {
                        console.log('Date selected:', dateText);
                        $('#selected-date-display').text(new Date(dateText).toLocaleDateString('en-US', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        }));
                        $('#loadTimeSlots').html('<i class="fas fa-clock mr-1"></i> Load Time Slots <span class="badge badge-light ml-1">' + dateText + '</span>');
                        $('#loadTimeSlots').removeClass('btn-primary').addClass('btn-success');
                    }
                });
                
                // Force the datepicker to show inline and be visible
                $datepicker.datepicker('widget').show();
                console.log('Datepicker initialized successfully');
            } else {
                console.error('jQuery UI datepicker not available, using fallback HTML5 date input');
                // Fallback: Create HTML5 date input
                createFallbackDatePicker($datepicker);
            }
        }, 200);
    });

    // Fallback date picker function
    function createFallbackDatePicker($container) {
        console.log('Creating fallback date picker');

        // Get today's date for min attribute
        const today = new Date();
        const minDate = today.toISOString().split('T')[0];
        
        // Get 60 days from now for max attribute  
        const maxDate = new Date(today.getTime() + (60 * 24 * 60 * 60 * 1000));
        const maxDateStr = maxDate.toISOString().split('T')[0];
        
        // Create HTML5 date input
        const dateInput = `
            <div class="fallback-datepicker p-3">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle mr-2"></i>
                    Please select a date for your consultation
                </p>
                <input type="date" 
                       id="fallback-date-input" 
                       class="form-control" 
                       min="${minDate}" 
                       max="${maxDateStr}"
                       style="font-size: 16px; padding: 10px;">
                <small class="form-text text-muted mt-2">
                    Select a date between today and ${maxDate.toLocaleDateString()}
                </small>
            </div>
        `;
        
        // Replace the datepicker container with HTML5 input
        $container.html(dateInput);
        
        // Add event listener for the fallback input
        $('#fallback-date-input').on('change', function() {
            const selectedDate = this.value;
            if (selectedDate) {
                console.log('Fallback date selected:', selectedDate);
                $('#selected-date-display').text(new Date(selectedDate).toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                }));
                $('#loadTimeSlots').html('<i class="fas fa-clock mr-1"></i> Load Time Slots <span class="badge badge-light ml-1">' + selectedDate + '</span>');
                $('#loadTimeSlots').removeClass('btn-primary').addClass('btn-success');
            }
        });
    }

    // Also try to initialize datepicker on document ready
    $(document).ready(function() {
        console.log('Document ready, jQuery UI datepicker available:', typeof $.fn.datepicker !== 'undefined');

        // Try to initialize datepicker on page load as well
        if (typeof $.fn.datepicker !== 'undefined') {
            console.log('Attempting early datepicker initialization');
            try {
                const $datepicker = $('#datepicker');
                if ($datepicker.length && !$datepicker.hasClass('hasDatepicker')) {
                    $datepicker.datepicker({
                        minDate: 0,
                        maxDate: '+60d',
                        dateFormat: 'yy-mm-dd',
                        firstDay: 1,
                        showOtherMonths: true,
                        selectOtherMonths: true,
                        changeMonth: true,
                        changeYear: true,
                        inline: true,
                        onSelect: function(dateText) {
                            console.log('Date selected (early init):', dateText);
                            $('#selected-date-display').text(new Date(dateText).toLocaleDateString('en-US', { 
                                weekday: 'long', 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            }));
                            $('#loadTimeSlots').html('<i class="fas fa-clock mr-1"></i> Load Time Slots <span class="badge badge-light ml-1">' + dateText + '</span>');
                            $('#loadTimeSlots').removeClass('btn-primary').addClass('btn-success');
                        }
                    });
                    console.log('Early datepicker initialization successful');
                }
            } catch (e) {
                console.log('Early datepicker initialization failed, will try again on modal open:', e);
            }
        }
    });

    // Time slots selection functionality - MISSING FUNCTIONALITY ADDED
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

    // Helper function for debugging
    function debugLog(message, data) {
        const debugging = true; // Set to false in production
        if (debugging && console) {
            if (data) {
                console.log(`[CoachingTimeSlots] ${message}:`, data);
            } else {
                console.log(`[CoachingTimeSlots] ${message}`);
            }
        }
    }

        // Load time slots when the button is clicked
        if (loadTimeSlotsBtn) {
            loadTimeSlotsBtn.addEventListener('click', function() {
            let selectedDate = null;
            let formattedDate = null;
            
            // Try to get date from jQuery UI datepicker first
            try {
                selectedDate = $('#datepicker').datepicker('getDate');
                if (selectedDate) {
                // Format date as YYYY-MM-DD
                const year = selectedDate.getFullYear();
                const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const day = String(selectedDate.getDate()).padStart(2, '0');
                    formattedDate = `${year}-${month}-${day}`;
                }
            } catch (e) {
                console.log('jQuery datepicker not available, checking fallback');
            }
            
            // If jQuery UI datepicker didn't work, try the fallback HTML5 input
            if (!formattedDate) {
                const fallbackInput = document.getElementById('fallback-date-input');
                if (fallbackInput && fallbackInput.value) {
                    formattedDate = fallbackInput.value;
                    console.log('Using fallback date input:', formattedDate);
                }
            }
            
            // Also check the stored date from the fallback
            if (!formattedDate && window.selectedDateForTimeSlots) {
                formattedDate = window.selectedDateForTimeSlots;
                console.log('Using stored fallback date:', formattedDate);
            }
            
            console.log('Load button clicked, formatted date:', formattedDate);

            if (!formattedDate) {
                alert('Please select a date first');
                return;
            }

                loadTimeSlots(formattedDate);
            });
        }

        // Function to load time slots for a selected date
        function loadTimeSlots(date) {
            debugLog(`Loading time slots for date: ${date}`);

            // Show loading, hide other elements
        if (timeSlotsEmpty) timeSlotsEmpty.classList.add('d-none');
        if (timeSlotsNone) timeSlotsNone.classList.add('d-none');
        if (timeSlotsListContainer) timeSlotsListContainer.classList.add('d-none');
        if (timeSlotsLoading) timeSlotsLoading.classList.remove('d-none');

            // Format the date for display
            const formattedDate = new Date(date);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        if (selectedDateDisplay) {
            selectedDateDisplay.textContent = formattedDate.toLocaleDateString('en-US', options);
        }

            // Get CSRF token from the document
            let csrfToken;
            try {
                const csrfElement = document.querySelector('input[name="_csrfToken"]');
                csrfToken = csrfElement ? csrfElement.value : '<?= $this->request->getAttribute('csrfToken') ?>';
                debugLog('Using CSRF token', csrfToken.substring(0, 10) + '...');
            } catch (e) {
                console.error('Error getting CSRF token:', e);
                csrfToken = '<?= $this->request->getAttribute('csrfToken') ?>';
            }

        // Build the URL for coaching service requests (not writing service requests)
            const url = `<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'getAvailableTimeSlots', 'prefix' => 'Admin']) ?>?date=${date}`;
            debugLog('Fetching from URL', url);

            // Fetch available time slots
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json().catch(error => {
                        console.error('Error parsing JSON response:', error);
                        throw new Error('Invalid JSON response');
                    });
                })
                .then(data => {
                    console.log('Time slots data:', data);
            if (timeSlotsLoading) timeSlotsLoading.classList.add('d-none');

                    if (data.success && data.timeSlots && data.timeSlots.length > 0) {
                // Hide other states first
                if (timeSlotsEmpty) timeSlotsEmpty.classList.add('d-none');
                if (timeSlotsNone) timeSlotsNone.classList.add('d-none');
                
                        // Show time slots container
                if (timeSlotsListContainer) timeSlotsListContainer.classList.remove('d-none');

                        // Populate time slots
                if (timeSlotsContainer) {
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
                }
                    } else {
                // Hide other states first
                if (timeSlotsEmpty) timeSlotsEmpty.classList.add('d-none');
                if (timeSlotsListContainer) timeSlotsListContainer.classList.add('d-none');
                
                        console.log('No time slots available or success is false');
                        // Show no time slots message
                if (timeSlotsNone) timeSlotsNone.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error loading time slots:', error);
            if (timeSlotsLoading) timeSlotsLoading.classList.add('d-none');
            if (timeSlotsNone) timeSlotsNone.classList.remove('d-none');
                    alert('Error loading time slots: ' + error.message);
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
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    timeSlotCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });

                    updateSelectedTimeSlots();
                });
            }

            // Clear previous selection
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
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
            if (selectedTimeSlotsJson) {
                selectedTimeSlotsJson.value = JSON.stringify(selectedTimeSlots);
            }

        // Enable/disable send button based on selection
        if (sendTimeSlotsBtn) {
            sendTimeSlotsBtn.disabled = selectedTimeSlots.length === 0;
        }

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.time-slot-checkbox');
            if (selectAllCheckbox && allCheckboxes.length > 0) {
                selectAllCheckbox.checked = timeSlotCheckboxes.length > 0 &&
                                        timeSlotCheckboxes.length === allCheckboxes.length;
            }
        }

        // Send time slots button
        if (sendTimeSlotsBtn) {
            sendTimeSlotsBtn.addEventListener('click', function() {
            const messageText = document.getElementById('timeSlotMessageText');
            const selectedTimeSlots = selectedTimeSlotsJson;

            if (!messageText || !messageText.value.trim()) {
                    alert('Please enter a message to accompany the time slots');
                    return;
                }

            if (!selectedTimeSlots || selectedTimeSlots.value === '[]') {
                    alert('Please select at least one time slot');
                    return;
                }

                try {
                    // Show loading state
                    sendTimeSlotsBtn.disabled = true;
                    sendTimeSlotsBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Sending...';

                    // Submit the form
                document.getElementById('timeSlotsForm').submit();
                } catch (error) {
                    console.error('Error submitting form:', error);
                    alert('An error occurred while sending time slots. Please try again.');

                    // Reset button state
                    sendTimeSlotsBtn.disabled = false;
                    sendTimeSlotsBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Time Slots';
                }
            });
        }
    });
</script>

<!-- Select Available Time Slots Modal -->
<div class="modal fade" id="timeSlotsModal" tabindex="-1" role="dialog" aria-labelledby="timeSlotsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="timeSlotsModalLabel">
                    <i class="far fa-clock mr-1"></i> Select Available Time Slots
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Debug: Expected form URL should be /admin/coaching-service-requests/send-time-slots/<?= h($coachingServiceRequest->coaching_service_request_id) ?> -->
                <?= $this->Form->create(null, [
                    'url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'CoachingServiceRequests', 
                        'action' => 'sendTimeSlots', 
                        $coachingServiceRequest->coaching_service_request_id
                    ]),
                    'id' => 'timeSlotsForm',
                    'type' => 'post'
                ]) ?>

                <!-- Explicit CSRF token -->
                <?= $this->Form->hidden('_csrfToken', [
                    'value' => $this->request->getAttribute('csrfToken')
                ]) ?>

                <!-- Include hidden coaching_service_request_id field to ensure it's passed -->
                <?= $this->Form->hidden('coaching_service_request_id', [
                    'value' => $coachingServiceRequest->coaching_service_request_id
                ]) ?>

                <div class="row">
                    <!-- Date Selection Column -->
                    <div class="col-md-5">
                        <h5 class="font-weight-bold mb-3">Select a Date</h5>
                        <div class="card shadow-sm mb-4">
                            <div class="card-body p-2">
                                <!-- Calendar will load here automatically -->
                                <div id="datepicker" class="border p-2 rounded"></div>
                            </div>
                        </div>

                        <!-- Load time slots button -->
                        <button type="button" id="loadTimeSlots" class="btn btn-primary btn-block">
                            <i class="fas fa-clock mr-1"></i> Load Time Slots
                        </button>

                        <!-- Helper text -->
                        <p class="small text-muted mt-2 text-center">
                            Click a date above, then click "Load Time Slots"
                        </p>
                    </div>

                    <!-- Time Slots Selection Column -->
                    <div class="col-md-7">
                        <h5 class="font-weight-bold mb-3">Select Time Slots to Offer</h5>
                        <div class="selected-date-container mb-2">
                            <span class="text-muted">Selected Date: </span>
                            <span id="selected-date-display" class="font-weight-bold">None selected</span>
                        </div>

                        <!-- Empty State -->
                        <div id="timeSlots-empty" class="text-center py-4">
                            <i class="fas fa-calendar-day fa-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Click a date on the calendar and load time slots</p>
                            <p class="text-sm text-gray-400">Available time slots will appear here</p>
                        </div>

                        <!-- Loading State -->
                        <div id="timeSlots-loading" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="text-gray-500">Loading available time slots...</p>
                        </div>

                        <!-- Time Slots List -->
                        <div id="timeSlots-list" class="card shadow-sm mb-3 d-none">
                            <div class="card-header py-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAllTimeSlots">
                                    <label class="custom-control-label font-weight-bold" for="selectAllTimeSlots">
                                        Select All Time Slots
                                    </label>
                                </div>
                            </div>
                            <div class="card-body p-3" style="max-height: 250px; overflow-y: auto;">
                                <div id="time-slots-container">
                                    <!-- Time slots will be populated here -->
                                </div>
                            </div>
                        </div>

                        <!-- No Time Slots State -->
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
                    <?= $this->Form->textarea('message_text', [
                        'class' => 'form-control',
                        'rows' => 3,
                        'id' => 'timeSlotMessageText',
                        'placeholder' => 'Enter a message to accompany the time slots...',
                        'value' => 'I\'d like to schedule a consultation to discuss your coaching service request. Here are some available time slots. Please click the link below to book one of these times or select another time that works for you.'
                    ]) ?>

                    <?= $this->Form->hidden('time_slots', [
                        'id' => 'selectedTimeSlotsJson',
                        'value' => '[]'
                    ]) ?>
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

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markAsPaidModal" tabindex="-1" role="dialog" aria-labelledby="markAsPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="markAsPaidModalLabel">Mark Payment as Paid</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= $this->Form->create(null, [
                'url' => ['action' => 'markAsPaid', $coachingServiceRequest->coaching_service_request_id],
                'id' => 'markAsPaidForm'
            ]) ?>

            <!-- Include _csrfToken field explicitly -->
            <?= $this->Form->hidden('_csrfToken', [
                'value' => $this->request->getAttribute('csrfToken')
            ]) ?>

            <div class="modal-body">
                <p class="text-muted mb-3">Record a manual payment for this coaching service request. The client will be notified of the payment being recorded.</p>

                <div class="form-group mb-3">
                    <label for="amount">Payment Amount ($)</label>
                    <?= $this->Form->control('amount', [
                        'class' => 'form-control',
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '1',
                        'placeholder' => 'Enter amount',
                        'required' => true,
                        'label' => false,
                    ]) ?>
                </div>

                <div class="form-group mb-3">
                    <label for="description">Payment Description (Optional)</label>
                    <?= $this->Form->textarea('description', [
                        'class' => 'form-control',
                        'rows' => 2,
                        'placeholder' => 'e.g. Payment received via bank transfer',
                    ]) ?>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> A confirmation message will be sent to the client automatically.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Mark as Paid</button>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<!-- Payment Request Modal -->
<div class="modal fade" id="paymentRequestModal" tabindex="-1" role="dialog" aria-labelledby="paymentRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="paymentRequestModalLabel"><i class="fas fa-credit-card mr-2"></i> Send Payment Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'sendPaymentRequest', $coachingServiceRequest->coaching_service_request_id],
                    'id' => 'paymentRequestForm',
                ]) ?>

                <!-- Include _csrfToken field explicitly -->
                <?= $this->Form->hidden('_csrfToken', [
                    'value' => $this->request->getAttribute('csrfToken')
                ]) ?>

                <div class="form-group">
                    <label for="amount">Payment Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <?= $this->Form->control('amount', [
                            'type' => 'text',
                            'class' => 'form-control',
                            'id' => 'amount',
                            'placeholder' => 'Enter amount',
                            'required' => true,
                            'value' => $coachingServiceRequest->final_price ?: '',
                            'label' => false
                        ]) ?>
                    </div>
                    <small class="form-text text-muted">Enter the amount to charge the client.</small>
                </div>

                <div class="form-group">
                    <label for="description">Payment Description</label>
                    <?= $this->Form->textarea('description', [
                        'class' => 'form-control',
                        'id' => 'description',
                        'required' => true,
                        'rows' => 3,
                        'placeholder' => 'e.g., Coaching session fee',
                        'value' => 'Coaching Service: ' . $coachingServiceRequest->service_title,
                    ]) ?>
                    <small class="form-text text-muted">Briefly describe what this payment is for.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <?= $this->Form->button(__('Send Payment Request'), [
                    'type'  => 'submit',
                    'class' => 'btn btn-warning'
                ]) ?>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<!-- Payment Templates -->
<div id="payment-request-template" class="d-none">
**Payment Request**

**Service:** Coaching Service: <?= h($coachingServiceRequest->service_title) ?>
**Amount:** $<?= number_format($coachingServiceRequest->final_price ?? 0, 2) ?>

Please click the button below to complete your payment. Once payment is processed, you'll receive a confirmation and we'll begin work on your coaching request.

[PAYMENT_BUTTON]<?= bin2hex(random_bytes(8)) ?>[/PAYMENT_BUTTON]
</div>

<div id="payment-confirmation-template" class="d-none">
[PAYMENT_CONFIRMATION]
**Payment Confirmation**

Your payment of **$<?= number_format($coachingServiceRequest->final_price ?? 0, 2) ?>** for **<?= h($coachingServiceRequest->service_title) ?>** has been received.

Thank you for your payment. We can now proceed with your coaching service as discussed.
[/PAYMENT_CONFIRMATION]
</div>

<?php
/**
 * Get status class for badges
 */
function getStatusClass(string $status): string
{
    return match ($status) {
        'pending' => 'warning',
        'in_progress' => 'primary',
        'completed' => 'success',
        'canceled', 'cancelled' => 'danger',
        default => 'secondary'
    };
}

/**
 * Get document icon based on mime type
 */
function getDocumentIcon(string $mimeType): string
{
    return match (true) {
        str_contains($mimeType, 'pdf') => 'fas fa-file-pdf text-danger',
        str_contains($mimeType, 'word') => 'fas fa-file-word text-primary',
        str_contains($mimeType, 'image') => 'fas fa-file-image text-success',
        default => 'fas fa-file-alt text-secondary'
    };
}

/**
 * Format file size
 */
function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
