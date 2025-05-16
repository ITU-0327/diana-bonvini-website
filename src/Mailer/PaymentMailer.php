<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\WritingServicePayment;
use App\Model\Entity\WritingServiceRequest;
use App\Model\Entity\User;
use Cake\Mailer\Mailer;
use Cake\Mailer\Transport\SmtpTransport;

/**
 * PaymentMailer for sending payment-related emails
 */
class PaymentMailer extends Mailer
{
    /**
     * Common configuration applied to all emails
     *
     * @param string $to Email recipient
     * @param string $name Recipient name
     * @param string $subject Email subject
     * @return $this
     */
    private function configureCommon(string $to, string $name, string $subject): self
    {
        $this->setTo($to, $name)
            ->setSubject($subject)
            ->setEmailFormat('both');
            
        $this->viewBuilder()->setLayout('default');
        
        return $this;
    }

    /**
     * Build email to notify customer of a new payment request
     *
     * @param \App\Model\Entity\WritingServiceRequest $request The writing service request
     * @param \App\Model\Entity\WritingServicePayment $payment The payment entity
     * @param float $amount The payment amount
     * @return void
     */
    public function paymentRequest(WritingServiceRequest $request, WritingServicePayment $payment, float $amount): void
    {
        // Make sure we have access to the user
        if (empty($request->user)) {
            throw new \InvalidArgumentException('User information is required for payment request email');
        }
        
        $this->configureCommon(
            $request->user->email, 
            $request->user->first_name . ' ' . $request->user->last_name, 
            'Payment Request: ' . $request->service_title
        );
        
        $this->setViewVars([
            'request' => $request,
            'payment' => $payment,
            'amount' => $amount,
            'userName' => $request->user->first_name,
            'paymentId' => $payment->writing_service_payment_id,
        ]);
        
        $this->viewBuilder()->setTemplate('payment_request');
    }
    
    /**
     * Build email to notify customer of payment confirmation
     *
     * @param \App\Model\Entity\WritingServiceRequest $request The writing service request
     * @param \App\Model\Entity\WritingServicePayment $payment The payment entity
     * @param array $paymentDetails Additional payment details
     * @return void
     */
    public function paymentConfirmation(WritingServiceRequest $request, WritingServicePayment $payment, array $paymentDetails = []): void
    {
        // Make sure we have access to the user
        if (empty($request->user)) {
            throw new \InvalidArgumentException('User information is required for payment confirmation email');
        }
        
        $this->configureCommon(
            $request->user->email, 
            $request->user->first_name . ' ' . $request->user->last_name, 
            'Payment Confirmation: ' . $request->service_title
        );
        
        $this->setViewVars([
            'request' => $request,
            'payment' => $payment,
            'paymentDetails' => $paymentDetails,
            'userName' => $request->user->first_name,
            'transactionId' => $payment->transaction_id ?? 'Not Available',
        ]);
        
        $this->viewBuilder()->setTemplate('payment_confirmation');
    }
    
    /**
     * Build email to notify admin of a new payment
     *
     * @param \App\Model\Entity\WritingServiceRequest $request The writing service request
     * @param \App\Model\Entity\WritingServicePayment $payment The payment entity
     * @param string $adminEmail Admin email address
     * @param string $adminName Admin name
     * @param array $paymentDetails Additional payment details
     * @return void
     */
    public function adminPaymentNotification(WritingServiceRequest $request, WritingServicePayment $payment, string $adminEmail, string $adminName, array $paymentDetails = []): void
    {
        $this->configureCommon($adminEmail, $adminName, '💰 New Payment Received: ' . $request->service_title);
        
        $this->setViewVars([
            'request' => $request,
            'payment' => $payment,
            'paymentDetails' => $paymentDetails,
            'adminName' => $adminName,
            'customerName' => $request->user->first_name . ' ' . $request->user->last_name,
            'customerEmail' => $request->user->email,
            'transactionId' => $payment->transaction_id ?? 'Not Available',
        ]);
        
        $this->viewBuilder()->setTemplate('admin_payment_notification');
    }
    
    /**
     * Build email to notify admin of a new writing service request
     *
     * @param \App\Model\Entity\WritingServiceRequest $request The writing service request
     * @param string $adminEmail Admin email address
     * @param string $adminName Admin name
     * @return void
     */
    public function newRequestNotification(WritingServiceRequest $request, string $adminEmail, string $adminName): void
    {
        // Make sure we have access to the user
        if (empty($request->user)) {
            throw new \InvalidArgumentException('User information is required for new request notification email');
        }
        
        $this->configureCommon($adminEmail, $adminName, 'New Writing Service Request: ' . $request->service_title);
        
        $this->setViewVars([
            'request' => $request,
            'adminName' => $adminName,
            'customerName' => $request->user->first_name . ' ' . $request->user->last_name,
            'customerEmail' => $request->user->email,
            'requestDate' => $request->created_at->format('F j, Y \a\t g:i A'),
        ]);
        
        $this->viewBuilder()->setTemplate('new_request_notification');
    }
    
    /**
     * Build email to notify admin of a new message from customer
     *
     * @param \App\Model\Entity\WritingServiceRequest $request The writing service request
     * @param string $message The message content
     * @param string $adminEmail Admin email address
     * @param string $adminName Admin name
     * @return void
     */
    public function newMessageNotification(WritingServiceRequest $request, string $message, string $adminEmail, string $adminName): void
    {
        // Make sure we have access to the user
        if (empty($request->user)) {
            throw new \InvalidArgumentException('User information is required for message notification email');
        }
        
        $this->configureCommon($adminEmail, $adminName, 'New Message: ' . $request->service_title);
        
        $this->setViewVars([
            'request' => $request,
            'adminName' => $adminName,
            'customerName' => $request->user->first_name . ' ' . $request->user->last_name,
            'customerEmail' => $request->user->email,
            'messageContent' => $message,
            'messageDate' => date('F j, Y \a\t g:i A'),
            'requestId' => $request->writing_service_request_id,
        ]);
        
        $this->viewBuilder()->setTemplate('new_message_notification');
    }
    
    /**
     * Build email to notify customer of a new message from admin
     *
     * @param \App\Model\Entity\WritingServiceRequest $request The writing service request
     * @param string $message The message content
     * @param string $adminName Admin name who sent the message
     * @return void
     */
    public function customerMessageNotification(WritingServiceRequest $request, string $message, string $adminName): void
    {
        // Make sure we have access to the user
        if (empty($request->user)) {
            throw new \InvalidArgumentException('User information is required for customer message notification email');
        }
        
        $customerName = $request->user->first_name . ' ' . $request->user->last_name;
        
        $this->configureCommon(
            $request->user->email,
            $customerName,
            'New Message from Diana Bonvini: ' . $request->service_title
        );
        
        $this->setViewVars([
            'request' => $request,
            'adminName' => $adminName,
            'customerName' => $customerName,
            'messageContent' => $message,
            'messageDate' => date('F j, Y \a\t g:i A'),
            'requestId' => $request->writing_service_request_id,
        ]);
        
        $this->viewBuilder()->setTemplate('customer_message_notification');
    }
    
    /**
     * Send email asynchronously by not waiting for SMTP response
     * 
     * @return bool
     */
    public function deliverAsync(): bool
    {
        try {
            // Set a reasonable timeout for SMTP connection
            $transport = $this->getTransport();
            if ($transport instanceof SmtpTransport) {
                // Use a slightly longer timeout for reliability while still being quick
                $transport->setConfig([
                    'timeout' => 5,
                    'tls' => true,
                    'context' => [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ]
                ]);
            }
            
            // Start the sending process
            return (bool)$this->deliver();
        } catch (\Exception $e) {
            // Log the error but don't break the application flow
            error_log('Email delivery error: ' . $e->getMessage());
            return false;
        }
    }
} 