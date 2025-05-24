<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest
 * @var \App\Model\Entity\CoachingServicePayment $payment
 * @var string $client_name
 * @var string $amount
 * @var string $transaction_id
 */
?>
<div style="font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #16a34a; margin-bottom: 10px;">Payment Confirmation</h1>
        <p style="font-size: 16px; color: #666;">Thank you for your payment!</p>
    </div>
    
    <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h2 style="color: #1f2937; margin-top: 0; font-size: 18px;">Hello <?= h($client_name) ?>,</h2>
        <p>Your payment for coaching services has been successfully processed. Here are your payment details:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; width: 150px;">Service:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($coachingServiceRequest->service_title) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Request ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($coachingServiceRequest->coaching_service_request_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Amount:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #16a34a;">$<?= h($amount) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Date:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= $payment->payment_date ? $payment->payment_date->format('F j, Y \a\t g:i A') : 'Just now' ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Transaction ID:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><?= h($transaction_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Status:</td>
                <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;"><span style="color: #16a34a; font-weight: bold;">Paid</span></td>
            </tr>
        </table>
    </div>
    
    <div style="background-color: #ecfdf5; border-left: 4px solid #16a34a; padding: 15px; margin-bottom: 25px;">
        <h3 style="color: #065f46; margin-top: 0; font-size: 16px;">What's Next?</h3>
        <p style="margin-bottom: 0; color: #1f2937;">
            We have now confirmed your payment and can proceed with your coaching service request. You can check the status of your request 
            at any time by logging into your account. We'll keep you updated on our progress and will be in touch to schedule your coaching session.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $coachingServiceRequest->coaching_service_request_id, 'prefix' => false], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #16a34a; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold;">
            View Request Status
        </a>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 12px;">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Diana Bonvini Coaching Services. All rights reserved.</p>
    </div>
</div> 