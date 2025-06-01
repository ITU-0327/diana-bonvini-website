<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 * @var string $meetingLink
 */
?>
🔔 NEW COACHING APPOINTMENT BOOKED

Hello <?= $adminName ?>,

A new coaching consultation appointment has been booked and confirmed.

CLIENT DETAILS
------------------
Name: <?= $customerName ?>
Email: <?= $customerEmail ?>
<?php if (!empty($appointment->user->phone)): ?>
Phone: <?= $appointment->user->phone ?>
<?php endif; ?>

APPOINTMENT DETAILS
------------------
Date: <?= $appointment->appointment_date->format('l, F j, Y') ?>
Time: <?= $appointment->appointment_time->format('g:i A') ?>
Duration: <?= $appointment->duration ?? 30 ?> minutes
Location: Online (Google Meet)
Status: <?= ucfirst($appointment->status) ?>

<?php if (!empty($appointment->coaching_service_request)): ?>
COACHING REQUEST DETAILS
------------------
Service Type: <?= ucwords(str_replace('_', ' ', $appointment->coaching_service_request->service_type)) ?>
Service Title: <?= $appointment->coaching_service_request->service_title ?>
Request ID: <?= $appointment->coaching_service_request->coaching_service_request_id ?>
Request Status: <?= ucfirst(str_replace('_', ' ', $appointment->coaching_service_request->request_status)) ?>
<?php endif; ?>

GOOGLE MEET LINK
------------------
<?= $meetingLink ?>

<?php if (!empty($appointment->description)): ?>
APPOINTMENT DESCRIPTION
------------------
<?= $appointment->description ?>
<?php endif; ?>

GOOGLE CALENDAR SYNC
------------------
<?php if ($appointment->is_google_synced): ?>
✅ Synced with Google Calendar (Event ID: <?= $appointment->google_calendar_event_id ?>)
<?php else: ?>
⚠️ Not synced with Google Calendar (manual entry may be needed)
<?php endif; ?>

ACTION REQUIRED
------------------
1. Review the client's coaching request details
2. Prepare coaching materials relevant to their goals
3. Test the Google Meet link before the session
4. Add this appointment to your personal calendar if not auto-synced

ADMIN DASHBOARD
------------------
View appointment details: <?= $this->Url->build(['controller' => 'Appointments', 'action' => 'view', $appointment->appointment_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

View coaching request: <?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $appointment->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>

The client has received a confirmation email with the meeting details.

Best regards,
Appointment System

© <?= date('Y') ?> Diana Bonvini Coaching Services. All rights reserved.

This is an automated notification. Please do not reply directly to this email. 