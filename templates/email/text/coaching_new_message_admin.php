<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $admin_name
 * @var string $client_name
 * @var string $message
 */
?>
Hello <?= $admin_name ?>,

<?= $client_name ?> has sent a new message regarding coaching service request #<?= $coaching_service_request->coaching_service_request_id ?>.

REQUEST DETAILS
------------------
Service Type: <?= ucwords(str_replace('_', ' ', $coaching_service_request->service_type)) ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Date of Message: <?= date('F j, Y \a\t g:i A') ?>
Client: <?= $client_name ?> (<?= $coaching_service_request->user->email ?>)

MESSAGE CONTENT
------------------
<?= $message ?>

To view and respond to this message, please log in to the admin dashboard:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 