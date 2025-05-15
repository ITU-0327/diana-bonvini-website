<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $userName
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3b82f6; margin-bottom: 10px;">Appointment Confirmation</h1>
        <p style="font-size: 16px; color: #666;">Thank you for scheduling a consultation with us!</p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($userName) ?>,</h2>
        <p>Your appointment has been confirmed. Here are the details:</p>
        
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
                    <a href="<?= $appointment->meeting_link ?>" style="display: inline-block; background-color: #34A853; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-weight: bold;">
                        <span style="display: inline-flex; align-items: center;">
                            <span style="margin-right: 8px;">Join Google Meet</span>
                        </span>
                    </a>
                </td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
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
        <h3 style="color: #1e40af; margin-top: 0; font-size: 16px;">Important Information</h3>
        <ul style="padding-left: 20px; margin-bottom: 0; color: #1f2937;">
            <li>This is a virtual consultation via Google Meet.</li>
            <li>Please join 5 minutes before the scheduled time.</li>
            <li>Ensure you have a stable internet connection and a quiet environment.</li>
            <li>Have any relevant documents or information ready to discuss.</li>
            <li>Cancellations must be made at least 24 hours in advance.</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <p>Need to reschedule or have questions? Please contact us.</p>
        <a href="<?= $this->Url->build(['controller' => 'Calendar', 'action' => 'myAppointments', 'prefix' => false], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #3b82f6; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-weight: bold;">Manage My Appointments</a>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Writing Service. All rights reserved.</p>
    </div>
</div>