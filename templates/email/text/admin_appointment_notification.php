<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 */
?>
=============================================================
📅 NEW APPOINTMENT BOOKED - ADMIN NOTIFICATION
=============================================================

Hello <?= $adminName ?>,

🎉 <?= $customerName ?> has accepted and confirmed their appointment time slot. The appointment has been automatically added to your Google Calendar.

👤 CUSTOMER INFORMATION
=============================================================
Customer: <?= $customerName ?>
Email: <?= $customerEmail ?>
Status: ✓ CONFIRMED

📅 APPOINTMENT DETAILS
=============================================================
Date: <?= $appointment->appointment_date->format('l, F j, Y') ?>
Time: <?= $appointment->appointment_time->format('g:i A') ?> (30 minutes)
Service: <?= ucfirst(str_replace('_', ' ', $appointment->appointment_type)) ?>

<?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
Project: <?= $appointment->writing_service_request->service_title ?>
Request ID: <?= $appointment->writing_service_request->writing_service_request_id ?>

<?php endif; ?>
<?php if (!empty($appointment->description)): ?>
📝 NOTES
=============================================================
<?= $appointment->description ?>

<?php endif; ?>
<?php if (!empty($appointment->meeting_link)): ?>
🎥 MEETING INFORMATION
=============================================================
Google Meet Link: <?= $appointment->meeting_link ?>
🔗 Meeting link has been shared with customer

<?php endif; ?>
⚡ AUTOMATIC UPDATES COMPLETED
=============================================================
✓ Google Calendar: Appointment added to your calendar
<?php if (!empty($appointment->meeting_link)): ?>
✓ Google Meet: Meeting link created and shared with customer
<?php endif; ?>
✓ Email Notification: Customer has received confirmation email
✓ Database: Appointment record created and status updated

📋 ADMIN REMINDER
=============================================================
Remember to prepare any materials needed for the consultation. The customer is expecting a professional writing consultation experience.

📧 QUICK ACTIONS
=============================================================
Contact Customer: mailto:<?= $customerEmail ?>
<?php if (!empty($appointment->meeting_link)): ?>
Join Meeting: <?= $appointment->meeting_link ?>
<?php endif; ?>

=============================================================

This is an automated admin notification. Do not reply directly to this email.

© <?= date('Y') ?> Diana Bonvini Writing Services - Admin Dashboard
Professional Writing Services - Admin Portal 