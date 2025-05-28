<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 * @var string $requestDate
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #6d28d9; margin-bottom: 10px;">New Writing Service Request</h1>
        <p style="font-size: 16px; color: #666;">A new request requires your attention</p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($adminName) ?>,</h2>
        <p>A new writing service request has been submitted. Here are the details:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Service:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($request->service_title) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Request ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($request->writing_service_request_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Date:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= $requestDate ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Customer:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($customerName) ?> (<?= h($customerEmail) ?>)</td>
            </tr>
            <?php if (!empty($request->service_type)): ?>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Type:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h(ucfirst($request->service_type)) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Status:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><span style="color: #f59e0b; font-weight: bold;">Pending</span></td>
            </tr>
        </table>
        
        <?php if (!empty($request->service_description)): ?>
        <div style="margin-top: 20px;">
            <h3 style="font-size: 16px; color: #1f2937;">Description:</h3>
            <div style="background-color: #fff; padding: 15px; border-radius: 5px; white-space: pre-line;"><?= h($request->service_description) ?></div>
        </div>
        <?php endif; ?>
    </div>
    
    <div style="background-color: #f5f3ff; border-left: 4px solid #6d28d9; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #4c1d95; margin-top: 0; font-size: 16px;">Action Required</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            Please review this new writing service request and take appropriate action. The customer is waiting for your response.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $request->writing_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #6d28d9; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold;">
            View Request Details
        </a>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Writing Service. All rights reserved.</p>
    </div>
</div> 