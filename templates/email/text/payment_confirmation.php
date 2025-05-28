<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var \App\Model\Entity\WritingServicePayment $payment
 * @var array $paymentDetails
 * @var string $userName
 * @var string $transactionId
 */
?>
Hello <?= $userName ?>,

Thank you for your payment. Your payment for writing services has been successfully processed.

PAYMENT DETAILS
------------------
Service: <?= $request->service_title ?>
Request ID: <?= $request->writing_service_request_id ?>
Amount: $<?= number_format($payment->amount, 2) ?>
Date: <?= $payment->payment_date->format('F j, Y \a\t g:i A') ?>
Transaction ID: <?= $transactionId ?>
Status: Paid

We have now started working on your writing service request. You can check the status of your request at any time by logging into your account.

To view your request status, visit:
<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $request->writing_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>

If you have any questions, please don't hesitate to contact our support team.

Thank you for choosing our writing service.

This is an automated message, please do not reply directly to this email. 