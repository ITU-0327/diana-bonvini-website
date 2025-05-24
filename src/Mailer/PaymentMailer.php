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

    /**
     * Send a coaching payment request notification to the client
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param \App\Model\Entity\CoachingServicePayment $payment The payment entity
     * @param float $amount The payment amount
     * @return void
     */
    public function coachingPaymentRequest($coachingServiceRequest, $payment, float $amount)
    {
        $formattedAmount = number_format($amount, 2);
        
        $this->setTo($coachingServiceRequest->user->email)
            ->setSubject('Payment Request for Coaching Service #' . $coachingServiceRequest->coaching_service_request_id)
            ->setEmailFormat('both')
            ->setViewVars([
                'client_name' => $coachingServiceRequest->user->full_name,
                'coaching_service_request' => $coachingServiceRequest,
                'payment' => $payment,
                'amount' => $formattedAmount
            ])
            ->viewBuilder()
                ->setTemplate('coaching_payment_request');
    }
    
    /**
     * Send a notification to admin when a client sends a new message in a coaching request
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param string $message The message text
     * @param string $adminEmail The admin's email address
     * @param string $adminName The admin's name
     * @return void
     */
    public function newCoachingMessageNotification($coachingServiceRequest, $message, $adminEmail, $adminName)
    {
        $this->setTo($adminEmail)
            ->setSubject('New Message for Coaching Request #' . $coachingServiceRequest->coaching_service_request_id)
            ->setEmailFormat('both')
            ->setViewVars([
                'admin_name' => $adminName,
                'client_name' => $coachingServiceRequest->user->full_name,
                'coaching_service_request' => $coachingServiceRequest,
                'message' => $message
            ])
            ->viewBuilder()
                ->setTemplate('coaching_new_message_admin');
    }
    
    /**
     * Send a notification to the client when the admin sends a message
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param string $message The message text
     * @param string $adminName The admin's name
     * @return void
     */
    public function customerCoachingMessageNotification($coachingServiceRequest, $message, $adminName)
    {
        $this->setTo($coachingServiceRequest->user->email)
            ->setSubject('New Message for Your Coaching Request #' . $coachingServiceRequest->coaching_service_request_id)
            ->setEmailFormat('both')
            ->setViewVars([
                'client_name' => $coachingServiceRequest->user->full_name,
                'admin_name' => $adminName,
                'coaching_service_request' => $coachingServiceRequest,
                'message' => $message
            ])
            ->viewBuilder()
                ->setTemplate('coaching_new_message_client');
    }
    
    /**
     * Send a notification to the client when admin uploads a document
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param string $documentName The name of the uploaded document
     * @return void
     */
    public function customerCoachingDocumentNotification($coachingServiceRequest, $documentName)
    {
        $this->setTo($coachingServiceRequest->user->email)
            ->setSubject('New Document for Your Coaching Request #' . $coachingServiceRequest->coaching_service_request_id)
            ->setEmailFormat('both')
            ->setViewVars([
                'client_name' => $coachingServiceRequest->user->full_name,
                'coaching_service_request' => $coachingServiceRequest,
                'document_name' => $documentName
            ])
            ->viewBuilder()
                ->setTemplate('coaching_new_document');
    }
    
    /**
     * Send a notification to the client when the status of their coaching request is updated
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param string $oldStatus The previous status
     * @param string $newStatus The new status
     * @return void
     */
    public function coachingStatusUpdateNotification($coachingServiceRequest, $oldStatus, $newStatus)
    {
        $formattedOldStatus = ucfirst(str_replace('_', ' ', $oldStatus));
        $formattedNewStatus = ucfirst(str_replace('_', ' ', $newStatus));
        
        $this->setTo($coachingServiceRequest->user->email)
            ->setSubject('Status Update for Your Coaching Request #' . $coachingServiceRequest->coaching_service_request_id)
            ->setEmailFormat('both')
            ->setViewVars([
                'client_name' => $coachingServiceRequest->user->full_name,
                'coaching_service_request' => $coachingServiceRequest,
                'old_status' => $formattedOldStatus,
                'new_status' => $formattedNewStatus
            ])
            ->viewBuilder()
                ->setTemplate('coaching_status_update');
    }

    /**
     * Send a payment confirmation notification to the client for coaching services
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param \App\Model\Entity\CoachingServicePayment $payment The payment entity
     * @return void
     */
    public function sendCoachingPaymentConfirmation($coachingServiceRequest, $payment)
    {
        $formattedAmount = number_format((float)$payment->amount, 2);
        
        $this->setTo($coachingServiceRequest->user->email)
            ->setSubject('Payment Confirmed for Coaching Service #' . $coachingServiceRequest->coaching_service_request_id)
            ->setEmailFormat('both')
            ->setViewVars([
                'client_name' => $coachingServiceRequest->user->full_name,
                'coaching_service_request' => $coachingServiceRequest,
                'payment' => $payment,
                'amount' => $formattedAmount,
                'transaction_id' => $payment->transaction_id ?? 'N/A'
            ])
            ->viewBuilder()
                ->setTemplate('coaching_payment_confirmation');
    }
    
    /**
     * Send a notification to the client when admin sends time slots
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param string $timeSlots The formatted time slots text
     * @param string $adminName The admin's name
     * @return void
     */
    public function customerCoachingTimeSlotsNotification($coachingServiceRequest, $timeSlots, $adminName)
    {
        $this->setTo($coachingServiceRequest->user->email)
            ->setSubject('Time Slots Available for Your Coaching Request #' . $coachingServiceRequest->coaching_service_request_id)
            ->setEmailFormat('both')
            ->setViewVars([
                'client_name' => $coachingServiceRequest->user->full_name,
                'admin_name' => $adminName,
                'coaching_service_request' => $coachingServiceRequest,
                'time_slots' => $timeSlots
            ])
            ->viewBuilder()
                ->setTemplate('coaching_time_slots');
    }
    
    /**
     * Send a notification to admin when a coaching payment is received
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param \App\Model\Entity\CoachingServicePayment $payment The payment entity
     * @param string $adminEmail Admin email address
     * @param string $adminName Admin name
     * @return void
     */
    public function adminCoachingPaymentNotification($coachingServiceRequest, $payment, string $adminEmail, string $adminName)
    {
        $formattedAmount = number_format((float)$payment->amount, 2);
        
        $this->setTo($adminEmail)
            ->setSubject('💰 New Coaching Payment Received #' . $coachingServiceRequest->coaching_service_request_id)
            ->setEmailFormat('both')
            ->setViewVars([
                'admin_name' => $adminName,
                'client_name' => $coachingServiceRequest->user->full_name,
                'client_email' => $coachingServiceRequest->user->email,
                'coaching_service_request' => $coachingServiceRequest,
                'payment' => $payment,
                'amount' => $formattedAmount,
                'transaction_id' => $payment->transaction_id ?? 'N/A'
            ])
            ->viewBuilder()
                ->setTemplate('admin_coaching_payment_notification');
    }
    
    /**
     * Send a notification to admin when a new coaching service request is created
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param string $adminEmail Admin email address
     * @param string $adminName Admin name
     * @return void
     */
    public function newCoachingRequestNotification($coachingServiceRequest, string $adminEmail, string $adminName)
    {
        // Make sure we have access to the user
        if (empty($coachingServiceRequest->user)) {
            throw new \InvalidArgumentException('User information is required for new coaching request notification email');
        }
        
        $this->setTo($adminEmail)
            ->setSubject('🎯 New Coaching Service Request: ' . ucwords(str_replace('_', ' ', $coachingServiceRequest->service_type)))
            ->setEmailFormat('both')
            ->setViewVars([
                'coaching_service_request' => $coachingServiceRequest,
                'admin_name' => $adminName,
                'client_name' => $coachingServiceRequest->user->full_name,
                'client_email' => $coachingServiceRequest->user->email,
            ])
            ->viewBuilder()
                ->setTemplate('coaching_new_request_notification');
    }
} 