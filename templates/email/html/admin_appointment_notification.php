<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $adminName
 * @var string $customerName
 * @var string $customerEmail
 */
?>
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 650px; margin: 0 auto; padding: 0; background-color: #f8fafc;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #1a365d 0%, #2d3748 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
        <div style="background-color: rgba(255,255,255,0.1); display: inline-block; padding: 15px; border-radius: 50%; margin-bottom: 20px;">
            <div style="width: 60px; height: 60px; background-color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <div style="color: #4299e1; font-size: 28px; font-weight: bold;">📅</div>
            </div>
        </div>
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">New Appointment Booked</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;">A customer has confirmed their consultation</p>
    </div>

    <!-- Main Content -->
    <div style="background-color: white; padding: 40px 30px;">
        <!-- Greeting & Summary -->
        <div style="margin-bottom: 30px;">
            <h2 style="color: #1a202c; margin: 0 0 15px 0; font-size: 24px; font-weight: 600;">Hello <?= h($adminName) ?>,</h2>
            <div style="background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%); border-left: 5px solid #38b2ac; padding: 20px; border-radius: 8px;">
                <p style="color: #234e52; margin: 0; font-size: 16px; line-height: 1.6; font-weight: 500;">
                    🎉 <strong><?= h($customerName) ?></strong> has accepted and confirmed their appointment time slot. 
                    The appointment has been automatically added to your Google Calendar.
                </p>
            </div>
        </div>

        <!-- Customer Information Card -->
        <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 12px; padding: 25px; margin-bottom: 30px; border-left: 5px solid #4299e1;">
            <h3 style="color: #2d3748; margin: 0 0 20px 0; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #48bb78; border-radius: 50%; margin-right: 10px;"></span>
                Customer Information
            </h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; width: 120px;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">👤</span>
                            <strong style="color: #2d3748; font-size: 14px;">Customer:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <?= h($customerName) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📧</span>
                            <strong style="color: #2d3748; font-size: 14px;">Email:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <a href="mailto:<?= h($customerEmail) ?>" style="color: #4299e1; text-decoration: none;">
                            <?= h($customerEmail) ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">✅</span>
                            <strong style="color: #2d3748; font-size: 14px;">Status:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; color: #4a5568; font-size: 15px;">
                        <span style="background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                            ✓ Confirmed
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Appointment Details Card -->
        <div style="background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%); border-radius: 12px; padding: 25px; margin-bottom: 30px; border-left: 5px solid #48bb78;">
            <h3 style="color: #2d3748; margin: 0 0 20px 0; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #4299e1; border-radius: 50%; margin-right: 10px;"></span>
                Appointment Details
            </h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; vertical-align: top; width: 120px;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📅</span>
                            <strong style="color: #2d3748; font-size: 14px;">Date:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; color: #2d3748; font-size: 15px; font-weight: 600;">
                        <?= $appointment->appointment_date->format('l, F j, Y') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">🕐</span>
                            <strong style="color: #2d3748; font-size: 14px;">Time:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; color: #2d3748; font-size: 15px; font-weight: 600;">
                        <?= $appointment->appointment_time->format('g:i A') ?> 
                        <span style="background-color: #ed8936; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;">
                            30 min
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">✍️</span>
                            <strong style="color: #2d3748; font-size: 14px;">Service:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; color: #2d3748; font-size: 15px; font-weight: 500;">
                        <?= ucfirst(str_replace('_', ' ', h($appointment->appointment_type))) ?>
                    </td>
                </tr>
                <?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📋</span>
                            <strong style="color: #2d3748; font-size: 14px;">Project:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; color: #2d3748; font-size: 15px; font-weight: 500;">
                        <?= h($appointment->writing_service_request->service_title) ?>
                        <div style="color: #68d391; font-size: 13px; margin-top: 2px; font-weight: 600;">
                            Request ID: <?= h($appointment->writing_service_request->writing_service_request_id) ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($appointment->description)): ?>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📝</span>
                            <strong style="color: #2d3748; font-size: 14px;">Notes:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #c6f6d5; color: #2d3748; font-size: 15px; line-height: 1.5;">
                        <?= h($appointment->description) ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($appointment->meeting_link)): ?>
                <tr>
                    <td style="padding: 12px 0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">🎥</span>
                            <strong style="color: #2d3748; font-size: 14px;">Meeting:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; color: #2d3748; font-size: 15px;">
                        <a href="<?= h($appointment->meeting_link) ?>" style="display: inline-flex; align-items: center; background: linear-gradient(135deg, #34a853 0%, #137333 100%); color: white; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.3s ease;">
                            <span style="margin-right: 8px;">🎬</span>
                            Join Google Meet
                        </a>
                        <div style="color: #68d391; font-size: 12px; margin-top: 8px; font-weight: 500;">
                            🔗 Meeting link has been shared with customer
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- System Notifications -->
        <div style="background-color: #fef5e7; border: 1px solid #f6e05e; border-radius: 12px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #d69e2e; margin: 0 0 15px 0; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                <span style="font-size: 20px; margin-right: 10px;">⚡</span>
                Automatic Updates
            </h3>
            <ul style="margin: 0; padding-left: 0; list-style: none; color: #744210;">
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;"><strong>Google Calendar:</strong> Appointment has been added to your calendar</span>
                </li>
                <?php if (!empty($appointment->meeting_link)): ?>
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;"><strong>Google Meet:</strong> Meeting link created and shared with customer</span>
                </li>
                <?php endif; ?>
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;"><strong>Email Notification:</strong> Customer has received confirmation email</span>
                </li>
                <li style="display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;"><strong>Database:</strong> Appointment record created and status updated</span>
                </li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin-bottom: 30px;">
            <?php if (!empty($appointment->meeting_link)): ?>
            <a href="<?= h($appointment->meeting_link) ?>" style="display: inline-block; background: linear-gradient(135deg, #34a853 0%, #137333 100%); color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 16px; margin: 0 10px 10px 0; box-shadow: 0 4px 15px rgba(52, 168, 83, 0.3); transition: all 0.3s ease;">
                🎬 Join Meeting
            </a>
            <?php endif; ?>
            <a href="mailto:<?= h($customerEmail) ?>" style="display: inline-block; background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 16px; margin: 0 10px 10px 0; box-shadow: 0 4px 15px rgba(66, 153, 225, 0.3); transition: all 0.3s ease;">
                📧 Contact Customer
            </a>
        </div>

        <!-- Admin Notes -->
        <div style="background-color: #e6f3ff; border: 1px solid #bee3f8; border-radius: 12px; padding: 20px; text-align: center;">
            <h4 style="color: #2c5282; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">📋 Admin Reminder</h4>
            <p style="color: #2d3748; margin: 0; font-size: 14px; line-height: 1.5;">
                Remember to prepare any materials needed for the consultation. The customer is expecting a professional writing consultation experience.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div style="background-color: #2d3748; padding: 30px; text-align: center; border-radius: 0 0 12px 12px;">
        <p style="color: #a0aec0; margin: 0 0 10px 0; font-size: 14px;">
            This is an automated admin notification. Do not reply directly to this email.
        </p>
        <p style="color: #718096; margin: 0; font-size: 12px;">
            &copy; <?= date('Y') ?> Diana Bonvini Writing Services - Admin Dashboard
        </p>
        <div style="margin-top: 20px;">
            <div style="display: inline-block; background-color: #4a5568; border-radius: 6px; padding: 8px 12px;">
                <span style="color: white; font-size: 12px; font-weight: 500;">Professional Writing Services - Admin Portal</span>
            </div>
        </div>
    </div>
</div>