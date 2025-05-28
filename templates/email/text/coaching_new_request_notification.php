<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $admin_name
 * @var string $client_name
 * @var string $client_email
 */
?>
Hello <?= $admin_name ?>,

A new coaching service request has been submitted.

REQUEST DETAILS
------------------
Service Type: <?= ucwords(str_replace('_', ' ', $coaching_service_request->service_type)) ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Date: <?= $coaching_service_request->created_at->format('F j, Y \a\t g:i A') ?>
Client: <?= $client_name ?> (<?= $client_email ?>)

<?php if (!empty($coaching_service_request->service_description)): ?>
Description:
<?= $coaching_service_request->service_description ?>
<?php endif; ?>

To view and respond to this request, please log in to the admin dashboard:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 