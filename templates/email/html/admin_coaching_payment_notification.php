<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coaching_service_request
 * @var \App\Model\Entity\CoachingServicePayment $payment
 * @var string $admin_name
 * @var string $client_name
 * @var string $client_email
 * @var string $amount
 * @var string $transaction_id
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #059669; margin-bottom: 10px;">💰 New Coaching Payment Received</h1>
        <p style="font-size: 16px; color: #666;">A payment has been processed for a coaching service request</p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($admin_name) ?>,</h2>
        <p>A payment has been received for the following coaching service request:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Client:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($client_name) ?> (<?= h($client_email) ?>)</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Service Type:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h(ucwords(str_replace('_', ' ', $coaching_service_request->service_type))) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Request ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($coaching_service_request->coaching_service_request_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Amount:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #059669;">$<?= h($amount) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Date:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= $payment->payment_date ? $payment->payment_date->format('F j, Y \a\t g:i A') : 'Now' ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Transaction ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($transaction_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Payment Method:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= ucfirst(h($payment->payment_method ?? 'Online')) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Status:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><span style="color: #16a34a; font-weight: bold;">Paid</span></td>
            </tr>
        </table>
    </div>
    
    <div style="background-color: #ecfdf5; border-left: 4px solid #059669; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #065f46; margin-top: 0; font-size: 16px;">Action Required</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            The request status has been automatically updated to "in progress" if it was previously in "pending" status.
            Please ensure coaching services begin promptly and communicate with your client about next steps.
        </p>
    </div>
    
    <?php if (!empty($coaching_service_request->service_description)): ?>
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #1f2937; margin-top: 0; font-size: 16px;">Client's Requirements:</h3>
        <p style="margin-bottom: 0; color: #4b5563; font-style: italic;"><?= nl2br(h($coaching_service_request->service_description)) ?></p>
    </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coaching_service_request->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #059669; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold;">
            View Coaching Request Details
        </a>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Coaching Service. All rights reserved.</p>
    </div>
</div> 