<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\Core\Configure;
use Cake\Log\Log;

/**
 * Mailer component for sending emails
 */
class MailerComponent extends Component
{
    /**
     * Send an email
     *
     * @param array $options Email options
     * @return bool Success
     */
    public function send(array $options): bool
    {
        try {
            // Create a new mailer
            $mailer = new Mailer('default');
            
            // Set email options
            $mailer->setFrom(Configure::read('App.emailFrom', 'noreply@writingservice.com'))
                ->setTo($options['to'])
                ->setSubject($options['subject']);
            
            // Set email format
            if (isset($options['emailFormat'])) {
                $mailer->setEmailFormat($options['emailFormat']);
            }
            
            // Set optional CC and BCC
            if (isset($options['cc'])) {
                $mailer->setCc($options['cc']);
            }
            
            if (isset($options['bcc'])) {
                $mailer->setBcc($options['bcc']);
            }
            
            // Set template and layout if provided
            if (isset($options['template'])) {
                $mailer->viewBuilder()
                    ->setTemplate($options['template'])
                    ->setLayout($options['layout'] ?? 'default');
            }
            
            // Set view variables
            if (isset($options['viewVars'])) {
                $mailer->setViewVars($options['viewVars']);
            }
            
            // Send the email
            $result = $mailer->deliver();
            
            Log::info('Email sent successfully to ' . (is_array($options['to']) ? implode(', ', $options['to']) : $options['to']));
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }
} 