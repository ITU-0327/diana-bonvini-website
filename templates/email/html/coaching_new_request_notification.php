<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var string $admin_name
 * @var string $client_name
 * @var string $client_email
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #059669; margin-bottom: 10px;">🎯 New Coaching Service Request</h1>
        <p style="font-size: 16px; color: #666;">A new coaching request requires your attention</p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($admin_name) ?>,</h2>
        <p>A new coaching service request has been submitted. Here are the details:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Service Type:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h(ucwords(str_replace('_', ' ', $coaching_service_request->service_type))) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Request ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($coaching_service_request->coaching_service_request_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Date:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= $coaching_service_request->created_at->format('F j, Y \a\t g:i A') ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Client:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($client_name) ?> (<?= h($client_email) ?>)</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Status:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><span style="color: #f59e0b; font-weight: bold;">Pending</span></td>
            </tr>
        </table>
        
        <?php if (!empty($coaching_service_request->service_description)): ?>
        <div style="margin-top: 20px;">
            <h3 style="font-size: 16px; color: #1f2937;">Client's Requirements:</h3>
            <div style="background-color: #fff; padding: 15px; border-radius: 5px; white-space: pre-line;"><?= h($coaching_service_request->service_description) ?></div>
        </div>
        <?php endif; ?>
    </div>
    
    <div style="background-color: #ecfdf5; border-left: 4px solid #059669; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #065f46; margin-top: 0; font-size: 16px;">Action Required</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            Please review this new coaching service request and take appropriate action. The client is waiting for your response.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #059669; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold;">
            View Request Details
        </a>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Coaching Service. All rights reserved.</p>
    </div>
</div> 