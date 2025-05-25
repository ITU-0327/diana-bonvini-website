<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;
use Cake\Event\EventInterface;
use Cake\Core\Configure;
use Cake\Utility\Text;
use Psr\Http\Message\UploadedFileInterface;
use Exception;

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
                    'payment_id',
                    'status'
                ],
                'unlockedActions' => [
                    'getAvailableTimeSlots',
                    'sendTimeSlots',
                    'updateStatus',
                    'markAsPaid',
                    'sendPaymentRequest',
                    'sendMessage',
                    'uploadDocument'
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
            ->contain(['Users', 'CoachingServicePayments'])
            ->where(['CoachingServiceRequests.is_deleted' => false]);

        $this->paginate = [
            'order' => ['CoachingServiceRequests.created_at' => 'DESC'],
            'limit' => 25,
        ];
        
        $coachingServiceRequests = $this->paginate($query);

        // Calculate extra data for the view
        $totalUnreadCount = $this->CoachingServiceRequests->CoachingRequestMessages->find()
            ->where([
                'is_read' => false,
                'user_id !=' => $user->user_id // Only count messages not from current admin
            ])
            ->count();

        // Pass filter values to the view
        $filters = [
            'q' => '',
            'status' => '',
            'created_date' => ''
        ];

        $this->set(compact('coachingServiceRequests', 'totalUnreadCount', 'filters'));
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

        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: [
            'Users',
            'CoachingRequestMessages' => function ($q) {
                return $q->contain(['Users'])
                    ->orderBy(['CoachingRequestMessages.created_at' => 'ASC'])
                    ->where(['CoachingRequestMessages.is_deleted' => false]);
            },
            'CoachingServicePayments' => function ($q) {
                return $q->orderBy(['CoachingServicePayments.created_at' => 'DESC']);
            },
        ]);
        
        // Fetch coaching request documents
        $coachingRequestDocumentsTable = $this->fetchTable('CoachingRequestDocuments');
        $coachingRequestDocuments = $coachingRequestDocumentsTable->find()
            ->where([
                'coaching_service_request_id' => $id,
                'is_deleted' => false,
            ])
            ->orderBy(['created_at' => 'DESC'])
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
        $this->log('sendPaymentRequest called with id: ' . ($id ?? 'null'), 'debug');
        $this->request->allowMethod(['post']);

        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id);

        $data = $this->request->getData();
        $this->log('Request data received: ' . json_encode($data), 'debug');
        
        $amount = $data['amount'] ?? null;
        $description = $data['description'] ?? 'Coaching service fee';

        // Validate amount
        if (empty($amount) || !is_numeric($amount) || (float)$amount <= 0) {
            $this->log('Invalid amount provided: ' . ($amount ?? 'null'), 'error');
            $this->Flash->error(__('Please provide a valid payment amount.'));
            return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
        }

        $amount = (float)$amount;
        $formattedAmount = '$' . number_format($amount, 2);

        try {
            // Create payment record in database
            $coachingServicePaymentsTable = $this->fetchTable('CoachingServicePayments');
            $paymentEntity = $coachingServicePaymentsTable->newEntity([
                'coaching_service_request_id' => $id,
                'amount' => $amount,
                'transaction_id' => null, // Will be filled when payment is completed
                'payment_method' => 'stripe',
                'status' => 'pending',
                'is_deleted' => false,
            ]);

            if (!$coachingServicePaymentsTable->save($paymentEntity)) {
                $errors = $paymentEntity->getErrors();
                $this->log('Failed to create coaching payment record. Errors: ' . json_encode($errors), 'error');
                $this->log('Payment entity data: ' . json_encode($paymentEntity->toArray()), 'error');
                $this->Flash->error(__('Failed to create payment request. Please try again.'));
                return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
            }

            // Create payment ID for the payment button
            $paymentId = $paymentEntity->coaching_service_payment_id;

            // Create message with payment button
            $messageText = "**Payment Request**\n\n";
            $messageText .= "**Service:** " . $description . "\n";
            $messageText .= "**Amount:** " . $formattedAmount . "\n\n";
            $messageText .= "Please click the button below to complete your payment. Once payment is processed, you'll receive a confirmation and we'll continue with your request.\n\n";
            $messageText .= '[PAYMENT_BUTTON]' . $paymentId . '[/PAYMENT_BUTTON]';

            // Save the message
            $messageData = [
                'coaching_request_messages' => [
                    [
                        'user_id' => $admin->user_id,
                        'message' => $messageText,
                        'is_read' => false,
                        'is_deleted' => false, // Ensure is_deleted is set
                        'coaching_service_request_id' => $coachingServiceRequest->coaching_service_request_id,
                    ],
                ],
            ];

            $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
                $coachingServiceRequest,
                $messageData,
            );

            if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
                $this->Flash->success(__('Payment request has been sent to the client.'));
                
                // Send email notification to customer
                try {
                    // Get the coaching service request with user information for email
                    $requestWithUser = $this->CoachingServiceRequests->get($id, contain: ['Users']);
                    
                    // Create and send payment request email
                    $mailer = new \App\Mailer\PaymentMailer('default');
                    $mailer->coachingPaymentRequest($requestWithUser, $paymentEntity, $amount);
                    $result = $mailer->deliverAsync();
                    
                    if ($result) {
                        $this->log('Coaching payment request email sent successfully to ' . $requestWithUser->user->email, 'info');
                    } else {
                        $this->log('Coaching payment request email failed to send to ' . $requestWithUser->user->email, 'warning');
                    }
                } catch (Exception $emailException) {
                    // Log email error but don't fail the payment request creation
                    $this->log('Error sending coaching payment request email: ' . $emailException->getMessage(), 'error');
                }
            } else {
                $messageErrors = $coachingServiceRequest->getErrors();
                $this->log('Failed to save coaching payment request message. Errors: ' . json_encode($messageErrors), 'error');
                $this->Flash->error(__('Failed to send payment request message. Please try again.'));
            }

        } catch (Exception $e) {
            $this->log('Error creating coaching payment request: ' . $e->getMessage(), 'error');
            $this->log('Stack trace: ' . $e->getTraceAsString(), 'error');
            $this->Flash->error(__('An error occurred while creating the payment request. Please try again.'));
        }

        $this->log('sendPaymentRequest completed successfully for id: ' . $id, 'debug');
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

        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: ['Users']);

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
                $requestWithUser = $this->CoachingServiceRequests->get($id, contain: ['Users']);
                
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
        
        // Validate file type - Only PDF and Word documents allowed
        $mimeType = $file->getClientMediaType();
        $originalFilename = $file->getClientFilename();
        $fileSize = $file->getSize();
        
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!in_array($mimeType, $allowedTypes)) {
            $this->Flash->error(__('Invalid file type. Please upload a PDF or Word document only.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        // Validate file size (max 10MB)
        $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($fileSize > $maxFileSize) {
            $this->Flash->error(__('File size too large. Maximum file size is 10MB.'));
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
                    $requestWithUser = $this->CoachingServiceRequests->get($id, contain: ['Users']);
                    
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
        $this->request->allowMethod(['post', 'put']);
        
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        
        if (!$admin || $admin->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to perform this action.'));
            return $this->redirect(['controller' => 'Admin', 'action' => 'dashboard']);
        }
        
        try {
            $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: ['Users']);
        } catch (\Exception $e) {
            $this->Flash->error(__('Coaching service request not found.'));
            return $this->redirect(['action' => 'index']);
        }
        
        $data = $this->request->getData();
        $newStatus = $data['status'] ?? null;
        
        // Log the received data for debugging
        $this->log('UpdateStatus called with ID: ' . $id, 'debug');
        $this->log('POST data: ' . json_encode($data), 'debug');
        $this->log('New status: ' . ($newStatus ?? 'null'), 'debug');
        
        $validStatuses = ['pending', 'in_progress', 'completed', 'canceled', 'cancelled'];
        
        if (empty($newStatus)) {
            $this->Flash->error(__('No status provided. Please select a status.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        if (!in_array($newStatus, $validStatuses)) {
            $this->Flash->error(__('Invalid status "{0}". Please select a valid status.', $newStatus));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        $oldStatus = $coachingServiceRequest->request_status;
        
        // Check if status is actually changing
        if ($oldStatus === $newStatus) {
            $this->Flash->info(__('Status is already set to "{0}".', ucfirst(str_replace('_', ' ', $newStatus))));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        $coachingServiceRequest->request_status = $newStatus;
        
        if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
            $this->Flash->success(__('The request status has been updated from "{0}" to "{1}".', 
                ucfirst(str_replace('_', ' ', $oldStatus)), 
                ucfirst(str_replace('_', ' ', $newStatus))
            ));
            
            // Add a message to the conversation thread about the status change
            $statusMessage = "**Status Update**\n\n";
            $statusMessage .= "The status of this coaching request has been changed from **" . ucfirst(str_replace('_', ' ', $oldStatus)) . "** to **" . ucfirst(str_replace('_', ' ', $newStatus)) . "**.";
            
            if ($newStatus === 'completed') {
                $statusMessage .= "\n\nThank you for using our coaching services! If you have any feedback or questions, please let us know.";
            } elseif ($newStatus === 'canceled' || $newStatus === 'cancelled') {
                $statusMessage .= "\n\nIf you have any questions about this cancellation, please contact us.";
            } elseif ($newStatus === 'in_progress') {
                $statusMessage .= "\n\nWe have started working on your coaching request. You can expect updates as we progress.";
            }
            
            $messageData = [
                'coaching_request_messages' => [
                    [
                        'user_id' => $admin->user_id,
                        'message' => $statusMessage,
                        'is_read' => false,
                        'is_deleted' => false,
                        'coaching_service_request_id' => $id,
                    ],
                ],
            ];
            
            $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
                $coachingServiceRequest,
                $messageData
            );
            
            if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
                // Log successful status update
                $this->log("Admin {$admin->user_id} updated coaching request {$id} status from {$oldStatus} to {$newStatus}", 'info');
            } else {
                $this->log('Failed to save status update message for coaching request ' . $id, 'warning');
            }
            
            // Notify the client via email about the status change
            try {
                $requestWithUser = $this->CoachingServiceRequests->get($id, contain: ['Users']);
                
                if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                    // Send status update notification
                    $mailer = new \App\Mailer\PaymentMailer('default');
                    $mailer->coachingStatusUpdateNotification(
                        $requestWithUser,
                        $oldStatus,
                        $newStatus
                    );
                    $result = $mailer->deliverAsync();
                    
                    if ($result) {
                        $this->log('Status update email sent successfully to ' . $requestWithUser->user->email, 'info');
                    } else {
                        $this->log('Status update email failed to send to ' . $requestWithUser->user->email, 'warning');
                    }
                }
            } catch (\Exception $e) {
                $this->log('Failed to send status update notification: ' . $e->getMessage(), 'error');
                // Don't fail the entire request for email issues
            }
            
        } else {
            $errors = $coachingServiceRequest->getErrors();
            $this->log('Failed to update coaching request status. Errors: ' . json_encode($errors), 'error');
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
     * Create basic time slots for a given date string
     *
     * @param string $dateString Date in Y-m-d format
     * @return array List of time slots
     */
    private function createBasicTimeSlots(string $dateString): array
    {
        $slots = [];
        
        // Generate time slots every hour from midnight to 11 PM
        $startHour = 0; // Midnight
        $endHour = 24;  // End of day
        
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            // Skip some slots randomly to simulate busy times (reduce the skip rate for more availability)
            if (rand(0, 100) < 15) { // Only 15% chance of being unavailable
                continue;
            }
            
            $startTime = sprintf('%02d:00', $hour);
            $endHour2 = $hour + 1;
            $endTime = sprintf('%02d:00', $endHour2);
                
            // Handle the last hour of the day
            if ($endHour2 >= 24) {
                $endTime = '23:59';
            }
            
                    $formattedStart = date('g:i A', strtotime($startTime));
                    $formattedEnd = date('g:i A', strtotime($endTime));
                    
                    $slots[] = [
                        'date' => $dateString,
                        'start' => $startTime,
                        'end' => $endTime,
                        'formatted' => "{$formattedStart} - {$formattedEnd}",
                    ];
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
        
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: ['Users']);
        
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
                $requestWithUser = $this->CoachingServiceRequests->get($id, contain: ['Users']);
                
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
        
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: ['Users']);
        
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
                $requestWithUser = $this->CoachingServiceRequests->get($id, contain: ['Users']);
                
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