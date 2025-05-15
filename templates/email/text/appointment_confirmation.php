<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $userName
 */
?>
Hello <?= $userName ?>,

Your appointment has been confirmed. Thank you for scheduling a consultation with us!

APPOINTMENT DETAILS
------------------
Date: <?= $appointment->appointment_date->format('l, F j, Y') ?>
Time: <?= $appointment->appointment_time->format('g:i A') ?> (<?= $appointment->duration ?> minutes)
Type: <?= ucfirst(str_replace('_', ' ', $appointment->appointment_type)) ?>

<?php if (!empty($appointment->meeting_link)): ?>
Google Meet Link: <?= $appointment->meeting_link ?>
<?php endif; ?>

<?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
Related Request: <?= $appointment->writing_service_request->service_title ?> (ID: <?= $appointment->writing_service_request->writing_service_request_id ?>)
<?php endif; ?>

<?php if (!empty($appointment->description)): ?>
Notes: <?= $appointment->description ?>
<?php endif; ?>

IMPORTANT INFORMATION
--------------------
- This is a virtual consultation via Google Meet.
- Please join 5 minutes before the scheduled time.
- Ensure you have a stable internet connection and a quiet environment.
- Have any relevant documents or information ready to discuss.
- Cancellations must be made at least 24 hours in advance.

Need to reschedule or have questions? Please contact us.

This is an automated message. Please do not reply directly to this email.

© <?= date('Y') ?> Writing Service. All rights reserved. 