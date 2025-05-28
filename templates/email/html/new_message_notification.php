<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 * @var string $messageContent
 * @var string $messageDate
 * @var string $requestId
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #6d28d9; margin-bottom: 10px;">New Message from Customer</h1>
        <p style="font-size: 16px; color: #666;">Writing Service Request #<?= h($requestId) ?></p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($adminName) ?>,</h2>
        <p><?= h($customerName) ?> has sent a new message regarding writing service request.</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Service:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($request->service_title) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Request ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($requestId) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Message Date:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($messageDate) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Customer:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($customerName) ?> (<?= h($customerEmail) ?>)</td>
            </tr>
        </table>
        
        <div style="margin-top: 20px;">
            <h3 style="font-size: 16px; color: #1f2937;">Message Content:</h3>
            <div style="background-color: #fff; padding: 15px; border-radius: 5px; white-space: pre-line;"><?= h($messageContent) ?></div>
        </div>
    </div>
    
    <div style="background-color: #f5f3ff; border-left: 4px solid #6d28d9; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #4c1d95; margin-top: 0; font-size: 16px;">Action Required</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            Please review and respond to this message at your earliest convenience. The customer may be waiting for your response.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $requestId, 'prefix' => 'Admin'], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #6d28d9; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold;">
            Respond to Message
        </a>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Writing Service. All rights reserved.</p>
    </div>
</div> 