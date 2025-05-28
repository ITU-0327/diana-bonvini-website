<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $userName
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3b82f6; margin-bottom: 10px;">Appointment Reminder</h1>
        <p style="font-size: 16px; color: #666;">Your appointment is tomorrow!</p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($userName) ?>,</h2>
        <p>This is a friendly reminder about your appointment tomorrow. Here are the details:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Date:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= $appointment->appointment_date->format('l, F j, Y') ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Time:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= $appointment->appointment_time->format('g:i A') ?> (<?= $appointment->duration ?> minutes)</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Type:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= ucfirst(str_replace('_', ' ', $appointment->appointment_type)) ?></td>
            </tr>
            <?php if (!empty($appointment->meeting_link)): ?>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Meeting Link:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
                    <a href="<?= $appointment->meeting_link ?>" style="color: #3b82f6; text-decoration: none;">Google Meet Link</a>
                    <div style="color: #6b7280; font-size: 13px; margin-top: 3px;">Click the link above to join your meeting tomorrow</div>
                </td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($appointment->writing_service_request)): ?>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Related Request:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($appointment->writing_service_request->writing_service_request_id) ?> - <?= h($appointment->writing_service_request->service_title) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($appointment->description)): ?>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Notes:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= nl2br(h($appointment->description)) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #1e40af; margin-top: 0; font-size: 16px;">Preparation Checklist</h3>
        <ul style="padding-left: 20px; margin-bottom: 0; color: #1f2937;">
            <li>Join the meeting 5 minutes early to test your connection</li>
            <li>Ensure you have a stable internet connection</li>
            <li>Find a quiet location with minimal background noise</li>
            <li>Prepare any questions or notes you want to discuss</li>
            <li>Have any relevant documents ready to reference</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <p>Need to reschedule? Please contact us as soon as possible.</p>
        <?php if (!empty($appointment->meeting_link)): ?>
        <a href="<?= $appointment->meeting_link ?>" style="display: inline-block; background-color: #10b981; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-weight: bold; margin-right: 10px;">Join Meeting (Tomorrow)</a>
        <?php endif; ?>
        <a href="<?= $this->Url->build(['controller' => 'Calendar', 'action' => 'myAppointments', 'prefix' => false], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #6b7280; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-weight: bold;">Manage Appointments</a>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated reminder. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Writing Service. All rights reserved.</p>
    </div>
</div>