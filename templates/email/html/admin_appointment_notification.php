<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $adminName
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="background-color: #4b5563; color: white; padding: 15px; border-radius: 8px 8px 0 0; text-align: center;">
        <h1 style="margin: 0; font-size: 22px;">New Appointment Scheduled</h1>
        <p style="margin: 10px 0 0; font-size: 16px;">Admin Notification</p>
    </div>
    
    <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; padding: 20px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($adminName) ?>,</h2>
        <p>A new appointment has been scheduled. Here are the details:</p>
        
        <div style="background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Client:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
                        <?= h($appointment->user->first_name . ' ' . $appointment->user->last_name) ?>
                        <div style="color: #6b7280; font-size: 13px; margin-top: 3px;"><?= h($appointment->user->email) ?></div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Date:</td>
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
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Status:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
                        <span style="background-color: #fef3c7; color: #92400e; font-size: 12px; padding: 3px 8px; border-radius: 9999px; font-weight: bold;">
                            <?= ucfirst($appointment->status) ?>
                        </span>
                    </td>
                </tr>
                <?php if (!empty($appointment->meeting_link)): ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Meeting Link:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><a href="<?= $appointment->meeting_link ?>" style="color: #2563eb; text-decoration: none;">Google Meet Link</a></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($appointment->writing_service_request)): ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Related Request:</td>
                    <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">
                        <?= h($appointment->writing_service_request->writing_service_request_id) ?> - <?= h($appointment->writing_service_request->service_title) ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($appointment->description)): ?>
                <tr>
                    <td style="padding: 10px; font-weight: bold;">Client Notes:</td>
                    <td style="padding: 10px;"><?= nl2br(h($appointment->description)) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div style="text-align: center; margin-top: 25px;">
            <a href="<?= $this->Url->build(['controller' => 'Calendar', 'action' => 'edit', $appointment->appointment_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #2563eb; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; margin-right: 10px;">View Appointment</a>
            
            <a href="<?= $this->Url->build(['controller' => 'Calendar', 'action' => 'index', 'prefix' => 'Admin'], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #4b5563; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold;">View Calendar</a>
        </div>
    </div>
    
    <div style="margin-top: 20px; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated admin notification. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Writing Service Admin System</p>
    </div>
</div>