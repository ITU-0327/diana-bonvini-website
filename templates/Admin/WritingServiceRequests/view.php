<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writingServiceRequest
 */
use Cake\Utility\Inflector;

$this->assign('title', __('Writing Service Request Details'));
?>

<!-- Timezone Indicator Removed for Admin View -->

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

    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <?= $this->Html->link(
                '<i class="fas fa-arrow-left mr-2"></i>' . __('Back to Writing Requests'),
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
                        <span class="badge badge-secondary mr-2">ID: <?= h(substr($writingServiceRequest->writing_service_request_id, 0, 12)) ?></span>
                        <span class="badge badge-<?= getStatusClass($writingServiceRequest->request_status) ?> py-2 px-3">
                            <?= ucfirst(str_replace('_', ' ', h($writingServiceRequest->request_status))) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold"><?= h($writingServiceRequest->service_title) ?></h5>
                            <p class="text-muted">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Created: <span class="created-date" data-server-time="<?= $writingServiceRequest->created_at->jsonSerialize() ?>" data-time-format="datetime"><?= $writingServiceRequest->created_at->format('F j, Y h:i A') ?></span>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-tag mr-2"></i>
                                Service Type: <?= h(Inflector::humanize($writingServiceRequest->service_type)) ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-fingerprint mr-2"></i>
                                Request ID: <?= h($writingServiceRequest->writing_service_request_id) ?>
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
                                    <?= $this->Html->link(
                                        '<i class="fas fa-download mr-1"></i> Download',
                                        '/' . $writingServiceRequest->document,
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
                        <span class="badge badge-info px-3 py-2"><?= count($writingServiceRequest->request_messages) ?> Messages</span>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div class="chat-container" style="max-height: 500px; overflow-y: auto; scroll-behavior: smooth; scroll-padding: 10px; overscroll-behavior: contain;" id="chat-messages">
                        <div id="chat-loading" class="chat-loading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <span>Loading messages...</span>
                        </div>
                        <?php if (!empty($writingServiceRequest->request_messages)) : ?>
                            <div class="chat-messages">
                                <?php foreach ($writingServiceRequest->request_messages as $message) : ?>
                                    <?php
                                    $isAdmin = isset($message->user) && $message->user->user_type === 'admin';
                                    ?>
                                    <div class="chat-message <?= $isAdmin ? 'admin-message' : 'client-message' ?>" data-message-id="<?= h($message->request_message_id) ?>">
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
                                                        <?= $message->created_at->format('M d, Y H:i') ?>
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
                            'url' => ['action' => 'sendMessage', $writingServiceRequest->writing_service_request_id],
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
                            <small class="form-text text-muted">
                                <i class="fas fa-keyboard mr-1"></i>
                                Tip: Press Ctrl+Enter to send quickly
                            </small>
                        </div>

                        <div class="form-group mb-0 d-flex justify-content-between align-items-center">
                            <div class="action-buttons">
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
                    <!-- Update Status -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-2">Update Status</h6>
                        <?= $this->Form->create(null, [
                            'url' => ['action' => 'updateStatus', $writingServiceRequest->writing_service_request_id],
                            'id' => 'statusFormTop',
                            'type' => 'post',
                        ]) ?>

                        <div class="form-group mb-3">
                            <?= $this->Form->hidden('writing_service_request_id', ['value' => $writingServiceRequest->writing_service_request_id]) ?>
                            <?= $this->Form->select('status', [
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'canceled' => 'Cancelled',
                            ], [
                                'default' => $writingServiceRequest->request_status,
                                'class' => 'form-control',
                                'empty' => false,
                            ]) ?>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block" id="updateStatusBtnTop">
                                <i class="fas fa-sync-alt mr-1"></i> Update Status
                            </button>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>

                    <!-- Document Upload Section -->
                    <div class="mb-4 pt-3 border-top">
                        <h6 class="font-weight-bold mb-2">Document Management</h6>
                        <div class="card bg-light border mb-3">
                            <div class="card-body p-3">
                                <?= $this->Form->create(null, [
                                    'url' => ['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'uploadDocument', $writingServiceRequest->writing_service_request_id],
                                    'type' => 'file',
                                    'class' => 'document-upload-form',
                                    'onsubmit' => 'this.querySelector("button[type=submit]").disabled = true;',
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
                                    <small class="form-text text-muted">Accepted: PDF and Word documents only</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-upload mr-1"></i> Upload Document
                                </button>
                                <?= $this->Form->end() ?>
                            </div>
                        </div>

                        <!-- Document List -->
                        <?php if (!empty($writingServiceRequest->document) || (isset($requestDocuments) && !empty($requestDocuments))) : ?>
                            <h6 class="font-weight-bold mb-2">Uploaded Documents</h6>
                            <div class="list-group">
                                <?php if (!empty($writingServiceRequest->document)) : ?>
                                    <div class="list-group-item list-group-item-action p-2 d-flex align-items-center">
                                        <div class="document-icon mr-2">
                                            <i class="fas fa-file-alt fa-lg text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="text-truncate font-weight-bold small">
                                                <?= h(basename($writingServiceRequest->document)) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <span><?= h(strtoupper(pathinfo($writingServiceRequest->document, PATHINFO_EXTENSION))) ?></span>
                                            </div>
                                        </div>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-download"></i>',
                                            '/' . $writingServiceRequest->document,
                                            ['escape' => false, 'target' => '_blank', 'class' => 'btn btn-sm btn-outline-primary ml-2']
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                                <?php foreach ($requestDocuments as $document) : ?>
                                    <div class="list-group-item list-group-item-action p-2 d-flex align-items-center">
                                        <div class="document-icon mr-2">
                                            <i class="<?= getDocumentIcon($document->file_type) ?> fa-lg text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="text-truncate font-weight-bold small">
                                                <?= h($document->document_name) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <span><?= h(strtoupper($document->file_extension)) ?></span> •
                                                <span><?= h($document->formatted_size) ?></span> •
                                                <span>
                                                    <?php if (!empty($document->created_at)): ?>
                                                        <span class="created-date" data-server-time="<?= $document->created_at->jsonSerialize() ?>" data-time-format="date">
                                                            <?= $document->created_at->format('M j, Y') ?>
                                                        </span>
                                                    <?php else: ?>
                                                        Unknown date
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-download"></i>',
                                            '/' . $document->document_path,
                                            ['escape' => false, 'target' => '_blank', 'class' => 'btn btn-sm btn-outline-primary ml-2']
                                        ) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Payment Request Button -->
                    <div class="mb-4 pt-3 border-top">
                        <h6 class="font-weight-bold mb-2">Payment Options</h6>
                        <button type="button" class="btn btn-warning btn-block" id="paymentOptionsBtn" data-toggle="modal" data-target="#paymentRequestModal">
                            <i class="fas fa-credit-card mr-1"></i> Send Payment Request
                        </button>
                        <p class="text-sm text-muted mt-1">Send a payment request link to the client</p>
                    </div>

                    <!-- Payment History -->
                    <div class="mb-4 pt-2 border-top">
                        <h6 class="font-weight-bold mb-2 d-flex justify-content-between">
                            <span>Payment History</span>
                            <?php if (!empty($writingServiceRequest->writing_service_payments)) : ?>
                                <span class="badge badge-info"><?= count($writingServiceRequest->writing_service_payments) ?></span>
                            <?php endif; ?>
                        </h6>

                        <?php if (!empty($writingServiceRequest->writing_service_payments)) : ?>
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
                                        <?php foreach ($writingServiceRequest->writing_service_payments as $payment) : ?>
                                            <tr class="<?= $payment->status === 'paid' ? 'table-success' : 'table-warning' ?>">
                                                <td class="small font-weight-bold">
                                                    #<?= h($payment->writing_service_payment_id) ?>
                                                </td>
                                                <td class="font-weight-bold">
                                                    $<?= number_format($payment->amount, 2) ?>
                                                </td>
                                                <td class="text-muted small">
                                                    <span class="created-date" data-server-time="<?= $payment->created_at ? $payment->created_at->jsonSerialize() : '' ?>" data-time-format="datetime">
                                                        <?= $payment->created_at ? $payment->created_at->format('M d, Y H:i') : 'Unknown' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($payment->status === 'paid') : ?>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle mr-1"></i>Paid
                                                        </span>
                                                        <?php if ($payment->payment_date) : ?>
                                                            <small class="d-block text-muted mt-1">
                                                                <span class="payment-date" data-server-time="<?= $payment->payment_date->jsonSerialize() ?>" data-time-format="datetime">
                                                                    <?= $payment->payment_date->format('M d, Y H:i') ?>
                                                                </span>
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php else : ?>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock mr-1"></i>Pending
                                                        </span>
                                                        <small class="d-block text-muted mt-1">Awaiting payment</small>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">Total Requests:</td>
                                            <td class="font-weight-bold"><?= count($writingServiceRequest->writing_service_payments) ?></td>
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
                                        foreach ($writingServiceRequest->writing_service_payments as $payment) {
                                            if ($payment->status === 'paid') {
                                                $paidCount++;
                                            }
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
                                        foreach ($writingServiceRequest->writing_service_payments as $payment) {
                                            if ($payment->status === 'pending') {
                                                $pendingCount++;
                                            }
                                        }
                                            echo $pendingCount;
                                        ?>
                                    </small>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="text-center p-3 bg-light rounded border text-muted">
                                <i class="fas fa-info-circle mr-1"></i> No payment requests yet
                                <small class="d-block mt-1">Use the "Send Payment Request" button above to create payment requests for this service.</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Google Calendar Link -->
                    <div class="mb-4 pt-2 border-top">
                        <h6 class="font-weight-bold mb-2">Calendar Management</h6>
                        <?= $this->Html->link(
                            '<i class="fab fa-google mr-1"></i> View My Calendar',
                            ['controller' => 'GoogleAuth', 'action' => 'viewCalendar'],
                            ['class' => 'btn btn-info btn-block', 'escape' => false],
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
    /* Chat Styles */
    .chat-container {
        padding: 8px;
    }

    .chat-message {
        margin-bottom: 12px;
        position: relative;
    }

    .client-message .message-content {
        margin-right: 15%;
    }

    .admin-message .message-content {
        margin-left: 15%;
    }

    .admin-message .message-bubble {
        background-color: #e3f2fd;
        border-left: 3px solid #4e73df;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .client-message .message-bubble {
        background-color: #e8f5e9;
        border-left: 3px solid #1cc88a;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message-avatar .avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.8rem;
    }

    .message-bubble {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 10px 12px !important;
    }

    .message-text {
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .message-text strong {
        font-weight: 600;
        color: #333;
    }

    .avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.8rem;
    }

    /* Payment Card Styles - More compact and professional */
    .payment-card {
        transition: all 0.2s ease;
        margin-top: 8px;
        margin-bottom: 8px;
    }

    .payment-card:hover {
        box-shadow: 0 .25rem 0.5rem rgba(0,0,0,.1)!important;
    }

    .payment-card.border-success {
        border-width: 1px !important;
    }

    .payment-card-header {
        position: relative;
        padding: 8px 12px !important;
    }

    .payment-card-header .badge {
        font-size: 70%;
    }

    .payment-status-indicator {
        margin-bottom: 0.25rem;
    }

    /* Payment confirmation card - more compact */
    .payment-confirmation-card {
        transition: all 0.2s ease;
        margin-top: 8px;
        margin-bottom: 8px;
    }

    .payment-confirmation-card:hover {
        box-shadow: 0 .25rem 0.5rem rgba(0,0,0,.1)!important;
    }

    .payment-confirmation-header {
        padding: 8px 12px !important;
    }

    .payment-confirmation-body {
        padding: 10px 12px !important;
    }

    .payment-confirmation-content {
        line-height: 1.4;
        font-size: 0.95rem;
    }

    .payment-confirmation-content strong {
        color: #28a745;
        font-weight: 600;
    }

    /* Process payment elements in existing messages */
    function processPaymentElements() {
        // Process payment buttons
        document.querySelectorAll('.message-bubble').forEach(message => {
            // First check for existing payment containers and initialize them
            const existingContainers = message.querySelectorAll('[data-payment-container]');
            existingContainers.forEach(container => {
                const paymentId = container.dataset.paymentContainer;
                if (paymentId) {
                    // Check the payment status from our payment history
                    const isPaid = checkPaymentPaidStatus(paymentId);

                    // Update the UI based on payment status
                    const button = container.querySelector('.payment-button');
                    if (button) {
                        button.classList.remove('btn-warning', 'btn-success');
                        button.classList.add(isPaid ? 'btn-success' : 'btn-warning');
                        button.innerHTML = `<i class="fas fa-${isPaid ? 'check-circle' : 'credit-card'} mr-1"></i> ${isPaid ? 'Payment Complete' : 'Payment Button'}`;
                    }

                    // Update the badge
                    const badge = container.querySelector('.badge');
                    if (badge) {
                        badge.classList.remove('badge-light', 'badge-success');
                        badge.classList.add(isPaid ? 'badge-success' : 'badge-light');
                        badge.textContent = isPaid ? 'PAID' : 'PENDING';
                    }
                }
            });

            // Now process any message text for new payment buttons or confirmations
            const content = message.querySelector('.message-text');
            if (content) {
                const text = content.innerHTML;

                // Process payment buttons
                if (text.includes('[PAYMENT_BUTTON]')) {
                    // Extract payment ID
                    const buttonPattern = /\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/;
                    const match = text.match(buttonPattern);

                    if (match && match[1]) {
                        const paymentId = match[1];

                        // Check payment history for this payment ID
                        const isPaid = checkPaymentPaidStatus(paymentId);

                        // Create payment button HTML with appropriate status
                        const buttonHtml = `
                            <div class="mt-2" data-payment-container="${paymentId}">
                                <span class="text-muted small">Payment request status:</span>
                                <div class="d-flex align-items-center mt-1">
                                    <button class="btn ${isPaid ? 'btn-success' : 'btn-warning'} btn-sm payment-button" disabled>
                                        <i class="fas fa-${isPaid ? 'check-circle' : 'credit-card'} mr-1"></i>
                                        ${isPaid ? 'Payment Complete' : 'Payment Button'}
                                    </button>
                                    <span class="badge badge-${isPaid ? 'success' : 'light'} ml-2">
                                        ${isPaid ? 'PAID' : 'PENDING'}
                                    </span>
                                </div>
                            </div>
                        `;

                        // Replace the tag with the button
                        content.innerHTML = text.replace(buttonPattern, buttonHtml);
                    }
                }

                // Process calendar booking links
                if (text.includes('[CALENDAR_BOOKING_LINK]')) {
                    const bookingLinkPattern = /\[CALENDAR_BOOKING_LINK\]([\s\S]*?)\[\/CALENDAR_BOOKING_LINK\]/;
                    const match = text.match(bookingLinkPattern);

                    if (match && match[1]) {
                        const linkText = match[1].trim();

                        // Create booking link HTML - for admin view, just show the text without button
                        const bookingHtml = `
                            <div class="mt-2 text-muted font-italic">
                                <small><i class="fas fa-info-circle mr-1"></i> ${linkText}</small>
                                <small class="d-block mt-1">Note: Button to view time slots only appears for clients</small>
                            </div>
                        `;

                        // Replace the tag with the text
                        content.innerHTML = text.replace(bookingLinkPattern, bookingHtml);
                    }
                }

                // Process payment confirmations
                if (text.includes('[PAYMENT_CONFIRMATION]')) {
                    const confirmPattern = /\[PAYMENT_CONFIRMATION\](.*?)\[\/PAYMENT_CONFIRMATION\]/;
                    const match = text.match(confirmPattern);

                    if (match) {
                        // Get the content and format it
                        let confirmationContent = match[1];
                        // Format the confirmation message (convert markdown bold to HTML)
                        confirmationContent = confirmationContent.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                        // Create an elegant payment confirmation card
                        const confirmationHtml = `
                            <div class="payment-confirmation-card mt-2 border border-success rounded shadow-sm">
                                <div class="payment-confirmation-header d-flex align-items-center bg-success-light border-bottom border-success">
                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                    <span class="font-weight-bold text-success">Payment Confirmation</span>
                                    <span class="badge badge-pill badge-success ml-auto">PAID</span>
                                </div>
                                <div class="payment-confirmation-body">
                                    <div class="payment-confirmation-content">
                                        ${confirmationContent}
                                    </div>
                                    <div class="d-flex align-items-center mt-2 pt-2 border-top">
                                        <i class="fas fa-info-circle text-primary mr-2"></i>
                                        <span class="text-muted small">This payment has been recorded and the client has been notified</span>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Replace the tag with the confirmation
                        content.innerHTML = text.replace(confirmPattern, confirmationHtml);
                    }
                }
            }
        });
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

        // Handle payment request form submission
        const paymentRequestForm = document.getElementById('paymentRequestForm');
        const sendPaymentRequestBtn = document.getElementById('sendPaymentRequestBtn');

        if (paymentRequestForm && sendPaymentRequestBtn) {
            sendPaymentRequestBtn.addEventListener('click', function() {
                // Validate the form
                const amountInput = document.getElementById('amount');
                const description = document.getElementById('description').value;

                // Get the amount value, strip currency symbols and commas
                let amountValue = amountInput.value.replace(/[$,]/g, '').trim();
                const amount = parseFloat(amountValue);

                // Simplified validation - only check if amount is positive
                if (!amountValue || isNaN(amount) || amount <= 0) {
                    alert('Please enter a valid payment amount greater than 0.');
                    return;
                }

                if (!description.trim()) {
                    alert('Please enter a payment description.');
                    return;
                }

                // Show loading state
                const originalButtonContent = sendPaymentRequestBtn.innerHTML;
                sendPaymentRequestBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Sending...';
                sendPaymentRequestBtn.disabled = true;

                // Submit the form
                paymentRequestForm.submit();
            });

            // Reset modal when it's closed
            $('#paymentRequestModal').on('hidden.bs.modal', function () {
                // Reset form values
                document.getElementById('amount').value = '';
                document.getElementById('description').value = 'Writing Service: <?= addslashes($writingServiceRequest->service_title) ?>';

                // Reset button state
                sendPaymentRequestBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Payment Request';
                sendPaymentRequestBtn.disabled = false;
            });
        }

        // Set up real-time message polling
        // setupMessagePolling(); // Function not defined, commenting out to prevent errors

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
                    selectedDate = datepicker.datepicker('getDate');
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

            // Build the URL
            const url = `<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'getAvailableTimeSlots', 'prefix' => 'Admin']) ?>?date=${date}`;
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
                    alert('An error occurred while sending time slots. Please try again.');

                    // Reset button state
                    sendTimeSlotsBtn.disabled = false;
                    sendTimeSlotsBtn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Time Slots';
                }
            });
        }

        // Handle refresh button click
        const refreshButton = document.getElementById('refresh-chat-button');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                // Add spinning animation to the icon
                const icon = this.querySelector('i');
                icon.classList.add('animate-spin');

                // Show loading indicator
                const loadingIndicator = document.getElementById('chat-loading');
                if (loadingIndicator) {
                    loadingIndicator.classList.add('active');
                }

                // Reload the page after a short delay to show the loading animation
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            });
        }

        // Handle message form submission
        const messageForm = document.getElementById('replyForm');
        if (messageForm) {
            const messageText = document.getElementById('messageText');
            
            // Add Ctrl+Enter shortcut for sending messages
            if (messageText) {
                messageText.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        messageForm.dispatchEvent(new Event('submit'));
                    }
                });
            }

            messageForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const sendButton = document.getElementById('sendButton');
                const messageValue = messageText.value.trim();

                if (!messageValue) {
                    return false;
                }

                // Show loading state
                if (sendButton) {
                    sendButton.disabled = true;
                    sendButton.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Sending...';
                }

                // Get form data
                const formData = new FormData(messageForm);

                // Send via AJAX
                fetch(messageForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.ok || response.status === 302) {
                        // Clear the message input
                        messageText.value = '';
                        
                        // Add message to chat immediately
                        addMessageToChat({
                            content: messageValue,
                            sender: 'admin',
                            senderName: 'You (Admin)',
                            timestamp: new Date().toISOString(),
                            is_read: false
                        });

                        // Scroll to bottom
                        if (chatContainer) {
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }

                        return response.text();
                    }
                    throw new Error('Server error');
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    alert('Failed to send message. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    if (sendButton) {
                        sendButton.disabled = false;
                        sendButton.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send Message';
                    }
                });
            });
        }

        // Function to add a message to the chat immediately
        function addMessageToChat(message) {
            const chatMessages = document.querySelector('.chat-messages');
            if (!chatMessages) return;

            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${message.sender === 'admin' ? 'admin-message' : 'client-message'}`;
            messageDiv.setAttribute('data-message-id', 'pending-' + Date.now());

            // Format the timestamp
            const messageTime = new Date(message.timestamp);
            const timeString = messageTime.toLocaleString();

            // Process message content
            let messageContent = message.content.replace(/\n/g, '<br>');
            messageContent = messageContent.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            messageDiv.innerHTML = `
                <div class="message-header d-flex align-items-center mb-1">
                    <div class="message-avatar mr-2">
                        <div class="avatar bg-primary text-white">A</div>
                    </div>
                    <div class="message-info">
                        <span class="message-sender font-weight-bold">${message.senderName}</span>
                        <span class="message-time text-muted ml-2">
                            <i class="far fa-clock"></i> <span class="local-time">${timeString}</span>
                        </span>
                        <span class="badge badge-info ml-2">Sending...</span>
                    </div>
                </div>
                <div class="message-content">
                    <div class="message-bubble p-3 rounded">
                        <div class="message-text">${messageContent}</div>
                    </div>
                </div>
            `;

            chatMessages.appendChild(messageDiv);

            // Remove the "sending" badge after a short delay
            setTimeout(() => {
                const sendingBadge = messageDiv.querySelector('.badge-info');
                if (sendingBadge) {
                    sendingBadge.remove();
                }
            }, 2000);
        }

        // Check if we need to scroll to bottom due to form submission
        if (sessionStorage.getItem('scrollToBottom') === 'true') {
            // Clear the flag
            sessionStorage.removeItem('scrollToBottom');

            // Scroll to bottom with a more significant delay to ensure all content is loaded
            setTimeout(function() {
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                    console.log('Scrolled to bottom after form submission');
                }
            }, 500);
        }

        // Set up image lightbox
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('lightbox-trigger')) {
                e.preventDefault();

                // Create lightbox
                const lightbox = document.createElement('div');
                lightbox.className = 'lightbox-overlay';
                lightbox.innerHTML = `
                    <div class="lightbox-content">
                        <span class="lightbox-close">&times;</span>
                        <img src="${e.target.href}" alt="${e.target.getAttribute('data-filename')}" class="lightbox-image">
                        <div class="lightbox-caption">${e.target.getAttribute('data-filename')}</div>
                    </div>
                `;

                document.body.appendChild(lightbox);

                // Close lightbox on click
                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
                        document.body.removeChild(lightbox);
                    }
                });
            }
        });
    });
</script>

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
                    'url' => ['action' => 'sendPaymentRequest', $writingServiceRequest->writing_service_request_id],
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
                            'value' => '',
                            'label' => false,
                        ]) ?>
                    </div>
                    <small class="form-text text-muted">Enter the amount to charge the client for this specific service.</small>
                </div>

                <div class="form-group">
                    <label for="description">Payment Description</label>
                    <?= $this->Form->textarea('description', [
                        'class' => 'form-control',
                        'id' => 'description',
                        'required' => true,
                        'rows' => 3,
                        'placeholder' => 'e.g., Editorial review fee, Writing consultation, Proofreading service',
                        'value' => 'Writing Service: ' . $writingServiceRequest->service_title,
                    ]) ?>
                    <small class="form-text text-muted">Briefly describe what this payment is for. This will be visible to the client.</small>
                </div>

                <?= $this->Form->end() ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="sendPaymentRequestBtn">
                    <i class="fas fa-paper-plane mr-1"></i> Send Payment Request
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
                <!-- Debug: Expected form URL should be /admin/writing-service-requests/send-time-slots/<?= h($writingServiceRequest->writing_service_request_id) ?> -->
                <?= $this->Form->create(null, [
                    'url' => $this->Url->build([
                        'prefix' => 'Admin',
                        'controller' => 'WritingServiceRequests',
                        'action' => 'sendTimeSlots',
                        $writingServiceRequest->writing_service_request_id,
                    ]),
                                                                                                        'id' => 'timeSlotsForm',
                                                                                                        'type' => 'post',
                ]) ?>

                <!-- Explicit CSRF token -->
                <?= $this->Form->hidden('_csrfToken', [
                    'value' => $this->request->getAttribute('csrfToken'),
                ]) ?>

                <!-- Include hidden writing_service_request_id field to ensure it's passed -->
                <?= $this->Form->hidden('writing_service_request_id', [
                    'value' => $writingServiceRequest->writing_service_request_id,
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
                        'value' => 'I\'d like to schedule a consultation to discuss your writing service request. Here are some available time slots. Please click the link below to book one of these times or select another time that works for you.',
                    ]) ?>

                    <?= $this->Form->hidden('time_slots', [
                        'id' => 'selectedTimeSlotsJson',
                        'value' => '[]',
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

        // Initialize datepicker when jQuery and jQuery UI are fully loaded
        $(document).ready(function() {
            // Initialize datepicker when the modal is shown
            $('#timeSlotsModal').on('shown.bs.modal', function() {
                console.log('Time slots modal shown, initializing datepicker');
                initDatepicker();
            });

            // Initialize datepicker
            function initDatepicker() {
                try {
                    const $datepicker = $('#datepicker');

                    // Destroy existing datepicker if it exists
                    if ($datepicker.hasClass('hasDatepicker')) {
                        $datepicker.datepicker('destroy');
                    }

                    // Initialize with proper configuration
                    $datepicker.datepicker({
                        minDate: 0, // Today
                        maxDate: '+60d', // Allow up to 60 days in the future
                        dateFormat: 'yy-mm-dd',
                        firstDay: 1, // Start week on Monday
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
                } catch (e) {
                    console.error('Failed to initialize datepicker:', e);
                }
            }
        });

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

        // Initialize datepicker
        try {
            datepicker.datepicker({
                minDate: 0, // Today
                maxDate: '+60d', // Allow up to 60 days in the future
                dateFormat: 'yy-mm-dd',
                firstDay: 1, // Start week on Monday
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
            console.log('Datepicker initialized');
        } catch (e) {
            console.error('Failed to initialize datepicker:', e);
        }

        // Load time slots when the button is clicked
        if (loadTimeSlotsBtn) {
            loadTimeSlotsBtn.addEventListener('click', function() {
                let selectedDate = null;
                let formattedDate = null;

                // Try to get date from jQuery UI datepicker first
                try {
                    selectedDate = datepicker.datepicker('getDate');
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

            // Build the URL
            const url = `<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'getAvailableTimeSlots', 'prefix' => 'Admin']) ?>?date=${date}`;
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
                            // Convert UTC slot to local time
                            const utcString = `${slot.date}T${slot.start}`;
                            const localTime = window.localTimeConverter
                                ? window.localTimeConverter.convertToLocalTime(utcString, 'time')
                                : slot.formatted;

                            slotDiv.innerHTML = `
                                <input type="checkbox" class="custom-control-input time-slot-checkbox" id="${id}" data-slot='${JSON.stringify(slot)}'>
                                <label class="custom-control-label" for="${id}">
                                    ${localTime}
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

                const form = document.getElementById('timeSlotsForm');
                if (!form) {
                    console.error('Form not found');
                    alert('Error: Form not found');
                    return;
                }

                try {
                    // Show loading state
                    sendTimeSlotsBtn.disabled = true;
                    sendTimeSlotsBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Sending...';

                    // Log form data before submission (for debugging only)
                    console.log('Submitting form with data:', {
                        message_text: messageText.value,
                        time_slots: selectedTimeSlots.value,
                        request_id: '<?= $writingServiceRequest->writing_service_request_id ?>'
                    });

                    // Log form action URL for debugging
                    console.log('Form action URL:', form.action);
                    console.log('Form method:', form.method);
                    console.log('Expected URL should be: /admin/writing-service-requests/send-time-slots/<?= h($writingServiceRequest->writing_service_request_id) ?>');

                    // Submit the form
                    // Submit the form
                    form.submit();
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

<!-- Add this at the end, before the closing body tag -->
<div id="payment-request-template" class="d-none">
**Payment Request**

**Service:** Writing Service: <?= h($writingServiceRequest->service_title) ?>
**Amount:** $<?= number_format($writingServiceRequest->final_price ?? 0, 2) ?>

Please click the button below to complete your payment. Once payment is processed, you'll receive a confirmation and we'll begin work on your request.

[PAYMENT_BUTTON]<?= bin2hex(random_bytes(8)) ?>[/PAYMENT_BUTTON]
</div>

<div id="payment-confirmation-template" class="d-none">
[PAYMENT_CONFIRMATION]
**Payment Confirmation**

Your payment of **$<?= number_format($writingServiceRequest->final_price ?? 0, 2) ?>** has been successfully processed.

Thank you for your payment. We'll now begin work on your writing service request.
[/PAYMENT_CONFIRMATION]
</div>

<?php $this->append('script'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll chat to bottom on load
        const chatContainer = document.querySelector('.chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Process payment elements in existing messages
        processPaymentElements();

        // Send payment request button
        const paymentRequestBtn = document.getElementById('paymentRequestBtn');
        if (paymentRequestBtn) {
            paymentRequestBtn.addEventListener('click', function() {
                const template = document.getElementById('payment-request-template').innerHTML;
                const messageText = document.getElementById('messageText');
                messageText.value = template;

                // Focus on the text area
                messageText.focus();
            });
        }

        // Mark as paid button
        const markAsPaidBtn = document.getElementById('markAsPaidBtn');
        if (markAsPaidBtn) {
            markAsPaidBtn.addEventListener('click', function() {
                const template = document.getElementById('payment-confirmation-template').innerHTML;
                const messageText = document.getElementById('messageText');
                messageText.value = template;

                // Focus on the text area
                messageText.focus();

                // Update payment status to paid
                fetch('<?= $this->Url->build(['action' => 'markAsPaid', $writingServiceRequest->writing_service_request_id]) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Error updating payment status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }

        // Process payment elements in existing messages
        function processPaymentElements() {
            // Process payment buttons
            document.querySelectorAll('.message-bubble').forEach(message => {
                // First check for existing payment containers and initialize them
                const existingContainers = message.querySelectorAll('[data-payment-container]');
                existingContainers.forEach(container => {
                    const paymentId = container.dataset.paymentContainer;
                    if (paymentId) {
                        // Check the payment status from our payment history
                        const isPaid = checkPaymentPaidStatus(paymentId);

                        // Update the UI based on payment status
                        const button = container.querySelector('.payment-button');
                        if (button) {
                            button.classList.remove('btn-warning', 'btn-success');
                            button.classList.add(isPaid ? 'btn-success' : 'btn-warning');
                            button.innerHTML = `<i class="fas fa-${isPaid ? 'check-circle' : 'credit-card'} mr-1"></i> ${isPaid ? 'Payment Complete' : 'Payment Button'}`;
                        }

                        // Update the badge
                        const badge = container.querySelector('.badge');
                        if (badge) {
                            badge.classList.remove('badge-light', 'badge-success');
                            badge.classList.add(isPaid ? 'badge-success' : 'badge-light');
                            badge.textContent = isPaid ? 'PAID' : 'PENDING';
                        }
                    }
                });

                // Now process any message text for new payment buttons or confirmations
                const content = message.querySelector('.message-text');
                if (content) {
                    const text = content.innerHTML;

                    // Process payment buttons
                    if (text.includes('[PAYMENT_BUTTON]')) {
                        // Extract payment ID
                        const buttonPattern = /\[PAYMENT_BUTTON\](.*?)\[\/PAYMENT_BUTTON\]/;
                        const match = text.match(buttonPattern);

                        if (match && match[1]) {
                            const paymentId = match[1];

                            // Check payment history for this payment ID
                            const isPaid = checkPaymentPaidStatus(paymentId);

                            // Create payment button HTML with appropriate status
                            const buttonHtml = `
                            <div class="mt-2" data-payment-container="${paymentId}">
                                    <span class="text-muted small">Payment request status:</span>
                                    <div class="d-flex align-items-center mt-1">
                                        <button class="btn ${isPaid ? 'btn-success' : 'btn-warning'} btn-sm payment-button" disabled>
                                            <i class="fas fa-${isPaid ? 'check-circle' : 'credit-card'} mr-1"></i>
                                            ${isPaid ? 'Payment Complete' : 'Payment Button'}
                                        </button>
                                        <span class="badge badge-${isPaid ? 'success' : 'light'} ml-2">
                                            ${isPaid ? 'PAID' : 'PENDING'}
                                        </span>
                                    </div>
                                </div>
                            `;

                            // Replace the tag with the button
                            content.innerHTML = text.replace(buttonPattern, buttonHtml);
                        }
                    }

                    // Process calendar booking links
                    if (text.includes('[CALENDAR_BOOKING_LINK]')) {
                        const bookingLinkPattern = /\[CALENDAR_BOOKING_LINK\]([\s\S]*?)\[\/CALENDAR_BOOKING_LINK\]/;
                        const match = text.match(bookingLinkPattern);

                        if (match && match[1]) {
                            const linkText = match[1].trim();

                            // Create booking link HTML - for admin view, just show the text without button
                            const bookingHtml = `
                            <div class="mt-2 text-muted font-italic">
                                    <small><i class="fas fa-info-circle mr-1"></i> ${linkText}</small>
                                    <small class="d-block mt-1">Note: Button to view time slots only appears for clients</small>
                                </div>
                            `;

                            // Replace the tag with the text
                            content.innerHTML = text.replace(bookingLinkPattern, bookingHtml);
                        }
                    }

                    // Process payment confirmations
                    if (text.includes('[PAYMENT_CONFIRMATION]')) {
                        const confirmPattern = /\[PAYMENT_CONFIRMATION\](.*?)\[\/PAYMENT_CONFIRMATION\]/;
                        const match = text.match(confirmPattern);

                        if (match) {
                            // Get the content and format it
                            let confirmationContent = match[1];
                            // Format the confirmation message (convert markdown bold to HTML)
                            confirmationContent = confirmationContent.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                            // Create an elegant payment confirmation card
                            const confirmationHtml = `
                            <div class="payment-confirmation-card mt-2 border border-success rounded shadow-sm">
                                    <div class="payment-confirmation-header d-flex align-items-center bg-success-light border-bottom border-success">
                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                        <span class="font-weight-bold text-success">Payment Confirmation</span>
                                        <span class="badge badge-pill badge-success ml-auto">PAID</span>
                                    </div>
                                <div class="payment-confirmation-body">
                                        <div class="payment-confirmation-content">
                                            ${confirmationContent}
                                        </div>
                                    <div class="d-flex align-items-center mt-2 pt-2 border-top">
                                            <i class="fas fa-info-circle text-primary mr-2"></i>
                                            <span class="text-muted small">This payment has been recorded and the client has been notified</span>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Replace the tag with the confirmation
                            content.innerHTML = text.replace(confirmPattern, confirmationHtml);
                        }
                    }
                }
            });
        }

        // Function to check if a payment ID corresponds to a paid payment in payment history
        function checkPaymentPaidStatus(paymentId) {
            // Extract the database payment ID from the combined ID
            const parts = paymentId.split('|');
            const dbPaymentId = parts[1] || null;

            // If we don't have a database ID, we can't match it
            if (!dbPaymentId || dbPaymentId === 'pending') return false;

            // Get all payment history rows from the table
            const paymentRows = document.querySelectorAll('#payment-history-table tbody tr');
            let isPaid = false;

            paymentRows.forEach(row => {
                // Get the payment ID from the first column (may be wrapped in an element)
                const idCell = row.querySelector('td:nth-child(1)');
                const statusCell = row.querySelector('td:nth-child(4)'); // Status is in column 4

                if (!idCell || !statusCell) return;

                const rowId = idCell.textContent.trim();
                const status = statusCell.textContent.trim();

                // Check if the ID in this row matches our payment ID and the status is 'Paid'
                if (rowId.includes(dbPaymentId) && status.toLowerCase() === 'paid') {
                    isPaid = true;
                }
            });

            return isPaid;
        }

        // Status update form handling
        const statusForm = document.getElementById('statusFormTop');
        if (statusForm) {
            statusForm.addEventListener('submit', function(e) {
                const button = document.getElementById('updateStatusBtnTop');
                button.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Updating...';
                button.disabled = true;
            });
        }
    });
</script>
<?php $this->end(); ?>

<?php $this->append('script'); ?>
<script>
    // Debugging script to check jQuery UI availability
    $(document).ready(function() {
        console.log('jQuery version:', $.fn.jquery);
        console.log('jQuery UI available:', typeof $.ui !== 'undefined');
        console.log('Datepicker available:', typeof $.fn.datepicker !== 'undefined');

        if (typeof $.ui === 'undefined') {
            console.error('jQuery UI is not loaded!');
        }

        if (typeof $.fn.datepicker === 'undefined') {
            console.error('jQuery UI Datepicker is not loaded!');
        }

        // Test modal functionality
        $('#timeSlotsModal').on('show.bs.modal', function() {
            console.log('Modal is opening...');
        });

        $('#timeSlotsModal').on('shown.bs.modal', function() {
            console.log('Modal is now visible');
            // Force datepicker initialization
            setTimeout(function() {
                const $datepicker = $('#datepicker');
                console.log('Datepicker element found:', $datepicker.length > 0);

                if ($datepicker.length > 0 && typeof $.fn.datepicker !== 'undefined') {
                    // Initialize datepicker
                    if (!$datepicker.hasClass('hasDatepicker')) {
                        console.log('Initializing datepicker...');
                        $datepicker.datepicker({
                            minDate: 0,
                            maxDate: '+60d',
                            dateFormat: 'yy-mm-dd',
                            firstDay: 1,
                            showOtherMonths: true,
                            selectOtherMonths: true,
                            beforeShowDay: $.datepicker.noWeekends,
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
                        console.log('Datepicker initialized successfully');
                    } else {
                        console.log('Datepicker already initialized');
                    }
                } else {
                    console.error('Cannot initialize datepicker - element not found or jQuery UI not available');
                }
            }, 100);
        });
    });
</script>
<?php $this->end(); ?>

<?php
// Helper function for determining badge colors
function getStatusClass(string $status): string
{
    return match ($status) {
        'pending' => 'warning',
        'in_progress' => 'info',
        'completed' => 'success',
        'canceled' => 'danger',
        default => 'secondary',
    };
}

function getDocumentIcon(string $mimeType): string
{
    return match ($mimeType) {
        'application/pdf' => 'fas fa-file-pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fas fa-file-word',
        'application/msword' => 'fas fa-file-word',
        'text/plain' => 'fas fa-file-alt',
        'image/jpeg', 'image/png', 'image/gif' => 'fas fa-file-image',
        default => 'fas fa-file',
    };
}

function formatFileSize(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    } elseif ($bytes < 1048576) {
        return round($bytes / 1024, 1) . ' KB';
    } else {
        return round($bytes / 1048576, 1) . ' MB';
    }
}
?>

<?php $this->end(); ?>


