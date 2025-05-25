<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $userName
 */
?>
=============================================================
🎉 APPOINTMENT CONFIRMED! 
=============================================================

Hello <?= $userName ?>,

Great news! Your writing consultation appointment has been successfully confirmed. We're excited to help you with your writing project.

📅 APPOINTMENT DETAILS
=============================================================
Date: <?= $appointment->appointment_date->format('l, F j, Y') ?>
Time: <?= $appointment->appointment_time->format('g:i A') ?> (<?= $appointment->duration ?> minutes)
Service: <?= ucfirst(str_replace('_', ' ', $appointment->appointment_type)) ?>

<?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
Project: <?= $appointment->writing_service_request->service_title ?>
Request ID: <?= $appointment->writing_service_request->writing_service_request_id ?>

<?php endif; ?>
<?php if (!empty($appointment->meeting_link)): ?>
🎥 VIRTUAL MEETING
=============================================================
Google Meet Link: <?= $appointment->meeting_link ?>

💡 TIP: Join 5 minutes early to test your connection

<?php endif; ?>
<?php if (!empty($appointment->description)): ?>
📝 NOTES
=============================================================
<?= $appointment->description ?>

<?php endif; ?>
📋 PRE-MEETING CHECKLIST
=============================================================
✓ Test your internet connection and camera/microphone
✓ Find a quiet, well-lit space with minimal distractions  
✓ Prepare your questions and any materials you'd like to discuss
✓ Have pen and paper ready for taking notes
✓ Join the meeting 5 minutes early

⚠️  IMPORTANT INFORMATION
=============================================================
• This is a virtual consultation via Google Meet
• Please ensure you have a stable internet connection
• Have any relevant documents or information ready to discuss  
• Cancellations must be made at least 24 hours in advance

📞 NEED TO RESCHEDULE?
=============================================================
If you need to reschedule or have any questions, please contact us at least 24 hours in advance. We're here to help!

🔗 MANAGE YOUR APPOINTMENTS
=============================================================
View all your appointments: <?= $this->Url->build(['controller' => 'Calendar', 'action' => 'myAppointments', 'prefix' => false], ['fullBase' => true]) ?>

=============================================================

This is an automated confirmation. Please do not reply directly to this email.

© <?= date('Y') ?> Diana Bonvini Writing Services. All rights reserved.
Professional Writing Services 