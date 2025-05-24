<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $admin_name
 * @var string $client_name
 * @var string $message
 */
?>
Hello <?= $client_name ?>,

You have received a new message from <?= $admin_name ?> regarding your coaching service request.

REQUEST DETAILS
------------------
Service Type: <?= ucwords(str_replace('_', ' ', $coaching_service_request->service_type)) ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Date of Message: <?= date('F j, Y \a\t g:i A') ?>
From: <?= $admin_name ?>

MESSAGE CONTENT
------------------
<?= $message ?>

To view and respond to this message, please log in to your account:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 