<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="background-color: #4f46e5; padding: 15px; border-radius: 8px 8px 0 0;">
            <h1 style="color: white; margin: 0; font-size: 24px;">New Appointment Accepted</h1>
        </div>
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-top: none; padding: 20px; border-radius: 0 0 8px 8px;">
            <p style="font-size: 16px; color: #4b5563; margin-top: 0;">A customer has confirmed an appointment slot</p>
        </div>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px; border-left: 5px solid #4f46e5;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($adminName) ?>,</h2>
        <p style="margin-bottom: 20px; line-height: 1.6; color: #4b5563;">
            <strong><?= h($customerName) ?></strong> has accepted and confirmed an appointment time slot. The appointment has been added to your Google Calendar.
        </p>
    </div>
    
    <div style="background-color: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
        <div style="background-color: #f3f4f6; padding: 12px 20px; border-bottom: 1px solid #e5e7eb;">
            <h3 style="margin: 0; color: #1f2937; font-size: 16px;">Appointment Details</h3>
        </div>
        <div style="padding: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 40%;">Customer:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($customerName) ?> (<?= h($customerEmail) ?>)</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Type:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= ucfirst(str_replace('_', ' ', h($appointment->appointment_type))) ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Date:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= $appointment->appointment_date->format('l, F j, Y') ?></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Time:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= $appointment->appointment_time->format('g:i A') ?> (30 minutes)</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Status:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
                        <span style="background-color: #059669; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; text-transform: uppercase;">Confirmed</span>
                    </td>
                </tr>
                <?php if (!empty($appointment->description)): ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Notes:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($appointment->description) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($appointment->meeting_link)): ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Meeting Link:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
                        <a href="<?= h($appointment->meeting_link) ?>" style="display: inline-block; background-color: #4f46e5; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: bold;">
                            Join Google Meet
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Related Request:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
                        <?= h($appointment->writing_service_request->service_title) ?>
                        <span style="color: #6b7280; font-size: 12px;">(ID: <?= h($appointment->writing_service_request->writing_service_request_id) ?>)</span>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <div style="background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 16px; margin-bottom: 25px;">
        <p style="color: #b91c1c; margin: 0; font-size: 14px;">
            <strong>Note:</strong> This appointment has been automatically added to your Google Calendar.
            <?php if (!empty($appointment->meeting_link)): ?>
            A Google Meet link has been created and shared with the customer.
            <?php endif; ?>
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
        <p>This is an automated message, please do not reply directly to this email.</p>
    </div>
</div>