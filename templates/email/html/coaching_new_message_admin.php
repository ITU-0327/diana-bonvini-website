<?php
/**
 * Coaching New Message Admin Notification Email Template
 *
 * Variables:
 * - $admin_name: The admin's name
 * - $client_name: The client's name
 * - $coaching_service_request: The coaching service request entity
 * - $message: The message content
 */
?>
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h1 style="color: #3366cc; margin-bottom: 5px;">New Message</h1>
        <p style="color: #666; font-size: 16px;">From Client for Coaching Request #<?= h($coaching_service_request->coaching_service_request_id) ?></p>
    </div>

    <div style="margin-bottom: 30px;">
        <p>Dear <?= h($admin_name) ?>,</p>
        <p>You have received a new message from <strong><?= h($client_name) ?></strong> regarding their coaching service request.</p>
    </div>

    <div style="background-color: #f9f9f9; border-radius: 5px; padding: 15px; margin-bottom: 30px;">
        <h2 style="color: #333; font-size: 18px; margin-top: 0;">Request Details</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee; width: 40%;"><strong>Request ID:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><?= h($coaching_service_request->coaching_service_request_id) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Service Title:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><?= h($coaching_service_request->service_title) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Service Type:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><?= h($coaching_service_request->service_type) ?></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Client Name:</strong></td>
                <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><?= h($client_name) ?></td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f0f7ff; border-radius: 5px; padding: 15px; margin-bottom: 30px; border-left: 4px solid #3366cc;">
        <h2 style="color: #333; font-size: 18px; margin-top: 0;">Message</h2>
        <div style="padding: 10px 0; line-height: 1.5;">
            <?= nl2br(h($message)) ?>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 30px;">
        <a href="<?= $this->Url->build([
            'controller' => 'Admin/CoachingServiceRequests',
            'action' => 'view',
            $coaching_service_request->coaching_service_request_id,
            '#' => 'messageForm'
        ], ['fullBase' => true]) ?>" style="display: inline-block; background-color: #3366cc; color: white; font-weight: bold; padding: 12px 25px; text-decoration: none; border-radius: 4px;">
            Reply to Client
        </a>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #999; text-align: center;">
        <p>This is an automated notification. Please do not reply directly to this email.</p>
        <p>&copy; <?= date('Y') ?> Diana Bonvini. All rights reserved.</p>
    </div>
</div> 