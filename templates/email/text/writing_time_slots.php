<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $writing_service_request
 * @var string $client_name
 * @var string $admin_name
 * @var string $time_slots
 */
?>
Hello <?= $client_name ?>,

<?= $admin_name ?> has sent you available time slots for your writing service request.

REQUEST DETAILS
------------------
Service: <?= $writing_service_request->service_title ?>
Request ID: <?= $writing_service_request->writing_service_request_id ?>
Date: <?= date('F j, Y \a\t g:i A') ?>

AVAILABLE TIME SLOTS
------------------
<?= $time_slots ?>

Please log in to your account to book one of these time slots or propose another time that works better for you:
<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $writing_service_request->writing_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>

This is an automated message, please do not reply directly to this email.

© <?= date('Y') ?> Diana Bonvini Writing Services. All rights reserved. 