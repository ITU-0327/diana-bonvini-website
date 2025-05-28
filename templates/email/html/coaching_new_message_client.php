<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $admin_name
 * @var string $client_name
 * @var string $message
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #6d28d9; margin-bottom: 10px;">New Message from <?= h($admin_name) ?></h1>
        <p style="font-size: 16px; color: #666;">Coaching Service Request #<?= h($coaching_service_request->coaching_service_request_id) ?></p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($client_name) ?>,</h2>
        <p>You have received a new message regarding your coaching service request.</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Service:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($coaching_service_request->service_title) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Request ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($coaching_service_request->coaching_service_request_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">From:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($admin_name) ?></td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <h3 style="font-size: 16px; color: #1f2937;">Message Content:</h3>
            <div style="background-color: #fff; padding: 15px; border-radius: 5px; white-space: pre-line;"><?= h($message) ?></div>
        </div>
    </div>
    
    <div style="background-color: #f5f3ff; border-left: 4px solid #6d28d9; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #4c1d95; margin-top: 0; font-size: 16px;">Reply to this Message</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            To view and respond to this message, please log in to your account:
        </p>
        <p style="margin-top: 10px;">
            <a href="<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #6d28d9; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">View & Reply</a>
        </p>
    </div>
    
    <div style="font-size: 12px; color: #666; text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Diana Bonvini Coaching Services. All rights reserved.</p>
    </div>
</div> 