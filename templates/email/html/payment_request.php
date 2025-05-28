<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WritingServiceRequest $request
 * @var \App\Model\Entity\WritingServicePayment $payment
 * @var float $amount
 * @var string $userName
 * @var string $paymentId
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #f59e0b; margin-bottom: 10px;">Payment Request</h1>
        <p style="font-size: 16px; color: #666;">Action required for your writing service</p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($userName) ?>,</h2>
        <p>A payment request has been created for your writing service. Please complete this payment to proceed with your request.</p>
        
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
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Amount:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #f59e0b;">$<?= number_format($amount, 2) ?></td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 25px;">
            <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'view', $request->writing_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #f59e0b; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold;">
                Pay Now
            </a>
        </div>
    </div>
    
    <div style="background-color: #fff7ed; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #9a3412; margin-top: 0; font-size: 16px;">Next Steps</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            Once your payment is processed, your writing service request will be moved to the next stage of completion. 
            Our team will begin working on your request immediately.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <p>Have questions about this payment? Please contact our support team.</p>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Writing Service. All rights reserved.</p>
    </div>
</div> 