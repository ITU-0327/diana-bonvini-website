<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $client_name
 * @var string $old_status
 * @var string $new_status
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #059669; margin-bottom: 10px;">Status Update</h1>
        <p style="font-size: 16px; color: #666;">Coaching Service Request #<?= h($coaching_service_request->coaching_service_request_id) ?></p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($client_name) ?>,</h2>
        <p>The status of your coaching service request has been updated.</p>
        
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
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Previous Status:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($old_status) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">New Status:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #059669;"><?= h($new_status) ?></td>
            </tr>
        </table>
    </div>
    
    <div style="background-color: #ecfdf5; border-left: 4px solid #059669; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #047857; margin-top: 0; font-size: 16px;">View Your Request</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            To view the latest updates and communicate with us, please log in to your account:
        </p>
        <p style="margin-top: 10px;">
            <a href="<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #059669; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold;">View Request</a>
        </p>
    </div>
    
    <div style="font-size: 12px; color: #666; text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Diana Bonvini Coaching Services. All rights reserved.</p>
    </div>
</div> 