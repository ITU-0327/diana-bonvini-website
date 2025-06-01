<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $userName
 * @var string $meetingLink
 */
?>
✅ COACHING APPOINTMENT CONFIRMED

Hello <?= $userName ?>,

Great news! Your coaching consultation appointment has been confirmed.

APPOINTMENT DETAILS
------------------
Date: <?= $appointment->appointment_date->format('l, F j, Y') ?>
Time: <?= $appointment->appointment_time->format('g:i A') ?>
Duration: <?= $appointment->duration ?? 30 ?> minutes
Location: Online (Google Meet)

<?php if (!empty($appointment->coaching_service_request)): ?>
Related Request: <?= $appointment->coaching_service_request->service_title ?>
Request ID: <?= $appointment->coaching_service_request->coaching_service_request_id ?>
<?php endif; ?>

GOOGLE MEET LINK
------------------
<?= $meetingLink ?>

WHAT TO EXPECT
------------------
✓ This is a personalized coaching session designed to help you achieve your goals
✓ Please ensure you have a stable internet connection
✓ Join the meeting a few minutes early to test your audio/video
✓ Have any relevant materials or questions ready
✓ The session will be recorded for your future reference (with your permission)

BEFORE THE SESSION
------------------
1. Test your Google Meet setup: <?= $meetingLink ?>
2. Prepare any questions or topics you'd like to discuss
3. Find a quiet, private space for our conversation
4. Have a notebook or device ready for taking notes

NEED TO RESCHEDULE?
------------------
If you need to reschedule, please contact us at least 24 hours in advance.
Email: diana@dianabonvini.com
Phone: [Your phone number]

We're excited to work with you and help you achieve your coaching goals!

Best regards,
Diana Bonvini
Certified Life & Business Coach

© <?= date('Y') ?> Diana Bonvini Coaching Services. All rights reserved.

This is an automated confirmation. Please do not reply directly to this email. 