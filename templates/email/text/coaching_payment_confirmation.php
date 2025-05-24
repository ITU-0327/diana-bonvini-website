<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var \App\Model\Entity\CoachingServicePayment $payment
 * @var string $client_name
 * @var string $amount
 * @var string $transaction_id
 */
?>
Hello <?= $client_name ?>,

Thank you for your payment. Your payment for coaching services has been successfully processed.

PAYMENT DETAILS
------------------
Service Type: <?= ucwords(str_replace('_', ' ', $coaching_service_request->service_type)) ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Amount: $<?= $amount ?>
Date: <?= $payment->payment_date ? $payment->payment_date->format('F j, Y \a\t g:i A') : 'Now' ?>
Transaction ID: <?= $transaction_id ?>
Status: Paid

We have now started working on your coaching service request. Diana will be in touch with you shortly to begin your coaching journey. You can check the status of your request and any new messages at any time by logging into your account.

To view your request status, visit:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>

If you have any questions, please don't hesitate to contact our support team.

Thank you for choosing our coaching service.

This is an automated message, please do not reply directly to this email. 