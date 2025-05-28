<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var \App\Model\Entity\WritingServicePayment $payment
 * @var array $paymentDetails
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 * @var string $transactionId
 */
?>
Hello <?= $adminName ?>,

A payment has been received for a writing service request.

PAYMENT DETAILS
------------------
Customer: <?= $customerName ?> (<?= $customerEmail ?>)
Service: <?= $request->service_title ?>
Request ID: <?= $request->writing_service_request_id ?>
Amount: $<?= number_format($payment->amount, 2) ?>
Date: <?= $payment->payment_date->format('F j, Y \a\t g:i A') ?>
Transaction ID: <?= $transactionId ?>
Payment Method: <?= ucfirst($payment->payment_method) ?>
Status: Paid

The request status has been automatically updated to "in progress" if it was previously in "pending" status.

To view the request, visit:
<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $request->writing_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

Please ensure work begins on this request promptly.

This is an automated message, please do not reply directly to this email. 