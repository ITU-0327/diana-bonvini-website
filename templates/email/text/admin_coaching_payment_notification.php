<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var \App\Model\Entity\CoachingServicePayment $payment
 * @var string $admin_name
 * @var string $client_name
 * @var string $client_email
 * @var string $amount
 * @var string $transaction_id
 */
?>
Hello <?= $admin_name ?>,

A payment has been received for a coaching service request.

PAYMENT DETAILS
------------------
Client: <?= $client_name ?> (<?= $client_email ?>)
Service Type: <?= ucwords(str_replace('_', ' ', $coaching_service_request->service_type)) ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Amount: $<?= $amount ?>
Date: <?= $payment->payment_date ? $payment->payment_date->format('F j, Y \a\t g:i A') : 'Now' ?>
Transaction ID: <?= $transaction_id ?>
Payment Method: <?= ucfirst($payment->payment_method ?? 'Online') ?>
Status: Paid

<?php if (!empty($coaching_service_request->service_description)): ?>
CLIENT'S REQUIREMENTS
---------------------
<?= $coaching_service_request->service_description ?>

<?php endif; ?>
The request status has been automatically updated to "in progress" if it was previously in "pending" status.

To view the coaching request, visit:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

Please ensure coaching services begin promptly and communicate with your client about next steps.

This is an automated message, please do not reply directly to this email. 