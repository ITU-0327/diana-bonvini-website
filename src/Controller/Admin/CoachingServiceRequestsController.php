<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;
use Cake\Event\EventInterface;
use Cake\Core\Configure;
use Cake\Utility\Text;
use Psr\Http\Message\UploadedFileInterface;

/**
 * CoachingServiceRequests Controller
 *
 * @property \App\Model\Table\CoachingServiceRequestsTable $CoachingServiceRequests
 */
class CoachingServiceRequestsController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Set admin layout for all views in this controller
        $this->viewBuilder()->setLayout('admin');
        
        // Set the admin template path
        $this->viewBuilder()->setTemplatePath('Admin/CoachingServiceRequests');
        
        $this->Authentication->addUnauthenticatedActions([]);
        
        // Configure FormProtection component
        if ($this->components()->has('FormProtection')) {
            $this->FormProtection->setConfig([
                'unlockedFields' => [
                    'message_text', 
                    'time_slots',
                    'coaching_service_request_id',
                    'request_status',
                    '_csrfToken',
                    'amount',
                    'description',
                    'payment_id'
                ],
                'unlockedActions' => [
                    'getAvailableTimeSlots',
                    'sendTimeSlots',
                    'update_status',
                    'markAsPaid'
                ]
            ]);
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to access admin area.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $query = $this->CoachingServiceRequests->find()
            ->contain(['Users', 'CoachingServicePayments' => function ($q) {
                return $q->where(['status' => 'paid']);
            }])
            ->where(['CoachingServiceRequests.is_deleted' => false]);

        // Process filter parameters
        $keyword = $this->request->getQuery('q');
        $status = $this->request->getQuery('status');
        $serviceType = $this->request->getQuery('service_type');
        $dateRange = $this->request->getQuery('date_range');

        // Apply keyword search filter
        if (!empty($keyword)) {
            $query->where([
                'OR' => [
                    'CoachingServiceRequests.service_title LIKE' => '%' . $keyword . '%',
                    'CoachingServiceRequests.service_type LIKE' => '%' . $keyword . '%',
                    'Users.first_name LIKE' => '%' . $keyword . '%',
                    'Users.last_name LIKE' => '%' . $keyword . '%',
                ],
            ]);
        }

        // Apply service type filter
        if (!empty($serviceType)) {
            $query->where(['CoachingServiceRequests.service_type' => $serviceType]);
        }

        // Apply status filter
        if (!empty($status)) {
            $query->where(['CoachingServiceRequests.request_status' => $status]);
        }

        // Apply date range filter
        if (!empty($dateRange)) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                try {
                    $startDate = new \DateTime($dates[0]);
                    $endDate = new \DateTime($dates[1]);
                    // Set end date to end of day
                    $endDate->setTime(23, 59, 59);
                    
                    $query->where([
                        'CoachingServiceRequests.created_at >=' => $startDate,
                        'CoachingServiceRequests.created_at <=' => $endDate,
                    ]);
                } catch (\Exception $e) {
                    // Invalid date format, ignore this filter
                    $this->log('Invalid date range format: ' . $dateRange, 'warning');
                }
            }
        }

        $this->paginate = [
            'order' => ['CoachingServiceRequests.created_at' => 'DESC'],
        ];
        
        $coachingServiceRequests = $this->paginate($query);

        // Calculate extra data for the view
        $totalUnreadCount = $this->CoachingServiceRequests->CoachingRequestMessages->find()
            ->where([
                'is_read' => false,
                'user_id !=' => $user->user_id // Only count messages not from current admin
            ])
            ->count();

        $this->set(compact('coachingServiceRequests', 'totalUnreadCount'));
    }

    /**
     * View method
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();

        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, [
            'contain' => [
                'Users',
                'CoachingRequestMessages' => function ($q) {
                    return $q->contain(['Users'])
                        ->order(['CoachingRequestMessages.created_at' => 'ASC'])
                        ->where(['CoachingRequestMessages.is_deleted' => false]);
                },
                'CoachingServicePayments' => function ($q) {
                    return $q->order(['CoachingServicePayments.created_at' => 'DESC'])
                        ->where(['CoachingServicePayments.is_deleted' => false]);
                },
            ],
        ]);
        
        // Fetch coaching request documents
        $coachingRequestDocumentsTable = $this->fetchTable('CoachingRequestDocuments');
        $coachingRequestDocuments = $coachingRequestDocumentsTable->find()
            ->where([
                'coaching_service_request_id' => $id,
                'is_deleted' => false,
            ])
            ->order(['created_at' => 'DESC'])
            ->toArray();

        // Mark client messages as read when admin views them
        $this->markMessagesAsRead($coachingServiceRequest, $admin->user_id);

        $this->set(compact('coachingServiceRequest', 'coachingRequestDocuments'));
    }

    /**
     * Marks messages as read for the given user
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param string $userId The ID of the current user (admin)
     * @return void
     */
    private function markMessagesAsRead($coachingServiceRequest, string $userId): void
    {
        if (empty($coachingServiceRequest->coaching_request_messages)) {
            return;
        }

        $coachingRequestMessagesTable = $this->fetchTable('CoachingRequestMessages');
        $updatedCount = 0;

        foreach ($coachingServiceRequest->coaching_request_messages as $message) {
            // Only mark messages from other users (client) as read
            if ($message->user_id !== $userId && !$message->is_read) {
                $message->is_read = true;
                $coachingRequestMessagesTable->save($message);
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            $this->log("Admin marked $updatedCount client messages as read", 'info');
        }
    }

    /**
     * Send payment request to client
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null
     */
    public function sendPaymentRequest(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        $this->log('sendPaymentRequest started for coaching service request ID: ' . $id, 'debug');

        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, [
            'contain' => ['Users'],
        ]);

        $data = $this->request->getData();
        $this->log('Raw payment request data: ' . json_encode($data), 'debug');
        
        // First try to get the pre-cleaned amount if provided by enhanced JS
        $amount = $data['cleaned_amount'] ?? $data['amount'] ?? null;
        $description = $data['description'] ?? 'Coaching service fee';

        // Ensure amount is properly cleaned of any currency symbols and commas
        if (!empty($amount)) {
            $this->log('Original amount before cleaning: ' . $amount, 'debug');
            
            // Convert to string if it's not already
            $amount = (string)$amount;
            
            // If this doesn't look like a purely numeric value, clean it
            if (!is_numeric($amount)) {
                // Remove currency symbols, commas, and any other non-numeric chars except decimal point
                $amount = preg_replace('/[^0-9.]/', '', $amount);
                $this->log('Cleaned amount after regex: ' . $amount, 'debug');
            }
            
            // Ensure it's treated as a float
            $amount = (float)$amount;
        }

        // Only validate that amount is numeric and positive
        if (empty($amount) || !is_numeric($amount) || (float)$amount <= 0) {
            $this->log('Amount validation failed. Amount: ' . ($amount ?? 'null'), 'debug');
            $this->Flash->error(__('Please provide a valid payment amount.'));

            return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
        }
        
        // Update the amount in the data array to use the cleaned value
        $data['amount'] = $amount;
        $this->log('Final amount after validation: ' . $amount, 'debug');

        // Create a unique payment ID
        $paymentId = uniqid('csr_payment_');
        $this->log('Generated payment ID: ' . $paymentId, 'debug');

        // Format the amount for display
        $formattedAmount = '$' . number_format((float)$amount, 2);

        // Default to session-only tracking
        $dbPaymentId = 'pending';
        $useDatabase = false;

        // Try to create a database record, but don't require it
        try {
            $coachingServicePaymentsTable = $this->fetchTable('CoachingServicePayments');
            $useDatabase = true;
            
            // Create a new payment record
            $newPayment = $coachingServicePaymentsTable->newEntity([
                'coaching_service_request_id' => $id,
                'amount' => $amount,
                'transaction_id' => null,
                'payment_date' => new \DateTime(),
                'payment_method' => 'pending',
                'status' => 'pending',
                'is_deleted' => false,
            ]);
            
            $this->log('Attempting to save payment record to database', 'debug');
            if ($coachingServicePaymentsTable->save($newPayment)) {
                $dbPaymentId = $newPayment->coaching_service_payment_id;
                $this->log('Payment record saved successfully with ID: ' . $dbPaymentId, 'debug');
                
                // Send email notification to customer
                try {
                    // We don't need to fetch Users table since we already have user info
                    // Make sure we have fresh data with user information
                    $coachingServiceRequest = $this->CoachingServiceRequests->get($id, [
                        'contain' => ['Users'],
                    ]);
                    
                    // Send payment request email
                    $mailer = new \App\Mailer\PaymentMailer('default');
                    $this->log('Attempting to send coaching payment request email', 'debug');
                    $mailer->coachingPaymentRequest($coachingServiceRequest, $newPayment, (float)$amount);
                    $result = $mailer->deliverAsync();
                    
                    if (!$result) {
                        $this->log('Payment request email failed to send but continuing', 'warning');
                    } else {
                        $this->log('Payment request email sent successfully', 'debug');
                    }
                } catch (\Exception $e) {
                    // Log but don't prevent the process from continuing
                    $this->log('Failed to send payment request email: ' . $e->getMessage(), 'error');
                }
            } else {
                $this->log('Failed to save payment record. Validation errors: ' . json_encode($newPayment->getErrors()), 'error');
            }
        } catch (\Exception $e) {
            $this->log('Error creating payment record: ' . $e->getMessage(), 'error');
            $dbPaymentId = 'failed';
        }

        // Combine session paymentId and db paymentId for reference
        $combinedPaymentId = $paymentId;
        if ($dbPaymentId !== 'pending' && $dbPaymentId !== 'failed') {
            $combinedPaymentId .= '|' . $dbPaymentId;
        }
        $this->log('Combined payment ID: ' . $combinedPaymentId, 'debug');

        // Create a message for the chat
        $messageText = "**Payment Request**\n\n";
        $messageText .= "**Service:** " . $coachingServiceRequest->service_title . "\n";
        $messageText .= "**Amount:** " . $formattedAmount . "\n\n";
        $messageText .= "Please click the button below to complete your payment. Once payment is processed, you'll receive a confirmation and we'll begin work on your coaching request.\n\n";
        $messageText .= '[PAYMENT_BUTTON]' . $combinedPaymentId . '[/PAYMENT_BUTTON]';
        $this->log('Created payment request message with PAYMENT_BUTTON tag', 'debug');

        // Store payment details in session (primary source of truth)
        $this->request->getSession()->write("CsrPayments.$paymentId", [
            'amount' => $amount,
            'description' => $description,
            'coaching_service_request_id' => $id,
            'created' => time(),
            'status' => 'pending',
            'db_payment_id' => $dbPaymentId,
        ]);

        $this->log('Payment data stored in session: ' . json_encode([
                'sessionKey' => "CsrPayments.$paymentId",
                'amount' => $amount,
                'description' => $description,
                'id' => $id,
            ]), 'debug');

        // Save the message
        $messageData = [
            'coaching_request_messages' => [
                [
                    'user_id' => $admin->user_id,
                    'message' => $messageText,
                    'is_read' => false,
                    'coaching_service_request_id' => $coachingServiceRequest->coaching_service_request_id,
                ],
            ],
        ];

        $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
            $coachingServiceRequest,
            $messageData,
        );
        
        $this->log('Attempting to save message to coaching service request', 'debug');
        if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
            $this->log('Message saved successfully', 'debug');
            $this->Flash->success(__('Payment request has been sent to the client.'));
        } else {
            $this->log('Failed to save message. Validation errors: ' . json_encode($coachingServiceRequest->getErrors()), 'error');
            $this->Flash->error(__('Failed to send payment request. Please try again.'));
        }

        $this->log('sendPaymentRequest completed', 'debug');
        return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
    }

    /**
     * Send message to client
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null
     */
    public function sendMessage(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        
        if (!$admin || $admin->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to perform this action.'));
            return $this->redirect(['controller' => 'Admin', 'action' => 'dashboard']);
        }

        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, [
            'contain' => ['Users']
        ]);

        $data = $this->request->getData();
        $messageText = $data['message_text'] ?? '';

        if (empty(trim($messageText))) {
            $this->Flash->error(__('Please enter a message.'));
            return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
        }

        // Prepare message data
        $messageData = [
            'coaching_request_messages' => [
                [
                    'user_id' => $admin->user_id,
                    'message' => $messageText,
                    'is_read' => false, // Initially not read by the client
                    'coaching_service_request_id' => $coachingServiceRequest->coaching_service_request_id,
                ],
            ],
        ];

        // Add the message to the request
        $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
            $coachingServiceRequest,
            $messageData
        );

        if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
            $this->Flash->success(__('Message sent successfully.'));
            
            // If the request status is pending, update it to in_progress
            if ($coachingServiceRequest->request_status === 'pending') {
                $coachingServiceRequest->request_status = 'in_progress';
                $this->CoachingServiceRequests->save($coachingServiceRequest);
            }
            
            // Send email notification to customer
            try {
                // Get a fresh copy of the request with user data to ensure we have all necessary information
                $requestWithUser = $this->CoachingServiceRequests->get($id, [
                    'contain' => ['Users'],
                ]);
                
                if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                    // Admin name (use the actual admin's name or a fixed name)
                    $adminName = 'Diana Bonvini';
                    
                    // Send customer notification
                    $mailer = new \App\Mailer\PaymentMailer('default');
                    $mailer->customerCoachingMessageNotification(
                        $requestWithUser,
                        $messageText,
                        $adminName
                    );
                    $result = $mailer->deliverAsync();
                    
                    if (!$result) {
                        $this->log('Customer notification email failed to send', 'warning');
                    }
                }
            } catch (\Exception $e) {
                $this->log('Failed to send customer notification: ' . $e->getMessage(), 'error');
            }
            
        } else {
            $this->Flash->error(__('Failed to send message. Please try again.'));
        }

        return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
    }

    /**
     * Upload document
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null
     */
    public function uploadDocument(?string $id = null)
    {
        $this->request->allowMethod(['post']);
        
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        
        if (!$admin || $admin->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to perform this action.'));
            return $this->redirect(['controller' => 'Admin', 'action' => 'dashboard']);
        }
        
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id);
        
        // Check if file was uploaded
        $file = $this->request->getUploadedFile('document');
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            $this->Flash->error(__('No file was uploaded or there was an error with the upload.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        // Validate file type
        $mimeType = $file->getClientMediaType();
        $originalFilename = $file->getClientFilename();
        $fileSize = $file->getSize();
        
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        
        if (!in_array($mimeType, $allowedTypes)) {
            $this->Flash->error(__('Invalid file type. Please upload a PDF, JPG, DOC, DOCX, or TXT file.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        // Generate unique filename
        $uniqueFilename = Text::uuid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $originalFilename);
        
        // Define upload directory
        $uploadDir = WWW_ROOT . 'uploads' . DS . 'coaching_docs';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        try {
            // Move uploaded file
            $file->moveTo($uploadDir . DS . $uniqueFilename);
            
            // Create record in CoachingRequestDocuments table
            $coachingRequestDocumentsTable = $this->fetchTable('CoachingRequestDocuments');
            
            $documentEntity = $coachingRequestDocumentsTable->newEntity([
                'coaching_service_request_id' => $id,
                'user_id' => $admin->user_id,
                'document_path' => 'uploads/coaching_docs/' . $uniqueFilename,
                'document_name' => $originalFilename,
                'file_type' => $mimeType,
                'file_size' => $fileSize,
                'uploaded_by' => 'admin',
                'is_deleted' => false,
            ]);
            
            if ($coachingRequestDocumentsTable->save($documentEntity)) {
                $this->Flash->success(__('Document uploaded successfully.'));
                
                // Create a message to notify the client
                $messageData = [
                    'coaching_request_messages' => [
                        [
                            'user_id' => $admin->user_id,
                            'message' => "**Document Uploaded**\n\nI've uploaded a document for your coaching request: **" . $originalFilename . "**\n\nYou can view or download this file from the Documents section.",
                            'is_read' => false,
                            'coaching_service_request_id' => $id,
                        ],
                    ],
                ];
                
                $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
                    $coachingServiceRequest,
                    $messageData
                );
                
                $this->CoachingServiceRequests->save($coachingServiceRequest);
                
                // Notify the client via email
                try {
                    $requestWithUser = $this->CoachingServiceRequests->get($id, [
                        'contain' => ['Users'],
                    ]);
                    
                    if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                        // Send customer notification
                        $mailer = new \App\Mailer\PaymentMailer('default');
                        $mailer->customerCoachingDocumentNotification(
                            $requestWithUser,
                            $originalFilename
                        );
                        $mailer->deliverAsync();
                    }
                } catch (\Exception $e) {
                    $this->log('Failed to send document notification email: ' . $e->getMessage(), 'error');
                }
                
            } else {
                $this->Flash->error(__('The document was uploaded but could not be saved in the database.'));
            }
            
        } catch (\Exception $e) {
            $this->Flash->error(__('Error uploading document: {0}', $e->getMessage()));
        }
        
        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Update Request Status
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null Redirects on success
     */
    public function updateStatus(?string $id = null)
    {
        $this->request->allowMethod(['post']);
        
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        
        if (!$admin || $admin->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to perform this action.'));
            return $this->redirect(['controller' => 'Admin', 'action' => 'dashboard']);
        }
        
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, [
            'contain' => ['Users'],
        ]);
        
        $data = $this->request->getData();
        $newStatus = $data['status'] ?? null;
        
        $validStatuses = ['pending', 'in_progress', 'completed', 'canceled', 'cancelled'];
        
        if (empty($newStatus) || !in_array($newStatus, $validStatuses)) {
            $this->Flash->error(__('Invalid status. Please select a valid status.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        $oldStatus = $coachingServiceRequest->request_status;
        $coachingServiceRequest->request_status = $newStatus;
        
        if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
            $this->Flash->success(__('The request status has been updated.'));
            
            // Add a message to the conversation thread about the status change
            $statusMessage = "**Status Update**\n\n";
            $statusMessage .= "The status of this coaching request has been changed from **" . ucfirst(str_replace('_', ' ', $oldStatus)) . "** to **" . ucfirst(str_replace('_', ' ', $newStatus)) . "**.";
            
            if ($newStatus === 'completed') {
                $statusMessage .= "\n\nThank you for using our coaching services! If you have any feedback or questions, please let us know.";
            } elseif ($newStatus === 'canceled' || $newStatus === 'cancelled') {
                $statusMessage .= "\n\nIf you have any questions about this cancellation, please contact us.";
            }
            
            $messageData = [
                'coaching_request_messages' => [
                    [
                        'user_id' => $admin->user_id,
                        'message' => $statusMessage,
                        'is_read' => false,
                        'coaching_service_request_id' => $id,
                    ],
                ],
            ];
            
            $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
                $coachingServiceRequest,
                $messageData
            );
            
            $this->CoachingServiceRequests->save($coachingServiceRequest);
            
            // Notify the client via email about the status change
            try {
                $requestWithUser = $this->CoachingServiceRequests->get($id, [
                    'contain' => ['Users'],
                ]);
                
                if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                    // Send status update notification
                    $mailer = new \App\Mailer\PaymentMailer('default');
                    $mailer->coachingStatusUpdateNotification(
                        $requestWithUser,
                        $oldStatus,
                        $newStatus
                    );
                    $mailer->deliverAsync();
                }
            } catch (\Exception $e) {
                $this->log('Failed to send status update notification: ' . $e->getMessage(), 'error');
            }
            
        } else {
            $this->Flash->error(__('The request status could not be updated. Please try again.'));
        }
        
        return $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Get available time slots for a date (AJAX)
     *
     * @return \Cake\Http\Response|null
     */
    public function getAvailableTimeSlots()
    {
        $this->request->allowMethod(['get']);
        
        if (!$this->request->is('ajax') && !$this->request->getHeader('Accept') && !in_array('application/json', $this->request->getHeader('Accept'))) {
            $this->request = $this->request->withHeader('Accept', 'application/json');
        }
        
        // Configure the response
        $this->viewBuilder()->setClassName('Json');
        $this->response = $this->response->withType('application/json');
        
        // Get the date from query params
        $dateString = $this->request->getQuery('date');
        
        if (empty($dateString)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Date parameter is required',
            ]));
        }
        
        try {
            // Create a list of time slots for the date
            $date = new \DateTime($dateString);
            $timeSlots = $this->createBasicTimeSlots($dateString);
            
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'date' => $dateString,
                'timeSlots' => $timeSlots,
            ]));
        } catch (\Exception $e) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Error processing date: ' . $e->getMessage(),
            ]));
        }
    }
    
    /**
     * Create a list of basic time slots for a given date
     *
     * @param string $dateString The date string in Y-m-d format
     * @return array List of time slots
     */
    private function createBasicTimeSlots(string $dateString): array
    {
        $slots = [];
        
        // Generate time slots from 9 AM to 5 PM with 30-minute intervals
        $startHour = 9; // 9 AM
        $endHour = 17;  // 5 PM
        $interval = 30;  // 30 minutes
        
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $interval) {
                $startTime = sprintf('%02d:%02d', $hour, $minute);
                $endHour2 = $hour;
                $endMinute = $minute + $interval;
                
                if ($endMinute >= 60) {
                    $endHour2++;
                    $endMinute -= 60;
                }
                
                $endTime = sprintf('%02d:%02d', $endHour2, $endMinute);
                
                // Create a slot for this time period
                if ($endHour2 <= $endHour) {
                    $formattedStart = date('g:i A', strtotime($startTime));
                    $formattedEnd = date('g:i A', strtotime($endTime));
                    
                    $slots[] = [
                        'date' => $dateString,
                        'start' => $startTime,
                        'end' => $endTime,
                        'formatted' => "{$formattedStart} - {$formattedEnd}",
                    ];
                }
            }
        }
        
        return $slots;
    }

    /**
     * Send time slots to client
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null
     */
    public function sendTimeSlots(?string $id = null)
    {
        $this->request->allowMethod(['post']);
        
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        
        if (!$admin || $admin->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to perform this action.'));
            return $this->redirect(['controller' => 'Admin', 'action' => 'dashboard']);
        }
        
        // Get ID from route parameter or POST data
        $postData = $this->request->getData();
        if (empty($id) && !empty($postData['coaching_service_request_id'])) {
            $id = $postData['coaching_service_request_id'];
        }
        
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, [
            'contain' => ['Users'],
        ]);
        
        $data = $this->request->getData();
        $this->log('SendTimeSlots called with ID: ' . $id, 'debug');
        $this->log('POST data: ' . json_encode($data), 'debug');
        
        // Get time slots and message from POST data
        $timeSlots = $data['time_slots'] ?? '';
        $messageText = $data['message_text'] ?? '';
        
        $this->log('Time slots received: ' . $timeSlots, 'debug');
        $this->log('Message text received: ' . $messageText, 'debug');
        
        // Validate required fields
        if (empty($messageText)) {
            $this->Flash->error(__('Please enter a message.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        // Validate time slots
        if (empty($timeSlots)) {
            $this->Flash->error(__('No time slots selected.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        // Decode time slots JSON
        $decodedTimeSlots = json_decode($timeSlots, true);
        
        if (empty($decodedTimeSlots) || !is_array($decodedTimeSlots)) {
            $this->log('Failed to decode time slots JSON: ' . $timeSlots, 'error');
            
            // Create some basic time slots as fallback
            $date = new \DateTime();
            $decodedTimeSlots = $this->createBasicTimeSlots($date->format('Y-m-d'));
            $this->log('Created fallback time slots: ' . json_encode($decodedTimeSlots), 'debug');
        }
        
        // Format time slots for display in the message
        $formattedSlots = [];
        foreach ($decodedTimeSlots as $slot) {
            if (isset($slot['date']) && isset($slot['formatted'])) {
                try {
                    $formattedDate = new \DateTime($slot['date']);
                    $dayName = $formattedDate->format('l');
                    $formattedSlots[] = "- {$dayName}, {$formattedDate->format('F j, Y')}: {$slot['formatted']}";
                } catch (\Exception $e) {
                    $this->log('Error formatting date: ' . $e->getMessage(), 'error');
                    // Add a basic formatted slot as fallback
                    $formattedSlots[] = "- {$slot['date']}: {$slot['formatted']}";
                }
            }
        }
        
        if (empty($formattedSlots)) {
            $this->Flash->error(__('Failed to format time slots.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        // Build the message with time slots
        $message = $messageText . "\n\n";
        $message .= "**Available Time Slots:**\n";
        $message .= implode("\n", $formattedSlots);
        
        $message .= "\n\nPlease select one of the time slots above for our coaching session by clicking the \"Accept\" button next to your preferred time.";
        
        // Add message to the conversation
        $messageData = [
            'coaching_request_messages' => [
                [
                    'user_id' => $admin->user_id,
                    'message' => $message,
                    'is_read' => false,
                    'coaching_service_request_id' => $id,
                ],
            ],
        ];
        
        $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
            $coachingServiceRequest,
            $messageData
        );
        
        if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
            $this->Flash->success(__('Time slots have been sent to the client.'));
            
            // Notify the client via email
            try {
                $requestWithUser = $this->CoachingServiceRequests->get($id, [
                    'contain' => ['Users'],
                ]);
                
                if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                    // Use the already formatted time slots for email
                    $timeSlotsText = implode("\n", $formattedSlots);
                    
                    // Send notification
                    $mailer = new \App\Mailer\PaymentMailer('default');
                    $mailer->customerCoachingTimeSlotsNotification(
                        $requestWithUser,
                        $timeSlotsText,
                        'Diana Bonvini'
                    );
                    $mailer->deliverAsync();
                }
            } catch (\Exception $e) {
                $this->log('Failed to send time slots notification: ' . $e->getMessage(), 'error');
            }
            
            // Update request status if it's pending
            if ($coachingServiceRequest->request_status === 'pending') {
                $coachingServiceRequest->request_status = 'in_progress';
                $this->CoachingServiceRequests->save($coachingServiceRequest);
            }
            
            // Store time slots in session for later use when client books
            $this->request->getSession()->write(
                "CoachingTimeSlots.{$id}", 
                [
                    'slots' => $decodedTimeSlots,
                    'expires' => time() + (7 * 24 * 60 * 60), // Expire after 7 days
                ]
            );
        } else {
            $this->Flash->error(__('Failed to send time slots. Please try again.'));
        }
        
        return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
    }
    
    /**
     * Mark payment as paid
     * 
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null
     */
    public function markAsPaid(?string $id = null)
    {
        $this->request->allowMethod(['post']);
        
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        
        if (!$admin || $admin->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to perform this action.'));
            return $this->redirect(['controller' => 'Admin', 'action' => 'dashboard']);
        }
        
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, [
            'contain' => ['Users'],
        ]);
        
        $data = $this->request->getData();
        $amount = $data['amount'] ?? 0;
        $paymentId = $data['payment_id'] ?? 'manual_' . uniqid();
        $description = $data['description'] ?? 'Payment received';
        
        if (empty($amount) || !is_numeric($amount) || (float)$amount <= 0) {
            $this->Flash->error(__('Please provide a valid payment amount.'));
            return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
        }
        
        // Create or update payment record
        $paymentTable = $this->fetchTable('CoachingServicePayments');
        
        // Create a new payment record
        $payment = $paymentTable->newEntity([
            'coaching_service_request_id' => $id,
            'payment_id' => $paymentId,
            'transaction_id' => 'manual_' . date('YmdHis'),
            'amount' => (float)$amount,
            'payment_date' => new \DateTime(),
            'payment_method' => 'manual',
            'status' => 'paid',
            'is_deleted' => false,
        ]);
        
        if ($paymentTable->save($payment)) {
            // Update request status if needed
            if ($coachingServiceRequest->request_status === 'pending') {
                $coachingServiceRequest->request_status = 'in_progress';
                $this->CoachingServiceRequests->save($coachingServiceRequest);
            }
            
            // Add confirmation message
            $confirmationMessage = "**Payment Confirmation**\n\n";
            $confirmationMessage .= "A payment of **$" . number_format((float)$amount, 2) . "** for your coaching service has been processed and marked as paid.\n\n";
            $confirmationMessage .= "Thank you for your payment. We can now proceed with your coaching service as discussed.";
            
            if (!empty($description)) {
                $confirmationMessage .= "\n\n**Details**: " . $description;
            }
            
            // Add [PAYMENT_CONFIRMATION] tag for special formatting
            $confirmationMessage = "[PAYMENT_CONFIRMATION]\n" . $confirmationMessage;
            
            $messageData = [
                'coaching_request_messages' => [
                    [
                        'user_id' => $admin->user_id,
                        'message' => $confirmationMessage,
                        'is_read' => false,
                        'coaching_service_request_id' => $id,
                    ],
                ],
            ];
            
            $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
                $coachingServiceRequest,
                $messageData
            );
            
            $this->CoachingServiceRequests->save($coachingServiceRequest);
            
            $this->Flash->success(__('Payment has been marked as paid and confirmation message sent.'));
            
            // Send email notification to client
            try {
                $requestWithUser = $this->CoachingServiceRequests->get($id, [
                    'contain' => ['Users'],
                ]);
                
                if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                    $mailer = new \App\Mailer\PaymentMailer('default');
                    $mailer->sendCoachingPaymentConfirmation($requestWithUser, $payment);
                    $mailer->deliverAsync();
                }
            } catch (\Exception $e) {
                $this->log('Failed to send payment confirmation email: ' . $e->getMessage(), 'error');
            }
        } else {
            $this->Flash->error(__('Failed to mark payment as paid. Please try again.'));
        }
        
        return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
    }
} 