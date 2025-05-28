<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var string $adminName
 * @var string $customerName
 * @var string $messageContent
 * @var string $messageDate
 * @var string $requestId
 */
?>
Hello <?= $customerName ?>,

You have received a new message from Diana Bonvini regarding your writing service request.

REQUEST DETAILS
------------------
Service: <?= $request->service_title ?>
Request ID: <?= $requestId ?>
Date of Message: <?= $messageDate ?>
From: <?= $adminName ?>

MESSAGE CONTENT
------------------
<?= $messageContent ?>

To view and respond to this message, please log in to your account:
<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId, 'prefix' => false], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 