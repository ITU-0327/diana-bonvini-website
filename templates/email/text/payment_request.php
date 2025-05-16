<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var \App\Model\Entity\WritingServicePayment $payment
 * @var float $amount
 * @var string $userName
 * @var string $paymentId
 */
?>
Hello <?= $userName ?>,

A payment request has been created for your writing service request.

PAYMENT DETAILS
------------------
Service: <?= $request->service_title ?>
Request ID: <?= $request->writing_service_request_id ?>
Amount: $<?= number_format($amount, 2) ?>

Please log in to your account to complete this payment. Once payment is processed, your writing service request will move to the next stage of completion.

To pay now, visit:
<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $request->writing_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>

If you have any questions about this payment, please contact our support team.

Thank you for choosing our writing service.

This is an automated message, please do not reply directly to this email. 