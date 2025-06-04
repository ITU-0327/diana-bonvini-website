<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest
 * @var \App\Model\Entity\CoachingRequestDocument[] $coachingRequestDocuments
 */
use Cake\Utility\Inflector;

$this->assign('title', __('Coaching Service Request Details'));
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

    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <?= $this->Html->link(
                '<i class="fas fa-arrow-left mr-2"></i>' . __('Back to Coaching Requests'),
                ['action' => 'index'],
                [
                    'class' => 'btn btn-outline-primary',
                    'escape' => false
                ]
            ) ?>
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
                                Created: <span class="created-date" data-server-time="<?= $coachingServiceRequest->created_at->jsonSerialize() ?>" data-time-format="datetime">
                                    <?= $coachingServiceRequest->created_at->format('F j, Y h:i A') ?>
                                </span>
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
                                    <?= $this->Html->link(
                                        '<i class="fas fa-download mr-1"></i> Download',
                                        '/' . $coachingServiceRequest->document,
                                        ['escape' => false, 'target' => '_blank', 'class' => 'btn btn-sm btn-primary']
                                    ) ?>
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
                                                    <i class="far fa-clock"></i> 
                                                    <span class="message-timestamp" data-server-time="<?= $message->created_at->jsonSerialize() ?>" data-time-format="datetime">
                                                        <?= $message->created_at->format('M j, Y g:i A') ?>
                                                    </span>
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
                                'canceled' => 'Cancelled',
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
                                        'accept' => '.pdf,.doc,.docx',
                                    ]) ?>
                                    <small class="form-text text-muted">Accepted: PDF and Word documents only (max 10MB)</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-upload mr-1"></i> Upload Document
                                </button>
                                <?= $this->Form->end() ?>
                            </div>
                        </div>

                        <!-- Document List -->
                        <?php if (!empty($coachingRequestDocuments)): ?>
                            <h6 class="font-weight-bold mb-2">Uploaded Documents</h6>
                            <div class="document-list">
                                <?php foreach($coachingRequestDocuments as $document): ?>
                                    <div class="document-item border rounded mb-2 p-3 hover-shadow">
                                        <div class="d-flex align-items-start">
                                            <div class="document-icon mr-3 mt-1">
                                                <i class="<?= getDocumentIcon($document->file_type) ?> fa-2x"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="document-name font-weight-bold text-truncate mb-1" title="<?= h($document->document_name) ?>">
                                                    <?= h($document->document_name) ?>
                                                </div>
                                                <div class="document-meta small text-muted">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="file-type"><?= h(strtoupper(pathinfo($document->document_name, PATHINFO_EXTENSION))) ?></span>
                                                        <span class="file-size"><?= formatFileSize($document->file_size) ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="upload-date">
                                                            <?php if (!empty($document->created_at)): ?>
                                                                <span class="created-date" data-server-time="<?= $document->created_at->jsonSerialize() ?>" data-time-format="date">
                                                                    <?= $document->created_at->format('M j, Y') ?>
                                                                </span>
                                                            <?php else: ?>
                                                                Unknown date
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="uploaded-by badge badge-<?= $document->uploaded_by === 'admin' ? 'primary' : 'info' ?> badge-sm">
                                                            <?= $document->uploaded_by === 'admin' ? 'Admin' : 'Client' ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="document-actions mt-2">
                                                    <?= $this->Html->link(
                                                        '<i class="fas fa-eye mr-1"></i> View Document',
                                                        '/' . $document->document_path,
                                                        ['escape' => false, 'target' => '_blank', 'class' => 'btn btn-sm btn-outline-primary btn-block']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-documents text-center py-3">
                                <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                                <div class="small text-muted">No documents uploaded yet</div>
                                <div class="small text-muted">Upload documents to share with the client</div>
                            </div>
                        <?php endif; ?>
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
                                                    <span class="created-date" data-server-time="<?= $payment->created_at ? $payment->created_at->jsonSerialize() : '' ?>" data-time-format="datetime">
                                                        <?= $payment->created_at ? $payment->created_at->format('M j, Y g:i A') : 'Unknown' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($payment->status === 'paid'): ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle mr-1"></i>Paid
                                                        </span>
                                                        <?php if ($payment->payment_date): ?>
                                                            <small class="d-block text-muted mt-1">
                                                                <span class="payment-date" data-server-time="<?= $payment->payment_date->jsonSerialize() ?>" data-time-format="datetime">
                                                                    <?= $payment->payment_date->format('M j, Y g:i A') ?>
                                                                </span>
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
    min-height: 200px;
    min-width: 250px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Make inline datepicker fill the container nicely */
#datepicker .ui-datepicker {
    width: 100% !important;
    border: none !important;
    box-shadow: none !important;
}

.ui-datepicker {
    font-size: 14px !important;
    background: #ffffff !important;
}

.ui-datepicker table {
    width: 100% !important;
    margin: 0 !important;
}

.ui-datepicker td, .ui-datepicker th {
    padding: 4px !important;
    text-align: center !important;
}

.ui-datepicker td a {
    display: block !important;
    padding: 8px !important;
    text-decoration: none !important;
    border-radius: 4px !important;
    color: #333 !important;
}

.ui-datepicker td a:hover {
    background: #007bff !important;
    color: white !important;
}

.ui-datepicker .ui-datepicker-today a {
    background: #f8f9fa !important;
    font-weight: bold !important;
}

.ui-datepicker .ui-state-active a {
    background: #007bff !important;
    color: white !important;
}

.ui-datepicker .ui-datepicker-header {
    background: #f8f9fa !important;
    border: none !important;
    border-radius: 4px 4px 0 0 !important;
    padding: 10px !important;
}

.ui-datepicker .ui-datepicker-prev,
.ui-datepicker .ui-datepicker-next {
    top: 8px !important;
    cursor: pointer !important;
    background: #007bff !important;
    color: white !important;
    border-radius: 4px !important;
}

.ui-datepicker .ui-datepicker-prev {
    left: 8px !important;
}

.ui-datepicker .ui-datepicker-next {
    right: 8px !important;
}

.ui-datepicker .ui-datepicker-title {
    text-align: center !important;
    font-weight: bold !important;
}

/* If datepicker still doesn't show, make the container more explicit */
#datepicker .ui-widget-content {
    background: #ffffff !important;
    border: 1px solid #ddd !important;
    color: #333333 !important;
}

/* Modal backdrop z-index fix */
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

<?php $this->append('script'); ?>
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
                       class="form-control form-control-lg"
                       min="${minDate}"
                       max="${maxDateStr}"
                       style="font-size: 1.1rem; padding: 12px;">
                <small class="form-text text-muted mt-2">
                    Available dates: ${minDate} to ${maxDateStr}
                </small>
            </div>
        `;

        $container.html(dateInput);

        // Add change event listener
        $('#fallback-date-input').on('change', function() {
            const selectedDate = $(this).val();
            const dateObj = new Date(selectedDate);

            console.log('Fallback date selected:', selectedDate);

            // Update the display
            $('#selected-date-display').text(dateObj.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }));

            $('#loadTimeSlots').html('<i class="fas fa-clock mr-1"></i> Load Time Slots <span class="badge badge-light ml-1">' + selectedDate + '</span>');
            $('#loadTimeSlots').removeClass('btn-primary').addClass('btn-success');

            // Store the selected date for the load time slots function
            window.selectedDateForTimeSlots = selectedDate;
        });
    }

    // Also try to initialize on page load as backup
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

    // Scroll to bottom of chat on page load
    const chatContainer = document.querySelector('.chat-container');
    if (chatContainer) {
        // Scroll immediately and then again after a short delay to ensure images and content are loaded
        chatContainer.scrollTop = chatContainer.scrollHeight;

        // Add a small delay to ensure content is fully rendered before scrolling
        setTimeout(function() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
            console.log('Scrolled chat to bottom with delay');
        }, 500);

        // Add another scroll after 1.5 seconds for any late-loading content
        setTimeout(function() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }, 1500);
    }

    // Focus on message input when clicking reply button
    const messageTextInput = document.getElementById('messageText');
    if (messageTextInput) {
        messageTextInput.focus();
    }

    // Animate button on form submit
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function() {
            const button = document.getElementById('sendButton');
            if (button) {
                button.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Sending...';
                button.disabled = true;
            }
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

    // Time slots selection functionality
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
                console.log(`[TimeSlots] ${message}:`, data);
            } else {
                console.log(`[TimeSlots] ${message}`);
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
                const $datepicker = $('#datepicker');
                selectedDate = $datepicker.datepicker('getDate');
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
        timeSlotsEmpty.classList.add('d-none');
        timeSlotsNone.classList.add('d-none');
        timeSlotsListContainer.classList.add('d-none');
        timeSlotsLoading.classList.remove('d-none');

        // Format the date for display
        const formattedDate = new Date(date);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        selectedDateDisplay.textContent = formattedDate.toLocaleDateString('en-US', options);

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

        // Build the URL for coaching service requests
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
                timeSlotsLoading.classList.add('d-none');

                if (data.success && data.timeSlots && data.timeSlots.length > 0) {
                    // Hide other states first
                    timeSlotsEmpty.classList.add('d-none');
                    timeSlotsNone.classList.add('d-none');

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
                    // Hide other states first
                    timeSlotsEmpty.classList.add('d-none');
                    timeSlotsListContainer.classList.add('d-none');

                    console.log('No time slots available or success is false');
                    // Show no time slots message
                    timeSlotsNone.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error loading time slots:', error);
                timeSlotsLoading.classList.add('d-none');
                timeSlotsNone.classList.remove('d-none');
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
            const messageText = document.getElementById('timeSlotMessageText').value.trim();
            const selectedTimeSlots = selectedTimeSlotsJson ? selectedTimeSlotsJson.value : '[]';

            if (!messageText) {
                alert('Please enter a message to accompany the time slots');
                return;
            }

            if (!selectedTimeSlots || selectedTimeSlots === '[]') {
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
                sendTimeSlotsBtn.disabled = false;
                sendTimeSlotsBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Time Slots';
                alert('Error submitting form: ' + error.message);
            }
        });
    }

    // Custom confirmation modal functionality
    let confirmationCallback = null;

    function showCustomConfirmation(title, mainMessage, additionalInfo, confirmText = 'Confirm', confirmClass = 'btn-primary') {
        return new Promise((resolve) => {
            // Remove any existing theme classes
            const modal = document.getElementById('confirmationModal');
            modal.className = modal.className.replace(/status-\w+|payment-\w+/g, '');

            // Add theme class based on confirm button class
            if (confirmClass.includes('btn-success')) {
                modal.classList.add(title.includes('Payment') ? 'payment-success' : 'status-completed');
            } else if (confirmClass.includes('btn-danger')) {
                modal.classList.add('status-canceled');
            } else if (confirmClass.includes('btn-primary')) {
                modal.classList.add('status-in-progress');
            } else if (confirmClass.includes('btn-warning')) {
                modal.classList.add('status-warning');
            }

            // Set modal content
            document.getElementById('confirmationModalLabel').innerHTML = `<i class="fas fa-question-circle mr-2"></i>${title}`;
            document.getElementById('confirmationMainMessage').innerHTML = mainMessage;
            document.getElementById('confirmationAdditionalInfo').innerHTML = additionalInfo || '';

            // Set confirm button text and style
            const confirmBtn = document.getElementById('confirmationConfirmBtn');
            confirmBtn.innerHTML = `<i class="fas fa-check mr-1"></i> ${confirmText}`;
            confirmBtn.className = `btn ${confirmClass} px-4`;

            // Set up the callback
            confirmationCallback = resolve;

            // Show the modal
            $('#confirmationModal').modal('show');
        });
    }

    // Handle confirmation button click
    document.getElementById('confirmationConfirmBtn').addEventListener('click', function() {
        $('#confirmationModal').modal('hide');
        if (confirmationCallback) {
            confirmationCallback(true);
            confirmationCallback = null;
        }
    });

    // Handle modal dismissal
    $('#confirmationModal').on('hidden.bs.modal', function() {
        if (confirmationCallback) {
            confirmationCallback(false);
            confirmationCallback = null;
        }
    });

    // Status update form enhancement
    const statusForm = document.querySelector('.status-update-form');
    const statusSelect = statusForm ? statusForm.querySelector('select[name="status"]') : null;
    const statusSubmitBtn = statusForm ? statusForm.querySelector('button[type="submit"]') : null;

    if (statusForm && statusSelect && statusSubmitBtn) {
        // Track original status
        const originalStatus = statusSelect.value;

        // Add change event listener to status select
        statusSelect.addEventListener('change', function() {
            const newStatus = this.value;
            const statusText = this.options[this.selectedIndex].text;

            if (newStatus !== originalStatus) {
                statusSubmitBtn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Update to ' + statusText;
                statusSubmitBtn.classList.remove('btn-primary');
                statusSubmitBtn.classList.add('btn-warning');
            } else {
                statusSubmitBtn.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Update Status';
                statusSubmitBtn.classList.remove('btn-warning');
                statusSubmitBtn.classList.add('btn-primary');
            }
        });

        // Add confirmation dialog for status updates
        statusForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const newStatus = statusSelect.value;
            const statusText = statusSelect.options[statusSelect.selectedIndex].text;
            const originalStatusText = statusSelect.querySelector(`option[value="${originalStatus}"]`).text;

            if (newStatus === originalStatus) {
                await showCustomConfirmation(
                    'No Change Required',
                    `Status is already set to "${statusText}"`,
                    null,
                    'OK',
                    'btn-info'
                );
                return;
            }

            // Custom confirmation messages based on status
            let mainMessage = `Are you sure you want to change the status from "<strong>${originalStatusText}</strong>" to "<strong>${statusText}</strong>"?`;
            let additionalInfo = '';
            let confirmClass = 'btn-warning';

            if (newStatus === 'completed') {
                additionalInfo = '<i class="fas fa-info-circle mr-1"></i> This will notify the client that their coaching service is complete.';
                confirmClass = 'btn-success';
            } else if (newStatus === 'canceled' || newStatus === 'cancelled') {
                additionalInfo = '<i class="fas fa-exclamation-triangle mr-1"></i> This will notify the client that their coaching service has been canceled.';
                confirmClass = 'btn-danger';
            } else if (newStatus === 'in_progress') {
                additionalInfo = '<i class="fas fa-play-circle mr-1"></i> This will notify the client that work has started on their coaching service.';
                confirmClass = 'btn-primary';
            }

            const confirmed = await showCustomConfirmation(
                'Confirm Status Change',
                mainMessage,
                additionalInfo,
                'Update Status',
                confirmClass
            );

            if (confirmed) {
                // Disable the button and show loading state
                statusSubmitBtn.disabled = true;
                statusSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Updating...';

                // Submit the form
                this.submit();
            }
        });
    }

    // Enhanced form validation for payment modals
    const paymentRequestForm = document.getElementById('paymentRequestForm');
    if (paymentRequestForm) {
        console.log('Payment request form found:', paymentRequestForm);
        console.log('Form action:', paymentRequestForm.action);
        console.log('Form method:', paymentRequestForm.method);

        paymentRequestForm.addEventListener('submit', function(e) {
            console.log('=== PAYMENT REQUEST FORM SUBMISSION ===');
            console.log('Event:', e);
            console.log('Form:', this);

            const amount = this.querySelector('input[name="amount"]').value;
            const description = this.querySelector('textarea[name="description"]').value;

            console.log('Form data:');
            console.log('- Amount:', amount);
            console.log('- Description:', description);

            // Validate amount
            if (!amount || parseFloat(amount) <= 0) {
                e.preventDefault();
                console.log('VALIDATION FAILED: Invalid amount');
                alert('Please enter a valid payment amount greater than $0.00');
                return false;
            }

            // Validate description
            if (!description.trim()) {
                e.preventDefault();
                console.log('VALIDATION FAILED: Missing description');
                alert('Please enter a payment description.');
                return false;
            }

            // If validation passes, allow form to submit naturally
            console.log('VALIDATION PASSED: Allowing form submission');
            console.log('Form will submit to:', this.action);

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';

            // Form will submit naturally
            console.log('Form submission proceeding...');
            return true;
        });
    } else {
        console.error('Payment request form NOT FOUND - this is the problem!');
        console.log('Available forms on page:', document.querySelectorAll('form'));
    }

    const markAsPaidForm = document.getElementById('markAsPaidForm');
    if (markAsPaidForm) {
        markAsPaidForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const amount = this.querySelector('input[name="amount"]').value;

            if (!amount || parseFloat(amount) <= 0) {
                await showCustomConfirmation(
                    'Invalid Amount',
                    'Please enter a valid payment amount.',
                    '<i class="fas fa-exclamation-circle mr-1"></i> The amount must be greater than $0.00',
                    'OK',
                    'btn-warning'
                );
                return;
            }

            const confirmed = await showCustomConfirmation(
                'Mark Payment as Paid',
                `Mark payment of <strong>$${parseFloat(amount).toFixed(2)}</strong> as paid?`,
                '<i class="fas fa-bell mr-1"></i> The client will be notified automatically.',
                'Mark as Paid',
                'btn-success'
            );

            if (confirmed) {
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processing...';
                this.submit();
            }
        });
    }

    // Enhanced document upload form handling
    const documentUploadForm = document.querySelector('.document-upload-form');
    if (documentUploadForm) {
        const fileInput = documentUploadForm.querySelector('input[type="file"]');
        const submitBtn = documentUploadForm.querySelector('button[type="submit"]');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file type
            const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!allowedTypes.includes(file.type)) {
                showCustomConfirmation(
                    'Invalid File Type',
                    'Please select a PDF or Word document.',
                    '<i class="fas fa-exclamation-triangle mr-1"></i> Only PDF (.pdf) and Word (.doc, .docx) files are allowed.',
                    'OK',
                    'btn-warning'
                );
                this.value = '';
                return;
            }

            // Validate file size (10MB max)
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                showCustomConfirmation(
                    'File Too Large',
                    'The selected file is too large.',
                    '<i class="fas fa-exclamation-triangle mr-1"></i> Maximum file size is 10MB. Please select a smaller file.',
                    'OK',
                    'btn-warning'
                );
                this.value = '';
                return;
            }

            // Update button text with file name
            const fileName = file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name;
            submitBtn.innerHTML = `<i class="fas fa-upload mr-1"></i> Upload ${fileName}`;
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
        });

        documentUploadForm.addEventListener('submit', async function(e) {
            const file = fileInput.files[0];
            if (!file) {
                e.preventDefault();
                await showCustomConfirmation(
                    'No File Selected',
                    'Please select a document to upload.',
                    '<i class="fas fa-info-circle mr-1"></i> Choose a PDF or Word document from your computer.',
                    'OK',
                    'btn-info'
                );
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Uploading...';
        });
    }

    console.log('Coaching service request view JavaScript loaded successfully');
});
</script>
<?php $this->end(); ?>

<!-- Custom Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="confirmationModalLabel">
                    <i class="fas fa-question-circle text-warning mr-2"></i>
                    Confirm Action
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body py-4">
                <div class="confirmation-content">
                    <div class="main-message mb-3" id="confirmationMainMessage">
                        <!-- Main confirmation message will be inserted here -->
                    </div>
                    <div class="additional-info text-muted" id="confirmationAdditionalInfo">
                        <!-- Additional information will be inserted here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary px-4" id="confirmationConfirmBtn">
                    <i class="fas fa-check mr-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

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
                <?php
                $formUrl = $this->Url->build(['action' => 'sendPaymentRequest', $coachingServiceRequest->coaching_service_request_id]);
                echo "<!-- DEBUG: Form URL will be: " . $formUrl . " -->";
                ?>
                <?= $this->Form->create(null, [
                    'url' => ['action' => 'sendPaymentRequest', $coachingServiceRequest->coaching_service_request_id],
                    'id' => 'paymentRequestForm',
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
        str_contains($mimeType, 'word') || str_contains($mimeType, 'msword') => 'fas fa-file-word text-primary',
        str_contains($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') => 'fas fa-file-word text-primary',
        default => 'fas fa-file-alt text-secondary'
    };
}

/**
 * Get document icon class
 */
function getDocumentIconClass(string $mimeType): string
{
    return match (true) {
        str_contains($mimeType, 'pdf') => 'text-red-500',
        str_contains($mimeType, 'word') || str_contains($mimeType, 'doc') => 'text-blue-700',
        default => 'text-gray-500'
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 Coaching admin messaging system initialized');

    // Simple form submission handler (exactly like writing service requests admin template)
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function() {
            const button = document.getElementById('sendButton');
            if (button) {
                button.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Sending...';
                button.disabled = true;
                console.log('📤 Submitting coaching message via form');
            }
        });
    }

    // All timestamps are converted automatically by local-time-converter.js
     
    // Scroll to messages section if hash is present
    if (window.location.hash === '#messages') {
        const messagesElement = document.getElementById('messages');
        if (messagesElement) {
            setTimeout(() => {
                messagesElement.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        }
    }

    console.log('✅ Coaching admin system ready');
});
</script>
