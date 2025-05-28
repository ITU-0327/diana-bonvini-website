<?php
/**
 * Coaching Payment Request Email Template
 *
 * Variables:
 * - $client_name: The client's name
 * - $coaching_service_request: The coaching service request entity
 * - $payment: The payment entity
 * - $amount: The formatted payment amount
 */
?>
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h1 style="color: #3366cc; margin-bottom: 5px;">Payment Request</h1>
        <p style="color: #666; font-size: 16px;">For Coaching Service #<?= h($coaching_service_request->coaching_service_request_id) ?></p>
    </div>

    <div style="margin-bottom: 30px;">
        <p>Dear <?= h($client_name) ?>,</p>
        <p>We hope this email finds you well. Your coaching service is ready to proceed, and we require payment to continue.</p>
        <p>Please find the payment details below:</p>
    </div>

    <div style="background-color: #f9f9f9; border-radius: 5px; padding: 15px; margin-bottom: 30px;">
        <h2 style="color: #333; font-size: 18px; margin-top: 0;">Payment Details</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee; width: 40%;"><strong>Service Title:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><?= h($coaching_service_request->service_title) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Service Type:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><?= h($coaching_service_request->service_type) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Amount Due:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold; color: #e74c3c;">$<?= h($amount) ?></td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-bottom: 30px;">
        <a href="<?= $this->Url->build([
            'controller' => 'CoachingServiceRequests',
            'action' => 'view',
            $coaching_service_request->coaching_service_request_id,
            '#' => 'messages'
        ], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #3366cc; color: white; font-weight: bold; padding: 12px 25px; text-decoration: none; border-radius: 4px;">
            Process Payment
        </a>
    </div>

    <div>
        <p>If you have any questions about this payment or your coaching service, please don't hesitate to reply to this message or contact us directly.</p>
        <p>Thank you for choosing our coaching services.</p>
        <p>Best regards,<br>
        Diana Bonvini</p>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #999; text-align: center;">
        <p>This is an automated email. Please do not reply directly to this message.</p>
        <p>&copy; <?= date('Y') ?> Diana Bonvini. All rights reserved.</p>
    </div>
</div> 