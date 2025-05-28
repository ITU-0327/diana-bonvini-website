<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $userName
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #dc2626; margin-bottom: 10px;">Appointment Cancelled</h1>
        <p style="font-size: 16px; color: #666;">Your appointment has been cancelled</p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($userName) ?>,</h2>
        <p>This email confirms that your appointment has been cancelled. Here are the details of the cancelled appointment:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Date:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280;"><del><?= $appointment->appointment_date->format('l, F j, Y') ?></del></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Time:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280;"><del><?= $appointment->appointment_time->format('g:i A') ?> (<?= $appointment->duration ?> minutes)</del></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Type:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280;"><del><?= ucfirst(str_replace('_', ' ', $appointment->appointment_type)) ?></del></td>
            </tr>
            <?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Related Request:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280;"><?= h($appointment->writing_service_request->writing_service_request_id) ?> - <?= h($appointment->writing_service_request->service_title) ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div style="background-color: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #b91c1c; margin-top: 0; font-size: 16px;">Cancellation Notice</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            Your appointment has been cancelled. If you didn't initiate this cancellation, or if you would like to reschedule,
            please contact our team as soon as possible.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <p>Would you like to reschedule your appointment?</p>
        <a href="<?= $this->Url->build(['controller' => 'Calendar', 'action' => 'availability', !empty($appointment->writing_service_request) ? $appointment->writing_service_request->writing_service_request_id : null, 'prefix' => false], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #3b82f6; color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; font-weight: bold;">Schedule New Appointment</a>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Writing Service. All rights reserved.</p>
    </div>
</div>