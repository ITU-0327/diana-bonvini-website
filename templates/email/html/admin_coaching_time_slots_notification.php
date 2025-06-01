<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $client_name
 * @var string $client_email
 * @var string $admin_name
 * @var string $time_slots
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #2563eb; margin-bottom: 10px;">📅 Time Slots Sent</h1>
        <p style="font-size: 16px; color: #666;">Coaching Service Request #<?= h($coaching_service_request->coaching_service_request_id) ?></p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($admin_name) ?>,</h2>
        <p>You have successfully sent time slots to <strong><?= h($client_name) ?></strong> for their coaching service request.</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Client:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($client_name) ?> (<?= h($client_email) ?>)</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Service:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($coaching_service_request->service_title) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Request ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($coaching_service_request->coaching_service_request_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Sent At:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= date('F j, Y g:i A') ?></td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <h3 style="font-size: 16px; color: #1f2937;">Time Slots Sent:</h3>
            <div style="background-color: #fff; padding: 15px; border-radius: 5px; white-space: pre-line; border-left: 4px solid #2563eb;"><?= h($time_slots) ?></div>
        </div>
    </div>
    
    <div style="background-color: #eff6ff; border-left: 4px solid #2563eb; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #1d4ed8; margin-top: 0; font-size: 16px;">Next Steps</h3>
        <ul style="margin: 0; padding-left: 20px; color: #1f2937;">
            <li style="margin-bottom: 8px;">The client has been notified via email</li>
            <li style="margin-bottom: 8px;">They can accept a time slot from their dashboard</li>
            <li style="margin-bottom: 8px;">When accepted, an appointment will be created with a Google Meet link</li>
            <li>Both you and the client will receive confirmation emails with meeting details</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">View Request</a>
    </div>
    
    <div style="font-size: 12px; color: #666; text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated notification. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Diana Bonvini Coaching Services. All rights reserved.</p>
    </div>
</div> 