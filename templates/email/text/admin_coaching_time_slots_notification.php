<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $client_name
 * @var string $client_email
 * @var string $admin_name
 * @var string $time_slots
 */
?>
📅 TIME SLOTS SENT - Coaching Service Request #<?= h($coaching_service_request->coaching_service_request_id) ?>

Hello <?= $admin_name ?>,

You have successfully sent time slots to <?= $client_name ?> for their coaching service request.

CLIENT DETAILS
------------------
Client: <?= $client_name ?> (<?= $client_email ?>)
Service: <?= $coaching_service_request->service_title ?>
Request ID: <?= $coaching_service_request->coaching_service_request_id ?>
Sent At: <?= date('F j, Y g:i A') ?>

TIME SLOTS SENT
------------------
<?= $time_slots ?>

NEXT STEPS
------------------
✓ The client has been notified via email
✓ They can accept a time slot from their dashboard
✓ When accepted, an appointment will be created with a Google Meet link
✓ Both you and the client will receive confirmation emails with meeting details

View the request details:
<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

This is an automated notification. Please do not reply directly to this email.

© <?= date('Y') ?> Diana Bonvini Coaching Services. All rights reserved. 