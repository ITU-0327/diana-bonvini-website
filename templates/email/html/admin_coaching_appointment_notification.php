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
    <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
        <div style="background-color: rgba(255,255,255,0.1); display: inline-block; padding: 15px; border-radius: 50%; margin-bottom: 20px;">
            <div style="width: 60px; height: 60px; background-color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <div style="color: #2563eb; font-size: 24px; font-weight: bold;">🔔</div>
            </div>
        </div>
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">New Coaching Appointment!</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;">A coaching session has been booked</p>
    </div>

    <!-- Main Content -->
    <div style="background-color: white; padding: 40px 30px;">
        <!-- Greeting -->
        <div style="margin-bottom: 30px;">
            <h2 style="color: #1a202c; margin: 0 0 15px 0; font-size: 24px; font-weight: 600;">Hello <?= h($adminName) ?>,</h2>
            <p style="color: #4a5568; margin: 0; font-size: 16px; line-height: 1.6;">
                A new coaching appointment has been booked by <strong><?= h($customerName) ?></strong>. Please review the details below and prepare for the session.
            </p>
        </div>

        <!-- Appointment Details Card -->
        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 12px; padding: 25px; margin-bottom: 30px; border-left: 5px solid #2563eb;">
            <h3 style="color: #1e40af; margin: 0 0 20px 0; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #3b82f6; border-radius: 50%; margin-right: 10px;"></span>
                Appointment Details
            </h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; width: 120px;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">👤</span>
                            <strong style="color: #1e40af; font-size: 14px;">Client:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <?= h($customerName) ?>
                        <div style="color: #6b7280; font-size: 13px; margin-top: 2px;">
                            <a href="mailto:<?= h($customerEmail) ?>" style="color: #3b82f6; text-decoration: none;"><?= h($customerEmail) ?></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📅</span>
                            <strong style="color: #1e40af; font-size: 14px;">Date:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <?= $appointment->appointment_date->format('l, F j, Y') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">🕐</span>
                            <strong style="color: #1e40af; font-size: 14px;">Time:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <?= $appointment->appointment_time->format('g:i A') ?> 
                        <span style="background-color: #f59e0b; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;">
                            <?= $appointment->duration ?> min
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">🚀</span>
                            <strong style="color: #1e40af; font-size: 14px;">Service:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <?= ucfirst(str_replace('_', ' ', $appointment->appointment_type)) ?>
                    </td>
                </tr>
                <?php if (!empty($appointment->coaching_service_request_id)): ?>
                    <?php
                    // Try to get the coaching service request details
                    $coachingRequest = null;
                    if (isset($appointment->coaching_service_request)) {
                        $coachingRequest = $appointment->coaching_service_request;
                    }
                    ?>
                    <?php if ($coachingRequest && isset($coachingRequest->coaching_service_request_id) && isset($coachingRequest->service_title)): ?>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                            <div style="display: flex; align-items: center;">
                                <span style="font-size: 16px; margin-right: 8px;">📋</span>
                                <strong style="color: #1e40af; font-size: 14px;">Project:</strong>
                            </div>
                        </td>
                        <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                            <?= h($coachingRequest->service_title) ?>
                            <div style="color: #6b7280; font-size: 13px; margin-top: 2px;">
                                Request ID: <?= h($coachingRequest->coaching_service_request_id) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($appointment->meeting_link)): ?>
                <tr>
                    <td style="padding: 12px 0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">🎥</span>
                            <strong style="color: #1e40af; font-size: 14px;">Meeting:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; color: #4a5568; font-size: 15px;">
                        <a href="<?= h($appointment->meeting_link) ?>" style="display: inline-flex; align-items: center; background: linear-gradient(135deg, #34a853 0%, #137333 100%); color: white; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.3s ease;">
                            <span style="margin-right: 8px;">🎬</span>
                            Join Google Meet
                        </a>
                        <div style="color: #6b7280; font-size: 12px; margin-top: 8px;">
                            💡 This meeting link has been shared with the client
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($appointment->description)): ?>
                <tr>
                    <td style="padding: 12px 0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📝</span>
                            <strong style="color: #1e40af; font-size: 14px;">Notes:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; color: #4a5568; font-size: 15px; line-height: 1.5;">
                        <?= nl2br(h($appointment->description)) ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Action Items -->
        <div style="background-color: #fefce8; border: 1px solid #fde047; border-radius: 12px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #a16207; margin: 0 0 15px 0; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                <span style="font-size: 20px; margin-right: 10px;">📋</span>
                Pre-Session Preparation
            </h3>
            <ul style="margin: 0; padding-left: 0; list-style: none; color: #713f12;">
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #ca8a04; margin-right: 10px; font-weight: bold;">•</span>
                    <span style="line-height: 1.5;">Review the coaching service request and client's goals</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #ca8a04; margin-right: 10px; font-weight: bold;">•</span>
                    <span style="line-height: 1.5;">Prepare coaching materials and assessment tools if needed</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #ca8a04; margin-right: 10px; font-weight: bold;">•</span>
                    <span style="line-height: 1.5;">Test your internet connection and Google Meet setup</span>
                </li>
                <li style="display: flex; align-items: flex-start;">
                    <span style="color: #ca8a04; margin-right: 10px; font-weight: bold;">•</span>
                    <span style="line-height: 1.5;">Join the meeting 5 minutes early to ensure everything is ready</span>
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
            <a href="<?= $this->Url->build(['controller' => 'CoachingServiceRequests', 'action' => 'view', $appointment->coaching_service_request_id, 'prefix' => 'Admin'], ['fullBase' => true]) ?>" style="display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 16px; margin: 0 10px 10px 0; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3); transition: all 0.3s ease;">
                📋 View Request
            </a>
        </div>

        <!-- Client Contact -->
        <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; text-align: center;">
            <h4 style="color: #0369a1; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">Client Contact Information</h4>
            <p style="color: #0c4a6e; margin: 0; font-size: 14px; line-height: 1.5;">
                <strong><?= h($customerName) ?></strong><br>
                Email: <a href="mailto:<?= h($customerEmail) ?>" style="color: #0369a1; text-decoration: none;"><?= h($customerEmail) ?></a>
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div style="background-color: #1f2937; padding: 30px; text-align: center; border-radius: 0 0 12px 12px;">
        <p style="color: #9ca3af; margin: 0 0 10px 0; font-size: 14px;">
            This is an automated notification. Please prepare for your coaching session accordingly.
        </p>
        <p style="color: #6b7280; margin: 0; font-size: 12px;">
            &copy; <?= date('Y') ?> Diana Bonvini Coaching Services. All rights reserved.
        </p>
        <div style="margin-top: 20px;">
            <div style="display: inline-block; background-color: #374151; border-radius: 6px; padding: 8px 12px;">
                <span style="color: white; font-size: 12px; font-weight: 500;">Professional Coaching Services</span>
            </div>
        </div>
    </div>
</div> 