<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $client_name
 * @var string $admin_name
 * @var string $time_slots
 */
?>
Hello <?= $client_name ?>,

<?= $admin_name ?> has sent you available time slots for your coaching service request.

REQUEST DETAILS
------------------
Service Type: <?= ucwords(str_replace('_', ' ', $coaching_service_request->service_type)) ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Date: <?= date('F j, Y \a\t g:i A') ?>

AVAILABLE TIME SLOTS
------------------
<?= $time_slots ?>

Please log in to your account to book one of these time slots or propose another time that works better for you:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email. 