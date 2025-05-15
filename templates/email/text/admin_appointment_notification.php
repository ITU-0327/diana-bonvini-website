<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 */
?>
Hello <?= $adminName ?>,

A customer has accepted and confirmed an appointment time slot. The appointment has been added to your Google Calendar.

APPOINTMENT DETAILS
------------------
Customer: <?= $customerName ?> (<?= $customerEmail ?>)
Type: <?= ucfirst(str_replace('_', ' ', $appointment->appointment_type)) ?>
Date: <?= $appointment->appointment_date->format('l, F j, Y') ?>
Time: <?= $appointment->appointment_time->format('g:i A') ?> (30 minutes)
Status: Confirmed

<?php if (!empty($appointment->description)): ?>
Notes: <?= $appointment->description ?>
<?php endif; ?>

<?php if (!empty($appointment->meeting_link)): ?>
Meeting Link: <?= $appointment->meeting_link ?>
<?php endif; ?>

<?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
Related Request: <?= $appointment->writing_service_request->service_title ?> (ID: <?= $appointment->writing_service_request->writing_service_request_id ?>)
<?php endif; ?>

Note: This appointment has been automatically added to your Google Calendar.
<?php if (!empty($appointment->meeting_link)): ?>
A Google Meet link has been created and shared with the customer.
<?php endif; ?>

This is an automated message, please do not reply directly to this email. 