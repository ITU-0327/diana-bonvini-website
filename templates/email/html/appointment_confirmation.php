<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Appointment $appointment
 * @var string $userName
 */
?>
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 650px; margin: 0 auto; padding: 0; background-color: #f8fafc;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; border-radius: 12px 12px 0 0;">
        <div style="background-color: rgba(255,255,255,0.1); display: inline-block; padding: 15px; border-radius: 50%; margin-bottom: 20px;">
            <div style="width: 60px; height: 60px; background-color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <div style="color: #667eea; font-size: 28px; font-weight: bold;">✓</div>
            </div>
        </div>
        <h1 style="color: white; margin: 0; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">Appointment Confirmed!</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;">Your writing consultation is all set</p>
    </div>

    <!-- Main Content -->
    <div style="background-color: white; padding: 40px 30px;">
        <!-- Greeting -->
        <div style="margin-bottom: 30px;">
            <h2 style="color: #1a202c; margin: 0 0 15px 0; font-size: 24px; font-weight: 600;">Hello <?= h($userName) ?>,</h2>
            <p style="color: #4a5568; margin: 0; font-size: 16px; line-height: 1.6;">
                Great news! Your writing consultation appointment has been successfully confirmed. We're excited to help you with your writing project.
            </p>
        </div>

        <!-- Appointment Details Card -->
        <div style="background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius: 12px; padding: 25px; margin-bottom: 30px; border-left: 5px solid #667eea;">
            <h3 style="color: #2d3748; margin: 0 0 20px 0; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                <span style="display: inline-block; width: 8px; height: 8px; background-color: #48bb78; border-radius: 50%; margin-right: 10px;"></span>
                Appointment Details
            </h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; width: 120px;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📅</span>
                            <strong style="color: #2d3748; font-size: 14px;">Date:</strong>
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
                            <strong style="color: #2d3748; font-size: 14px;">Time:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <?= $appointment->appointment_time->format('g:i A') ?> 
                        <span style="background-color: #ed8936; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;">
                            <?= $appointment->duration ?> min
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">✍️</span>
                            <strong style="color: #2d3748; font-size: 14px;">Service:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <?= ucfirst(str_replace('_', ' ', $appointment->appointment_type)) ?>
                    </td>
                </tr>
                <?php if (!empty($appointment->writing_service_request) && isset($appointment->writing_service_request->writing_service_request_id) && isset($appointment->writing_service_request->service_title)): ?>
                <tr>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📋</span>
                            <strong style="color: #2d3748; font-size: 14px;">Project:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #4a5568; font-size: 15px; font-weight: 500;">
                        <?= h($appointment->writing_service_request->service_title) ?>
                        <div style="color: #a0aec0; font-size: 13px; margin-top: 2px;">
                            Request ID: <?= h($appointment->writing_service_request->writing_service_request_id) ?>
                        </div>
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
                    <td style="padding: 12px 0; color: #4a5568; font-size: 15px;">
                        <a href="<?= h($appointment->meeting_link) ?>" style="display: inline-flex; align-items: center; background: linear-gradient(135deg, #34a853 0%, #137333 100%); color: white; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: all 0.3s ease;">
                            <span style="margin-right: 8px;">🎬</span>
                            Join Google Meet
                        </a>
                        <div style="color: #718096; font-size: 12px; margin-top: 8px;">
                            💡 Join 5 minutes early to test your connection
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($appointment->description)): ?>
                <tr>
                    <td style="padding: 12px 0; vertical-align: top;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 16px; margin-right: 8px;">📝</span>
                            <strong style="color: #2d3748; font-size: 14px;">Notes:</strong>
                        </div>
                    </td>
                    <td style="padding: 12px 0; color: #4a5568; font-size: 15px; line-height: 1.5;">
                        <?= nl2br(h($appointment->description)) ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Preparation Checklist -->
        <div style="background-color: #ebf8ff; border: 1px solid #bee3f8; border-radius: 12px; padding: 25px; margin-bottom: 30px;">
            <h3 style="color: #2c5282; margin: 0 0 15px 0; font-size: 18px; font-weight: 600; display: flex; align-items: center;">
                <span style="font-size: 20px; margin-right: 10px;">📋</span>
                Pre-Meeting Checklist
            </h3>
            <ul style="margin: 0; padding-left: 0; list-style: none; color: #2d3748;">
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;">Test your internet connection and camera/microphone</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;">Find a quiet, well-lit space with minimal distractions</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;">Prepare your questions and any materials you'd like to discuss</span>
                </li>
                <li style="margin-bottom: 10px; display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;">Have pen and paper ready for taking notes</span>
                </li>
                <li style="display: flex; align-items: flex-start;">
                    <span style="color: #48bb78; margin-right: 10px; font-weight: bold;">✓</span>
                    <span style="line-height: 1.5;">Join the meeting 5 minutes early</span>
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
            <a href="<?= $this->Url->build(['controller' => 'Calendar', 'action' => 'myAppointments', 'prefix' => false], ['fullBase' => true]) ?>" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; padding: 15px 30px; border-radius: 8px; font-weight: 600; font-size: 16px; margin: 0 10px 10px 0; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); transition: all 0.3s ease;">
                📅 My Appointments
            </a>
        </div>

        <!-- Need Help Section -->
        <div style="background-color: #fffaf0; border: 1px solid #fed7aa; border-radius: 12px; padding: 20px; text-align: center;">
            <h4 style="color: #c05621; margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">Need to Reschedule?</h4>
            <p style="color: #744210; margin: 0; font-size: 14px; line-height: 1.5;">
                If you need to reschedule or have any questions, please contact us at least 24 hours in advance. We're here to help!
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div style="background-color: #2d3748; padding: 30px; text-align: center; border-radius: 0 0 12px 12px;">
        <p style="color: #a0aec0; margin: 0 0 10px 0; font-size: 14px;">
            This is an automated confirmation. Please do not reply directly to this email.
        </p>
        <p style="color: #718096; margin: 0; font-size: 12px;">
            &copy; <?= date('Y') ?> Diana Bonvini Writing Services. All rights reserved.
        </p>
        <div style="margin-top: 20px;">
            <div style="display: inline-block; background-color: #4a5568; border-radius: 6px; padding: 8px 12px;">
                <span style="color: white; font-size: 12px; font-weight: 500;">Professional Writing Services</span>
            </div>
        </div>
    </div>
</div>