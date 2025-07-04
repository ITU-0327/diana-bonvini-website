<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\PaymentMailer;
use App\Model\Entity\WritingServicePayment;
use App\Model\Entity\WritingServiceRequest;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Routing\Router;
use Cake\Utility\Text;
use DateTime;
use DateTimeInterface;
use Exception;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Cake\Http\Exception\BadRequestException;

/**
 * WritingServiceRequests Controller
 *
 * @property \App\Model\Table\WritingServiceRequestsTable $WritingServiceRequests
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class WritingServiceRequestsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        $userId = $user?->get('user_id');

        if (!$userId) {
            $this->Flash->error(__('You need to be logged in to view your writing service requests.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $query = $this->WritingServiceRequests->find()
            ->contain(['Users', 'WritingServicePayments'])
            ->where(['WritingServiceRequests.user_id' => $userId]);

        $this->paginate = [
            'order' => ['WritingServiceRequests.created_at' => 'DESC'],
        ];

        $writingServiceRequests = $this->paginate($query);

        $this->set(compact('writingServiceRequests'));
    }

    /**
     * View method
     *
     * @param string|null $id Writing Service Request id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user) {
            $this->Flash->error(__('You need to be logged in.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        // Use basic contain without WritingServicePayments to ensure it works
        $writingServiceRequest = $this->WritingServiceRequests->get(
            $id,
            contain: [
                'Users',
                'RequestMessages' => function ($q) {
                    return $q->contain(['Users'])
                        ->orderBy(['RequestMessages.created_at' => 'ASC']);
                },
                'WritingServicePayments' => function ($q) {
                    return $q->orderBy(['WritingServicePayments.created_at' => 'DESC']);
                },
            ],
        );

        // Fetch appointments for this request to check if time slots have been accepted
        $appointmentsTable = $this->fetchTable('Appointments');
        $appointments = $appointmentsTable->find()
            ->where([
                'writing_service_request_id' => $id,
                'is_deleted' => false,
            ])
            ->orderBy(['appointment_date' => 'ASC', 'appointment_time' => 'ASC'])
            ->toArray();

        // Fetch request documents
        $requestDocumentsTable = $this->fetchTable('RequestDocuments');
        $requestDocuments = $requestDocumentsTable->find()
            ->where([
                'writing_service_request_id' => $id,
                'is_deleted' => false,
            ])
            ->orderBy(['created_at' => 'DESC'])
            ->toArray();

        // Mark messages from admin as read when customer views them
        $this->markMessagesAsRead($writingServiceRequest, $user->user_id);

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            
            // Log request data for debugging
            $this->log('Client message submission for request ' . $id . ': ' . json_encode([
                'data' => $data,
                'is_ajax' => $this->request->is('ajax'),
                'headers' => $this->request->getHeaders(),
            ]), 'debug');

            if (!empty($data['reply_message'])) {
                $this->log('Processing client reply message for user: ' . $user->user_id, 'debug');
                
                // For AJAX requests, handle differently for faster response
                if ($this->request->is('ajax') || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                    $this->log('Handling AJAX message submission', 'debug');
                    return $this->_handleAjaxMessageSubmission($id, $data['reply_message'], $user);
                }

                $this->log('Handling regular form submission (fallback)', 'debug');
                
                // Regular form submission handling (fallback)
                $data['request_messages'][] = [
                    'user_id' => $user->user_id,
                    'message' => $data['reply_message'],
                    'is_read' => false, // Initially not read by the admin
                    'is_deleted' => false, // Ensure is_deleted is set
                    'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                ];

                $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
                    $writingServiceRequest,
                    $data,
                );

                $this->log('About to save writing service request with message', 'debug');
                
                if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                    $this->log('Client message saved successfully', 'info');
                    $this->Flash->success(__('Message sent successfully.'));

                    // If the request status is pending, update it to in_progress
                    if ($writingServiceRequest->request_status === 'pending') {
                        $writingServiceRequest->request_status = 'in_progress';
                        $this->WritingServiceRequests->save($writingServiceRequest);
                    }

                    // Send notification email to admin
                    $this->_scheduleAdminEmailNotification($id, $data['reply_message']);

                    return $this->redirect(['action' => 'view', $id]);
                } else {
                    $this->log('Failed to save client message. Errors: ' . json_encode($writingServiceRequest->getErrors()), 'error');
                    $this->Flash->error(__('Failed to send message. Please try again.'));
                }
            } else {
                $this->log('No reply_message in POST data for request ' . $id, 'warning');
            }
        }

        $this->set(compact('writingServiceRequest', 'appointments', 'requestDocuments'));
    }

    /**
     * Marks messages as read for the given user
     *
     * @param \App\Model\Entity\WritingServiceRequest $writingServiceRequest The writing service request
     * @param string $userId The ID of the current user
     * @return void
     */
    private function markMessagesAsRead(WritingServiceRequest $writingServiceRequest, string $userId): void
    {
        if (empty($writingServiceRequest->request_messages)) {
            return;
        }

        $requestMessagesTable = $this->fetchTable('RequestMessages');
        $updatedCount = 0;

        foreach ($writingServiceRequest->request_messages as $message) {
            // Only mark messages from other users (admin) as read
            if ($message->user_id !== $userId && !$message->is_read) {
                $message->is_read = true;
                $requestMessagesTable->save($message);
                $updatedCount++;
            }
        }

        // Log how many messages were marked as read for debugging
        if ($updatedCount > 0) {
            $this->log("Marked $updatedCount messages as read for user $userId", 'info');
        }
    }

    /**
     * Handle AJAX message submission for faster response
     *
     * @param string $id Writing Service Request id
     * @param string $messageText The message text
     * @param \App\Model\Entity\User $user The current user
     * @return \Cake\Http\Response
     */
    private function _handleAjaxMessageSubmission(string $id, string $messageText, $user): Response
    {
        // Ensure this method always returns JSON
        $this->disableAutoRender();
        $this->response = $this->response->withType('application/json');

        try {
            if (empty(trim($messageText))) {
                $this->log('Empty message text provided in AJAX submission', 'warning');
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Please enter a message'
                ]));
            }

            $this->log('Starting AJAX message submission for request: ' . $id, 'debug');

            // Get the writing service request first to verify it exists
            $writingServiceRequest = $this->WritingServiceRequests->get($id);
            $this->log('Retrieved writing service request successfully', 'debug');

            // Use RequestMessages table directly for more reliable saving
            $requestMessagesTable = $this->fetchTable('RequestMessages');
            
            // Create new message entity directly with proper UUID
            $newMessage = $requestMessagesTable->newEntity([
                'request_message_id' => \Cake\Utility\Text::uuid(), // Generate UUID for primary key
                'writing_service_request_id' => $id,
                'user_id' => $user->user_id,
                'message' => $messageText,
                'is_read' => false, // Initially not read by the admin
                'is_deleted' => false,
                'created_at' => new \DateTime('now'),
                'updated_at' => new \DateTime('now'),
            ]);

            // Save the message directly to RequestMessages table
            if ($requestMessagesTable->save($newMessage)) {
                $this->log('Client message saved successfully via AJAX. Message ID: ' . $newMessage->request_message_id, 'info');
                
                // If the request status is pending, update it to in_progress
                if ($writingServiceRequest->request_status === 'pending') {
                    $writingServiceRequest->request_status = 'in_progress';
                    $this->WritingServiceRequests->save($writingServiceRequest);
                }

                // Schedule email notification to run in background
                $this->_scheduleAdminEmailNotification($id, $messageText);

                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'messageId' => $newMessage->request_message_id
                ]));
            } else {
                $this->log('Failed to save client message via AJAX. Errors: ' . json_encode($newMessage->getErrors()), 'error');
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to send message. Please try again.',
                    'errors' => $newMessage->getErrors()
                ]));
            }
        } catch (Exception $e) {
            $this->log('Error in AJAX message submission: ' . $e->getMessage(), 'error');
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'An error occurred while sending the message: ' . $e->getMessage()
            ]));
        }
    }

    /**
     * Schedule admin email notification to run in background
     *
     * @param string $id Writing Service Request id
     * @param string $messageText The message text
     * @return void
     */
    private function _scheduleAdminEmailNotification(string $id, string $messageText): void
    {
        try {
            // For now, just run it asynchronously with a small delay
            // In a production environment, you might want to use a proper queue system
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request(); // Send response to client immediately
            }
            
            // Now send the email without blocking the response
            $this->_sendAdminEmailNotification($id, $messageText);
        } catch (Exception $e) {
            $this->log('Error scheduling admin email notification: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Send admin email notification immediately
     *
     * @param string $id Writing Service Request id
     * @param string $messageText The message text
     * @return void
     */
    private function _sendAdminEmailNotification(string $id, string $messageText): void
    {
        try {
            // Get a fresh copy of the request with user data
            $requestWithUser = $this->WritingServiceRequests->get($id, contain: ['Users']);

            // Fixed admin email
            $adminEmail = 'diana@dianabonvini.com';
            $adminName = 'Diana Bonvini';

            // Send admin notification
            $mailer = new PaymentMailer('default');
            $mailer->newMessageNotification(
                $requestWithUser,
                $messageText,
                $adminEmail,
                $adminName,
            );
            $result = $mailer->deliverAsync();

            if ($result) {
                $this->log('Admin message notification sent successfully to ' . $adminEmail, 'info');
            } else {
                $this->log('Admin message notification failed to send to ' . $adminEmail, 'warning');
            }
        } catch (Exception $e) {
            $this->log('Error sending admin message notification: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * AJAX endpoint to fetch new messages
     *
     * @param string|null $id Writing Service Request id
     * @param string|null $lastMessageId The ID of the last message the client has
     * @return \Cake\Http\Response|null The JSON response with new messages
     */
    public function fetchMessages(?string $id = null, ?string $lastMessageId = null)
    {
        $this->request->allowMethod(['get', 'ajax']);

        if ($this->request->is('ajax')) {
            $this->disableAutoRender();
            $this->response = $this->response->withType('application/json');

            if (empty($id)) {
                $jsonResponse = json_encode([
                    'success' => false,
                    'message' => 'Request ID is required',
                ]);
                if ($jsonResponse === false) {
                    return $this->response->withStringBody('{"success":false,"message":"Error encoding response"}');
                }

                return $this->response->withStringBody($jsonResponse);
            }

            /** @var \App\Model\Entity\User|null $user */
            $user = $this->Authentication->getIdentity();

            if (!$user) {
                $jsonResponse = json_encode([
                    'success' => false,
                    'message' => 'Authentication required',
                ]);
                if ($jsonResponse === false) {
                    return $this->response->withStringBody('{"success":false,"message":"Error encoding response"}');
                }

                return $this->response->withStringBody($jsonResponse);
            }

            // Get the lastMessageId from query parameter if not provided as route parameter
            if (empty($lastMessageId)) {
                $lastMessageId = $this->request->getQuery('lastMessageId');
            }

            try {
                $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: [
                    'RequestMessages' => function ($q) use ($lastMessageId) {
                        $query = $q->contain(['Users'])
                            ->orderBy(['RequestMessages.created_at' => 'ASC']);

                        if (!empty($lastMessageId)) {
                            // Only get messages newer than the lastMessageId
                            $query->where(['RequestMessages.request_message_id >' => $lastMessageId]);
                        }

                        return $query;
                    },
                ]);

                // Format messages for JSON response
                $messages = [];
                if (!empty($writingServiceRequest->request_messages)) {
                    foreach ($writingServiceRequest->request_messages as $message) {
                        $isAdmin = isset($message->user) && $message->user->user_type === 'admin';

                        $messages[] = [
                            'id' => $message->request_message_id,
                            'content' => $message->message,
                            'sender' => $isAdmin ? 'admin' : 'client',
                            'senderName' => $isAdmin ? 'Admin' : ($message->user->first_name . ' ' . $message->user->last_name),
                            'timestamp' => $message->created_at->format('c'), // ISO 8601 format for client-side conversion
                            'timestamp_display' => '', // Will be filled by client-side JS
                            'is_read' => (bool)$message->is_read,
                            'created_at' => $message->created_at->format('c'), // ISO 8601 format
                        ];

                        // Mark the message as read if it's not from the current user
                        if ($message->user_id !== $user->user_id && !$message->is_read) {
                            $message->is_read = true;
                            $this->WritingServiceRequests->RequestMessages->save($message);
                        }
                    }
                }

                $jsonResponse = json_encode([
                    'success' => true,
                    'messages' => $messages,
                    'count' => count($messages),
                ]);
                if ($jsonResponse === false) {
                    return $this->response->withStringBody('{"success":false,"message":"Error encoding response"}');
                }

                return $this->response->withStringBody($jsonResponse);
            } catch (Exception $e) {
                $jsonResponse = json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ]);
                if ($jsonResponse === false) {
                    return $this->response->withStringBody('{"success":false,"message":"Error encoding response"}');
                }

                return $this->response->withStringBody($jsonResponse);
            }
        }

        $jsonResponse = json_encode([
            'success' => false,
            'message' => 'Invalid request',
        ]);
        if ($jsonResponse === false) {
            return $this->response->withStringBody('{"success":false,"message":"Error encoding response"}');
        }

        return $this->response->withStringBody($jsonResponse);
    }

    /**
     * Get messages as HTML for AJAX updates
     *
     * @param string|null $id Writing Service Request id
     * @return \Cake\Http\Response|null The response with HTML content
     */
    public function getMessages(?string $id = null)
    {
        $this->request->allowMethod(['get', 'ajax']);

        if ($this->request->is('ajax')) {
            $this->disableAutoRender();
            $this->response = $this->response->withType('application/json');

            if (empty($id)) {
                return $this->response->withStringBody('{"success":false,"message":"Request ID is required"}');
            }

            /** @var \App\Model\Entity\User|null $user */
            $user = $this->Authentication->getIdentity();

            if (!$user) {
                return $this->response->withStringBody('{"success":false,"message":"Authentication required"}');
            }

            try {
                $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: [
                    'RequestMessages' => function ($q) {
                        return $q->contain(['Users'])
                            ->orderBy(['RequestMessages.created_at' => 'ASC']);
                    },
                ]);

                // Mark messages from admin as read when customer views them
                $this->markMessagesAsRead($writingServiceRequest, $user->user_id);

                // Render the messages HTML
                $this->set(compact('writingServiceRequest'));
                $this->viewBuilder()->setLayout(false);
                $this->viewBuilder()->setTemplate('messages_only');
                $html = $this->render();

                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'html' => $html->getBody()->getContents(),
                ]));
            } catch (Exception $e) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ]));
            }
        }

        throw new BadRequestException('This endpoint only accepts AJAX requests');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $writingServiceRequest = $this->WritingServiceRequests->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Make sure we have required fields
            $data['user_id'] = $user->user_id;
            $data['request_status'] = 'pending';
            $data['is_deleted'] = false;

            // Handle appointment_id - make it nullable
            if (empty($data['appointment_id'])) {
                unset($data['appointment_id']);
            }

            // Handle file upload
            $documentPath = $this->_handleDocumentUpload($data['document'] ?? null, 'add');
            if ($this->response->getStatusCode() === 302) {
                return $this->response;
            }
            if ($documentPath) {
                $data['document'] = $documentPath;
            } else {
                unset($data['document']);
            }

            $writingServiceRequest = $this->WritingServiceRequests->patchEntity($writingServiceRequest, $data);

            if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                $this->Flash->success(__('The writing service request has been saved.'));

                // Send notification email to admin
                try {
                    // Get the writing service request with user information
                    $writingServiceRequest = $this->WritingServiceRequests->get($writingServiceRequest->writing_service_request_id, contain: ['Users']);

                    // Fixed admin email
                    $adminEmail = 'diana@dianabonvini.com';
                    $adminName = 'Diana Bonvini';

                    // Send admin notification
                    $mailer = new PaymentMailer('default');
                    $mailer->newRequestNotification($writingServiceRequest, $adminEmail, $adminName);
                    $mailer->deliverAsync();
                } catch (Exception $e) {
                    // Log critical errors only
                    $this->log('Error sending new writing service request notification: ' . $e->getMessage(), 'error');
                }

                return $this->redirect(['action' => 'index']);
            }

            $this->log('Error saving writing service request: ' . json_encode($writingServiceRequest->getErrors()), 'error');
            $this->Flash->error(__('The writing service request could not be saved. Please, try again.'));
        }

        $this->set(compact('writingServiceRequest'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Writing Service Request id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();
        $writingServiceRequest = $this->WritingServiceRequests->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            // Handle file upload
            $documentPath = $this->_handleDocumentUpload($data['document'] ?? null, 'edit');
            if ($this->response->getStatusCode() === 302) {
                return $this->response;
            }
            if ($documentPath) {
                $data['document'] = $documentPath;
            } else {
                unset($data['document']);
            }

            $data['user_id'] = $user->user_id;

            $writingServiceRequest = $this->WritingServiceRequests->patchEntity($writingServiceRequest, $data);

            if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                $this->Flash->success(__('The writing service request has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The writing service request could not be saved. Please, try again.'));
        }

        $this->set(compact('writingServiceRequest'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Writing Service Request id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $writingServiceRequest = $this->WritingServiceRequests->get($id);
        if ($this->WritingServiceRequests->delete($writingServiceRequest)) {
            $this->Flash->success(__('The writing service request has been deleted.'));
        } else {
            $this->Flash->error(__('The writing service request could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Check payment status
     *
     * @param string|null $id Writing Service Request id
     * @param string|null $paymentId Unique payment identifier
     * @return \Cake\Http\Response JSON response with payment status
     */
    public function checkPaymentStatus(?string $id = null, ?string $paymentId = null)
    {
        $this->request->allowMethod(['get', 'ajax']);
        $this->disableAutoRender();
        $this->response = $this->response->withType('application/json');

        // Log request information for debugging
        $this->log('checkPaymentStatus called with: ' . json_encode([
            'id' => $id,
            'paymentId' => $paymentId,
            'query_params' => $this->request->getQueryParams(),
            'url' => $this->request->getRequestTarget(),
        ]), 'debug');

        // Support both URL segments and query parameters
        if (empty($id)) {
            $id = $this->request->getQuery('id');
        }

        if (empty($paymentId)) {
            $paymentId = $this->request->getQuery('paymentId');
        }

        if (empty($id) || empty($paymentId)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Request ID and Payment ID are required',
                'error_type' => 'missing_parameters',
            ]));
        }

        // URL-decode the payment ID first
        $paymentId = urldecode($paymentId);

        // Check if we have a session payment ID or a database payment ID
        $parts = explode('|', $paymentId);
        $sessionPaymentId = $parts[0];
        $dbPaymentId = $parts[1] ?? null;

        // Ensure dbPaymentId is a string if not null
        if ($dbPaymentId !== null && !is_string($dbPaymentId)) {
            $dbPaymentId = (string)$dbPaymentId;
            $this->log('Converted dbPaymentId to string in checkPaymentStatus: ' . $dbPaymentId, 'debug');
        }

        // Default status and payment details
        $status = 'pending';
        $paymentDetails = null;
        $isPaid = false;

        // Always prioritize session data, since that's more reliable
        $paymentData = $this->request->getSession()->read("WsrPayments.$sessionPaymentId");

        // Log session payment data for debugging
        $this->log('Session payment data: ' . json_encode($paymentData), 'debug');

        if ($paymentData) {
            $status = $paymentData['status'] ?? 'pending';
            $paymentDetails = $paymentData['receipt'] ?? null;

            // If status is already paid in session, use that
            if ($status === 'paid' && $paymentDetails) {
                $isPaid = true;

                // Try to update database, but don't fail if can't
                try {
                    // Get the writing service request to ensure we have the correct price
                    $writingServiceRequest = $this->WritingServiceRequests->get($id);

                    // Make sure the database is also updated
                    $this->_updateDatabasePaymentStatus($id, $dbPaymentId, $sessionPaymentId, $paymentDetails);
                } catch (Exception $e) {
                    // Log error but don't block the response
                    $this->log('Error updating DB payment status: ' . $e->getMessage(), 'error');
                }

                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'status' => $status,
                    'paid' => true,
                    'details' => $paymentDetails,
                ]));
            }
        }

        // Check URL parameters for payment success indicators
        $paymentSuccess = $this->request->getQuery('payment_success');
        $paymentAlreadyCompleted = $this->request->getQuery('payment_already_completed');

        if ($paymentSuccess === 'true' || $paymentAlreadyCompleted === 'true') {
            $isPaid = true;
            $status = 'paid';

            // Create payment details if missing
            if (!$paymentDetails) {
                $paymentDetails = [
                    'transaction_id' => 'COMPLETED-' . substr(md5($sessionPaymentId), 0, 8),
                    'amount' => '0.00', // Default value, will try to update from DB
                    'date' => time(),
                    'status' => 'paid',
                ];

                // Try to get amount from writing service request, but don't fail if we can't
                try {
                    $writingServiceRequest = $this->WritingServiceRequests->get($id);
                    if (!empty($writingServiceRequest->final_price)) {
                        $paymentDetails['amount'] = $writingServiceRequest->final_price;
                    }

                    // Update request status to in_progress if it was pending
                    if ($writingServiceRequest->request_status === 'pending') {
                        $writingServiceRequest->request_status = 'in_progress';
                        $this->WritingServiceRequests->save($writingServiceRequest);
                    }
                } catch (Exception $e) {
                    // Log error but don't block the response
                    $this->log('Warning: Could not get WritingServiceRequest: ' . $e->getMessage(), 'warning');
                }

                // Update session data
                $this->request->getSession()->write("WsrPayments.$sessionPaymentId.status", 'paid');
                $this->request->getSession()->write("WsrPayments.$sessionPaymentId.receipt", $paymentDetails);

                // Try to update database, but don't fail if can't
                try {
                    $this->_updateDatabasePaymentStatus($id, $dbPaymentId, $sessionPaymentId, $paymentDetails);
                } catch (Exception $e) {
                    // Log error but don't block the response
                    $this->log('Error updating DB payment status: ' . $e->getMessage(), 'error');
                }
            }
        }

        // Only try database as fallback if we don't already know it's paid
        if (!$isPaid && $dbPaymentId && $dbPaymentId !== 'pending') {
            try {
                $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');
                try {
                    $paymentEntity = $writingServicePaymentsTable->get($dbPaymentId);
                    $status = $paymentEntity->status;

                    if ($status === 'paid') {
                        $isPaid = true;
                        $paymentDetails = [
                            'transaction_id' => $paymentEntity->transaction_id,
                            'amount' => $paymentEntity->amount,
                            'date' => $paymentEntity->payment_date instanceof DateTimeInterface ? $paymentEntity->payment_date->getTimestamp() : time(),
                            'status' => $paymentEntity->status,
                            'db_payment_id' => $paymentEntity->writing_service_payment_id,
                        ];

                        // Also update session data for future reference
                        if ($paymentData) {
                            $this->request->getSession()->write("WsrPayments.$sessionPaymentId.status", 'paid');
                            $this->request->getSession()->write("WsrPayments.$sessionPaymentId.receipt", $paymentDetails);
                        }
                    }
                } catch (Exception $e) {
                    $this->log('Error fetching payment record: ' . $e->getMessage(), 'warning');
                }
            } catch (Exception $e) {
                $this->log('WritingServicePayments table not available: ' . $e->getMessage(), 'warning');
            }
        }

        // Last resort - check for session parameters that indicate success
        if (!$isPaid && $paymentData) {
            // If we got here, we should check if the request has a final price and is not in pending status
            try {
                $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: ['RequestMessages']);

                // NEW: Check for payment confirmation messages in the messages
                $hasPaymentConfirmation = false;
                if (!empty($writingServiceRequest->request_messages)) {
                    foreach ($writingServiceRequest->request_messages as $message) {
                        // Only consider it confirmed if the confirmation message contains THIS payment ID
                        if (strpos($message->message, '[PAYMENT_CONFIRMATION]') !== false) {
                            // Check if this specific payment ID is mentioned in the confirmation message
                            if (
                                strpos($message->message, "Payment ID: $sessionPaymentId") !== false ||
                                strpos($message->message, "Payment ID:$sessionPaymentId") !== false ||
                                ($dbPaymentId && (strpos($message->message, "Payment ID: $dbPaymentId") !== false ||
                                                strpos($message->message, "Payment ID:$dbPaymentId") !== false))
                            ) {
                                $hasPaymentConfirmation = true;
                                break;
                            }
                        }
                    }
                }

                // If there's a final price and either not in pending status or has payment confirmation
                if (
                    !empty($writingServiceRequest->final_price) &&
                    ($writingServiceRequest->request_status !== 'pending' || $hasPaymentConfirmation)
                ) {
                    $isPaid = true;
                    $status = 'paid';

                    // Create payment details if missing
                    if (!$paymentDetails) {
                        $paymentDetails = [
                            'transaction_id' => 'R-' . substr($id, -7),
                            'amount' => $writingServiceRequest->final_price,
                            'date' => time(),
                            'status' => 'paid',
                        ];

                        // Update session data
                        $this->request->getSession()->write("WsrPayments.$sessionPaymentId.status", 'paid');
                        $this->request->getSession()->write("WsrPayments.$sessionPaymentId.receipt", $paymentDetails);

                        // If request is still pending but we have payment confirmation, update it
                        if ($writingServiceRequest->request_status === 'pending' && $hasPaymentConfirmation) {
                            $writingServiceRequest->request_status = 'in_progress';
                            $this->WritingServiceRequests->save($writingServiceRequest);
                        }

                        // Try database update
                        try {
                            $this->_updateDatabasePaymentStatus($id, $dbPaymentId, $sessionPaymentId, $paymentDetails);
                        } catch (Exception $e) {
                            // Log error but continue
                            $this->log('Error in DB update: ' . $e->getMessage(), 'error');
                        }
                    }
                }
            } catch (Exception $e) {
                // If we can't get the writing service request, still provide a usable response
                $this->log('Error getting final status: ' . $e->getMessage(), 'error');
            }
        }

        // If payment is marked as paid, add a confirmation message, but only if it doesn't already exist
        if ($isPaid && $status === 'paid') {
            try {
                // Get existing messages to check if confirmation already exists
                $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: ['RequestMessages']);

                // Only add confirmation if we don't already have one for this payment
                $paymentId = !empty($paymentDetails['transaction_id'])
                    ? $paymentDetails['transaction_id']
                    : (!empty($paymentDetails['payment_id']) ? $paymentDetails['payment_id'] : null);

                $hasConfirmation = false;

                if ($paymentId && !empty($writingServiceRequest->request_messages)) {
                    foreach ($writingServiceRequest->request_messages as $message) {
                        if (
                            strpos($message->message, '[PAYMENT_CONFIRMATION]') !== false &&
                            strpos($message->message, "Payment ID: {$paymentId}") !== false
                        ) {
                            $hasConfirmation = true;
                            break;
                        }
                    }
                }

                if (!$hasConfirmation) {
                    $this->_addPaymentConfirmationMessage($id, $paymentDetails);
                } else {
                    $this->log("Skipping duplicate payment confirmation for ID: {$paymentId}", 'debug');
                }
            } catch (Exception $e) {
                // Log error but continue
                $this->log('Error processing confirmation message: ' . $e->getMessage(), 'error');
            }
        }

        // Return success even if there were some errors along the way
        // The UI will show the payment status based on the information we have
        return $this->response->withStringBody(json_encode([
            'success' => true,
            'status' => $status,
            'paid' => $isPaid,
            'details' => $paymentDetails,
            'sessionId' => $sessionPaymentId,
        ]));
    }

    /**
     * Update payment status in database
     *
     * @param string $requestId The writing service request ID
     * @param string|int|null $dbPaymentId The database payment ID
     * @param string $sessionPaymentId The session payment ID
     * @param array $paymentDetails Payment details
     * @return bool True if successful, false otherwise
     */
    protected function _updateDatabasePaymentStatus(string $requestId, string|int|null $dbPaymentId, string $sessionPaymentId, array $paymentDetails): bool
    {
        try {
            // Ensure dbPaymentId is a string or null
            if ($dbPaymentId !== null && !is_string($dbPaymentId)) {
                $dbPaymentId = (string)$dbPaymentId;
            }

            // Get the writing service request with payment data
            $writingServiceRequest = $this->WritingServiceRequests->get($requestId, contain: ['WritingServicePayments']);

            // Use the amount from payment details
            $amount = $paymentDetails['amount'] ?? '0.00';

            $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');

            // Try to find existing payment record
            if ($dbPaymentId && $dbPaymentId !== 'pending') {
                try {
                    $paymentEntity = $writingServicePaymentsTable->get($dbPaymentId);
                } catch (Exception $e) {
                    $paymentEntity = null;
                    $this->log('Payment record not found, will create new: ' . $e->getMessage(), 'debug');
                }
            } else {
                $paymentEntity = null;
            }

            // If no existing payment record, create a new one
            if (!$paymentEntity) {
                $paymentEntity = $writingServicePaymentsTable->newEntity([
                    'writing_service_request_id' => $requestId,
                    'amount' => $amount,
                    'transaction_id' => $paymentDetails['transaction_id'] ?? 'COMPLETED-' . substr(md5($sessionPaymentId), 0, 8),
                    'payment_date' => date('Y-m-d H:i:s'),
                    'payment_method' => 'stripe',
                    'status' => 'paid',
                ]);
            } else {
                // Update existing payment record
                $paymentEntity->status = 'paid';
                $paymentEntity->transaction_id = $paymentDetails['transaction_id'] ?? $paymentEntity->transaction_id;
                $paymentEntity->amount = $amount;
            }

            // Save the payment record
            $result = $writingServicePaymentsTable->save($paymentEntity);

            if ($result) {
                $this->log('Payment status updated in database: ' . json_encode([
                        'id' => $paymentEntity->writing_service_payment_id,
                        'status' => $paymentEntity->status,
                        'amount' => $paymentEntity->amount,
                    ]), 'debug');

                // Also update the writing service request status
                if ($writingServiceRequest->request_status === 'pending') {
                    $writingServiceRequest->request_status = 'in_progress';
                    $this->WritingServiceRequests->save($writingServiceRequest);
                }

                return true;
            } else {
                $this->log('Failed to update payment status in database: ' . json_encode($paymentEntity->getErrors()), 'error');

                return false;
            }
        } catch (Exception $e) {
            $this->log('Error updating payment status in database: ' . $e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Handle payment for a writing service request
     *
     * @param string|null $id Writing Service Request id
     * @param string|null $paymentId Unique payment identifier
     * @return \Cake\Http\Response|null
     */
    public function pay(?string $id = null, ?string $paymentId = null)
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        $this->log('pay method called with parameters: ' . json_encode([
                'id' => $id,
                'paymentId' => $paymentId,
                'user' => $user ? $user->user_id : 'not logged in',
            ]), 'info');

        if (!$user) {
            $this->Flash->info(__('Please log in to make this payment.'));
            $this->request->getSession()->write('Writing.paymentRedirect', [
                'id' => $id,
                'paymentId' => $paymentId,
            ]);

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        // Get writing service request with basic containment
        try {
            $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: ['Users']);
        } catch (Exception $e) {
            $this->log('Error retrieving request: ' . $e->getMessage(), 'error');
            $this->Flash->error('Unable to find the requested service.');

            return $this->redirect(['action' => 'index']);
        }

        // Verify the request belongs to this user
        if ($writingServiceRequest->user_id !== $user->user_id) {
            $this->Flash->error(__('You can only make payments for your own writing service requests.'));

            return $this->redirect(['action' => 'index']);
        }

        // URL-decode the payment ID first
        $paymentId = urldecode($paymentId);

        // Check if we have a session payment ID or a database payment ID
        $parts = explode('|', $paymentId);
        $sessionPaymentId = $parts[0];
        $dbPaymentId = $parts[1] ?? null;

        // Ensure dbPaymentId is a string if not null
        if ($dbPaymentId !== null && !is_string($dbPaymentId)) {
            $dbPaymentId = (string)$dbPaymentId;
            $this->log('Converted dbPaymentId to string in pay method: ' . $dbPaymentId, 'debug');
        }

        // First check session payment status (prioritize session data)
        $paymentData = $this->request->getSession()->read("WsrPayments.$sessionPaymentId");

        $this->log('Payment data from session: ' . json_encode([
                'sessionPaymentId' => $sessionPaymentId,
                'dbPaymentId' => $dbPaymentId,
                'paymentData' => $paymentData,
            ]), 'debug');

        if (!$paymentData) {
            $this->Flash->error(__('Invalid payment request.'));
            $this->log('Payment data not found in session', 'error');

            return $this->redirect(['action' => 'view', $id]);
        }

        // Check if already paid in session
        if (isset($paymentData['status']) && $paymentData['status'] === 'paid') {
            $this->Flash->success(__('This payment has already been completed.'));

            return $this->redirect(['action' => 'view', $id, '?' => ['payment_already_completed' => true]]);
        }

        // Only check database as fallback if session doesn't indicate payment completion
        if ($dbPaymentId && $dbPaymentId !== 'pending') {
            try {
                $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');
                try {
                    $paymentEntity = $writingServicePaymentsTable->get($dbPaymentId);

                    // If payment is already marked as paid in database, redirect
                    if ($paymentEntity->status === 'paid') {
                        // Also update session for consistency
                        $this->request->getSession()->write("WsrPayments.$sessionPaymentId.status", 'paid');

                        $this->Flash->success(__('This payment has already been completed.'));

                        return $this->redirect(['action' => 'view', $id, '?' => ['payment_already_completed' => true]]);
                    }
                } catch (Exception $e) {
                    $this->log('Error fetching payment record: ' . $e->getMessage(), 'error');
                    // Continue with session data
                }
            } catch (Exception $e) {
                $this->log('WritingServicePayments table not available: ' . $e->getMessage(), 'warning');
                // Continue with session data
            }
        }

        // Create Stripe session
        try {
            // Configure Stripe API
            $stripeSecretKey = Configure::read('Stripe.secret');
            $this->log('Using Stripe secret key: ' . substr($stripeSecretKey, 0, 10) . '...', 'debug');

            if (empty($stripeSecretKey)) {
                throw new RuntimeException('Stripe secret key is not configured');
            }

            Stripe::setApiKey($stripeSecretKey);

            // Create the line item
            $amount = $paymentData['amount'];
            $description = $paymentData['description'];

            if (empty($amount) || !is_numeric($amount) || (float)$amount <= 0) {
                throw new RuntimeException('Invalid payment amount: ' . $amount);
            }

            $lineItem = [
                'price_data' => [
                    'currency' => 'aud',
                    'product_data' => [
                        'name' => 'Writing Service: ' . $description,
                    ],
                    'unit_amount' => (int)round((float)$amount * 100),
                ],
                'quantity' => 1,
            ];

            // Create session parameters
            $successUrl = Router::url(
                ['controller' => 'WritingServiceRequests',
                    'action'     => 'paymentSuccess',
                    $id,
                    $sessionPaymentId,
                ],
                true,
            );

            $cancelUrl = Router::url(
                ['controller' => 'WritingServiceRequests', 'action' => 'view', $id],
                true,
            );

            $params = [
                'payment_method_types' => ['card'],
                'line_items' => [$lineItem],
                'mode' => 'payment',
                'metadata' => [
                    'writing_service_request_id' => $id,
                    'payment_id' => $sessionPaymentId,
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ];

            $this->log('Creating Stripe session with params: ' . json_encode([
                    'line_items' => $lineItem,
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                ]), 'debug');

            // Create the session
            $session = Session::create($params);

            if (empty($session->url)) {
                throw new RuntimeException('Stripe did not return a checkout URL');
            }

            $this->log('Stripe session created successfully. Redirecting to: ' . $session->url, 'debug');

            // Redirect to Stripe checkout
            return $this->redirect($session->url);
        } catch (Exception $e) {
            $this->log('Stripe error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'error');
            $this->Flash->error(__('There was an error processing your payment: ') . $e->getMessage());

            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Helper method to send emails asynchronously
     *
     * @param \App\Model\Entity\WritingServiceRequest $request The writing service request
     * @param \App\Model\Entity\WritingServicePayment $payment The payment entity
     * @param array $paymentDetails Payment details
     * @return void
     */
    protected function _sendPaymentEmails(WritingServiceRequest $request, WritingServicePayment $payment, array $paymentDetails): void
    {
        // Fixed admin email
        $adminEmail = 'diana@dianabonvini.com';
        $adminName = 'Diana Bonvini';

        // Send customer email first - wrap in try-catch to prevent errors from blocking process
        try {
            $customerMailer = new PaymentMailer('default');
            $customerMailer->paymentConfirmation($request, $payment, $paymentDetails);
            $result = $customerMailer->deliverAsync();

            if (!$result) {
                $this->log('Customer payment confirmation email failed to send', 'warning');
            }
        } catch (Exception $e) {
            // Log error but don't let it prevent admin notification
            $this->log('Error sending customer confirmation email: ' . $e->getMessage(), 'error');
        }

        // Send admin notification in a separate try-catch
        try {
            $adminMailer = new PaymentMailer('default');
            $adminMailer->adminPaymentNotification(
                $request,
                $payment,
                $adminEmail,
                $adminName,
                $paymentDetails,
            );
            $result = $adminMailer->deliverAsync();

            if (!$result) {
                $this->log('Admin payment notification email failed to send', 'warning');
            }
        } catch (Exception $e) {
            // Log critical errors only
            $this->log('Error sending admin notification email: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Add payment confirmation message to the chat
     *
     * @param string $requestId Writing Service Request ID
     * @param array $paymentDetails Payment details
     * @return bool Success flag
     */
    protected function _addPaymentConfirmationMessage(string $requestId, array $paymentDetails): bool
    {
        // Only add confirmation if we have the necessary data
        if (empty($requestId) || empty($paymentDetails)) {
            return false;
        }

        try {
            // Get the request with user relationship
            $writingServiceRequest = $this->WritingServiceRequests->get($requestId, contain: ['Users', 'RequestMessages']);

            // Format amount
            $amount = !empty($paymentDetails['amount'])
                ? '$' . number_format((float)$paymentDetails['amount'], 2)
                : 'N/A';

            // Format date
            $date = !empty($paymentDetails['date'])
                ? date('F j, Y \a\t g:i A', (int)$paymentDetails['date'])
                : 'recently';

            // Get payment ID (transaction ID or database ID if available)
            $paymentId = !empty($paymentDetails['transaction_id'])
                ? $paymentDetails['transaction_id']
                : (!empty($paymentDetails['payment_id']) ? $paymentDetails['payment_id'] : 'unknown');

            // Check if a confirmation message already exists for this payment ID
            if (!empty($writingServiceRequest->request_messages)) {
                foreach ($writingServiceRequest->request_messages as $message) {
                    // Check if this is a payment confirmation message
                    if (strpos($message->message, '[PAYMENT_CONFIRMATION]') !== false) {
                        // Check if this message contains this specific payment ID
                        if (strpos($message->message, "Payment ID: {$paymentId}") !== false) {
                            // Confirmation already exists, don't add another one
                            $this->log("Payment confirmation message already exists for payment ID: {$paymentId}", 'debug');

                            return true;
                        }
                    }
                }
            }

            // Create confirmation message
            $messageText = "[PAYMENT_CONFIRMATION]\n\n";
            $messageText .= "**Payment Confirmation**\n\n";
            $messageText .= "Your payment of **{$amount}** has been successfully processed on {$date}.\n\n";
            $messageText .= "Payment ID: {$paymentId}\n\n"; // Add payment ID to the message
            $messageText .= "Thank you for your payment. We'll now begin work on your writing service request.";

            // Create system message
            $messageData = [
                'request_messages' => [
                    [
                        'user_id' => $writingServiceRequest->user_id, // Use client's ID for the confirmation
                        'message' => $messageText,
                        'is_read' => false,
                        'writing_service_request_id' => $requestId,
                        'is_system' => true,
                    ],
                ],
            ];

            // Add the message
            $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
                $writingServiceRequest,
                $messageData,
            );

            // Save the entity
            if (!$this->WritingServiceRequests->save($writingServiceRequest)) {
                $this->log('Failed to save payment confirmation message: ' . json_encode($writingServiceRequest->getErrors()), 'error');

                return false;
            }

            // Handle email sending separately from message saving to prevent failures
            $this->_processPaymentConfirmationEmails($writingServiceRequest, $paymentDetails, $paymentId);

            return true;
        } catch (Exception $e) {
            $this->log('Error adding payment confirmation message: ' . $e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Helper method to handle email processing separately from message saving
     *
     * @param \App\Model\Entity\WritingServiceRequest $writingServiceRequest The writing service request
     * @param array $paymentDetails Payment details
     * @param string $paymentId The payment ID
     * @return void
     */
    protected function _processPaymentConfirmationEmails(
        WritingServiceRequest $writingServiceRequest,
        array $paymentDetails,
        string $paymentId,
    ): void {
        try {
            // Get payment details from database if available
            $payment = null;
            $dbPaymentId = !empty($paymentDetails['payment_id']) ? $paymentDetails['payment_id'] : null;

            if ($dbPaymentId) {
                try {
                    $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');
                    $payment = $writingServicePaymentsTable->get($dbPaymentId);

                    // Ensure we're using the current payment amount
                    if (isset($paymentDetails['amount'])) {
                        $payment->amount = (float)$paymentDetails['amount'];
                    }
                } catch (Exception $e) {
                    // Log error but continue with a temporary payment object
                    $this->log('Error retrieving payment record: ' . $e->getMessage(), 'warning');
                }
            }

            // If we don't have a valid payment record, create a temporary one
            if (!$payment) {
                // Create a temporary payment object for email
                $payment = new WritingServicePayment([
                    'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                    'amount' => (float)($paymentDetails['amount'] ?? 0.00),
                    'transaction_id' => $paymentId,
                    'payment_date' => new DateTime(),
                    'payment_method' => 'online',
                    'status' => 'paid',
                ]);
            }

            // Send emails using the helper method
            $this->_sendPaymentEmails($writingServiceRequest, $payment, $paymentDetails);
        } catch (Exception $e) {
            // Log error but don't halt execution
            $this->log('Error in payment confirmation email processing: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Admin index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function adminIndex()
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to access admin area.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $query = $this->WritingServiceRequests->find()
            ->contain(['Users', 'WritingServicePayments' => function ($q) {
                return $q->where(['status' => 'paid']);
            }]);

        $serviceType = $this->request->getQuery('service_type');
        $requestStatus = $this->request->getQuery('request_status');
        $keyword = $this->request->getQuery('q');

        if (!empty($serviceType)) {
            $query->where(['WritingServiceRequests.service_type' => $serviceType]);
        }

        if (!empty($requestStatus)) {
            $query->where(['WritingServiceRequests.request_status' => $requestStatus]);
        }

        if (!empty($keyword)) {
            $query->where([
                'OR' => [
                    'WritingServiceRequests.service_title LIKE' => '%' . $keyword . '%',
                    'Users.first_name LIKE' => '%' . $keyword . '%',
                    'Users.last_name LIKE' => '%' . $keyword . '%',
                ],
            ]);
        }

        $this->paginate = [
            'order' => ['WritingServiceRequests.created_at' => 'DESC'],
        ];

        $writingServiceRequests = $this->paginate($query);

        $this->set(compact('writingServiceRequests'));
    }

    /**
     * Admin view method
     *
     * @param string|null $id Writing Service Request id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function adminView(?string $id = null)
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error(__('You are not authorized to access admin area.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $writingServiceRequest = $this->WritingServiceRequests->get(
            $id,
            contain: [
                'Users',
                'RequestMessages' => function ($q) {
                    return $q->contain(['Users'])
                        ->orderBy(['RequestMessages.created_at' => 'ASC']);
                },
                'WritingServicePayments' => function ($q) {
                    return $q->orderBy(['WritingServicePayments.created_at' => 'DESC']);
                },
            ],
        );

        $this->log(
            '==== DEBUG payment list for request ' . $id . ' ====' . PHP_EOL .
            print_r($writingServiceRequest->writing_service_payments, true) .
            PHP_EOL .
            'Total paid calculated by entity: ' . $writingServiceRequest->getTotalPaidAmount(),
            'debug',
        );

        // Fetch request documents
        $requestDocumentsTable = $this->fetchTable('RequestDocuments');
        $requestDocuments = $requestDocumentsTable->find()
            ->where([
                'writing_service_request_id' => $id,
                'is_deleted' => false,
            ])
            ->orderBy(['created_at' => 'DESC'])
            ->toArray();

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            if (!empty($data['reply_message'])) {
                $data['request_messages'][] = [
                    'user_id' => $user->user_id,
                    'message' => $data['reply_message'],
                ];
            }

            $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
                $writingServiceRequest,
                $data,
            );

            if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                $this->Flash->success(__('Request updated successfully (admin).'));

                return $this->redirect(['action' => 'adminView', $id]);
            } else {
                $this->Flash->error(__('Failed to update. Please try again.'));
            }
        }

        $this->set(compact('writingServiceRequest', 'requestDocuments'));
    }

    /**
     * Request payment for a writing service (admin action)
     *
     * @param string|null $id Writing Service Request id
     * @return \Cake\Http\Response|null
     */
    public function requestPayment(?string $id = null)
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error(__('Not authorized.'));

            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $writingServiceRequest = $this->WritingServiceRequests->get(
                $id,
                contain: ['Users'],
            );
            $data = $this->request->getData();

            if (isset($data['amount']) && is_numeric($data['amount']) && (float)$data['amount'] > 0) {
                $amount = (float)$data['amount'];
                $description = $data['description'] ?? 'Writing service payment';

                // Create a unique session payment ID
                $sessionPaymentId = 'pay_' . uniqid();

                // Store the payment request in session
                $this->request->getSession()->write("WsrPayments.$sessionPaymentId", [
                    'amount' => (string)$amount,
                    'description' => $description,
                    'writing_service_request_id' => $id,
                    'created' => time(),
                    'status' => 'pending',
                    'db_payment_id' => 'pending',
                ]);

                // Create a database record for the payment request
                $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');
                $newPayment = $writingServicePaymentsTable->newEntity([
                    'writing_service_request_id' => $id,
                    'amount' => $amount,
                    'transaction_id' => null,
                    'payment_date' => null,
                    'payment_method' => 'pending',
                    'status' => 'pending',
                    'is_deleted' => false,
                ]);

                if ($writingServicePaymentsTable->save($newPayment)) {
                    // Update the session with the database payment ID
                    $this->request->getSession()->write("WsrPayments.$sessionPaymentId.db_payment_id", $newPayment->writing_service_payment_id);

                    // Create the payment button message
                    $paymentId = $sessionPaymentId . '|' . $newPayment->writing_service_payment_id;
                    $messageText = $description . "\n\n";
                    $messageText .= 'Amount: $' . number_format($amount, 2) . "\n\n";
                    $messageText .= '[PAYMENT_BUTTON]' . $paymentId . '[/PAYMENT_BUTTON]';

                    // Add the message to the conversation
                    $messageData = [
                        'request_messages' => [
                            [
                                'user_id' => $user->user_id,
                                'message' => $messageText,
                                'is_read' => false,
                                'writing_service_request_id' => $id,
                            ],
                        ],
                    ];

                    $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
                        $writingServiceRequest,
                        $messageData,
                    );

                    $this->WritingServiceRequests->save($writingServiceRequest);

                    $this->Flash->success(__('Payment request has been sent to the client.'));
                } else {
                    $this->Flash->error(__('Unable to create payment request. Please try again.'));
                }
            } else {
                $this->Flash->error(__('Invalid payment amount.'));
            }

            return $this->redirect(['action' => 'adminView', $id]);
        }

        return $this->redirect(['action' => 'adminView', $id]);
    }

    /**
     * Handles document upload
     *
     * @param \Psr\Http\Message\UploadedFileInterface|null $file
     * @param string $redirectAction
     * @return \Cake\Http\Response|string|null
     */
    protected function _handleDocumentUpload(?UploadedFileInterface $file, string $redirectAction): string|Response|null
    {
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedMimeTypes = [
            'application/pdf',  // PDF
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
            'application/msword', // DOC
        ];

        if (!in_array($file->getClientMediaType(), $allowedMimeTypes)) {
            $this->Flash->error(__('Invalid file type. Please upload PDF or Word documents only.'));
            return $this->redirect(['action' => $redirectAction]);
        }

        // Use existing directories to avoid mkdir() issues
        $uploadPath = WWW_ROOT . 'uploads' . DS . 'documents';
        
        // If the target directory doesn't exist, try alternative locations
        if (!is_dir($uploadPath)) {
            // Try to use tmp directory first
            $tempPath = sys_get_temp_dir();
            if (is_writable($tempPath)) {
                $this->log('Primary upload directory not available, using system temp directory', 'warning');
                $uploadPath = $tempPath . DS . 'app_uploads';
                
                if (!is_dir($uploadPath)) {
                    try {
                        if (!mkdir($uploadPath, 0777, true)) {
                            $this->log('Failed to create temp upload directory: ' . $uploadPath, 'error');
                            $this->Flash->error(__('Upload system is temporarily unavailable. Please try again later.'));
                            return $this->redirect(['action' => $redirectAction]);
                        }
                    } catch (\Exception $e) {
                        $this->log('Exception creating temp upload directory: ' . $e->getMessage(), 'error');
                        $this->Flash->error(__('Upload system error. Please contact administrator.'));
                        return $this->redirect(['action' => $redirectAction]);
                    }
                }
            } else {
                // Fall back to application temp directory
                $uploadPath = TMP . 'uploads';
                if (!is_dir($uploadPath)) {
                    try {
                        if (!mkdir($uploadPath, 0777, true)) {
                            $this->log('Failed to create app temp upload directory: ' . $uploadPath, 'error');
                            $this->Flash->error(__('Upload system is not available. Please contact administrator.'));
                            return $this->redirect(['action' => $redirectAction]);
                        }
                    } catch (\Exception $e) {
                        $this->log('Exception creating app temp upload directory: ' . $e->getMessage(), 'error');
                        $this->Flash->error(__('Upload system error. Please contact administrator.'));
                        return $this->redirect(['action' => $redirectAction]);
                    }
                }
            }
        }
        
        // Verify directory is writable
        if (!is_writable($uploadPath)) {
            $this->log('Upload directory is not writable: ' . $uploadPath, 'error');
            $this->Flash->error(__('Upload system is temporarily unavailable. Please contact administrator.'));
            return $this->redirect(['action' => $redirectAction]);
        }

        // Generate safe filename
        $originalFilename = $file->getClientFilename() ?? 'document';
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalFilename);
        $filePath = $uploadPath . DS . $filename;
        
        try {
            // Move the uploaded file
            $file->moveTo($filePath);
            
            // Verify file was moved successfully
            if (!file_exists($filePath)) {
                $this->log('File was not moved successfully to: ' . $filePath, 'error');
                $this->Flash->error(__('File upload failed. Please try again.'));
                return $this->redirect(['action' => $redirectAction]);
            }
            
            // Set file permissions if possible
            try {
                chmod($filePath, 0644);
            } catch (\Exception $e) {
                // Don't fail if chmod fails, just log it
                $this->log('Could not set file permissions: ' . $e->getMessage(), 'warning');
            }
            
            $this->log('File uploaded successfully: ' . $filename . ' to: ' . $uploadPath, 'info');
            
            // Return relative path based on which directory was used
            if (strpos($uploadPath, WWW_ROOT . 'uploads') === 0) {
                // Standard web-accessible uploads directory
                return 'uploads/documents/' . $filename;
            } else {
                // Alternative directory - return the full path since it may not be web-accessible
                return $filePath;
            }
            
        } catch (\Exception $e) {
            $this->log('File upload exception: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('File upload failed. Please try again later.'));
            return $this->redirect(['action' => $redirectAction]);
        }
    }

    /**
     * Handle successful payment from Stripe
     *
     * @param string|null $id Writing Service Request id
     * @param string|null $sessionPaymentId Session payment identifier
     * @return \Cake\Http\Response|null
     */
    public function paymentSuccess(?string $id = null, ?string $sessionPaymentId = null)
    {
        // Determine if this is an AJAX request
        $isAjax = $this->request->is('ajax') || $this->request->is('json');

        if (empty($id) || empty($sessionPaymentId)) {
            if ($isAjax) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Invalid payment information.',
                    ]));
            }

            $this->Flash->error('Invalid payment information.');

            return $this->redirect(['action' => 'index']);
        }

        $this->log('Payment success received for request ' . $id . ' with session payment ID ' . $sessionPaymentId, 'debug');

        // Get the writing service request to ensure we have the correct price
        try {
            $writingServiceRequest = $this->WritingServiceRequests->get($id);
        } catch (Exception $e) {
            $this->log('Error retrieving writing service request: ' . $e->getMessage(), 'error');

            if ($isAjax) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Error retrieving writing service request.',
                    ]));
            }

            $this->Flash->error('Error processing payment: Unable to find the writing service request.');

            return $this->redirect(['action' => 'index']);
        }

        // Get payment data from session
        $paymentData = $this->request->getSession()->read("WsrPayments.$sessionPaymentId");

        // Skip if payment is already marked as paid
        if (!empty($paymentData) && isset($paymentData['status']) && $paymentData['status'] === 'paid') {
            $this->log("Payment $sessionPaymentId is already marked as paid", 'debug');

            if ($isAjax) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'paid' => true,
                        'message' => 'Payment already confirmed.',
                    ]));
            }

            $this->Flash->success('Payment has already been processed.');

            return $this->redirect(['action' => 'view', $id]);
        }

        // Get amount from session data
        $amount = $paymentData['amount'] ?? '0.00';

        // Create payment details with the amount from session
        $paymentDetails = [
            'transaction_id' => 'STRIPE-' . substr(md5($sessionPaymentId . time()), 0, 8),
            'amount' => $amount,
            'date' => time(),
            'status' => 'paid',
        ];

        $this->log('Created payment details with amount: ' . $paymentDetails['amount'], 'debug');

        // Update session data
        $this->request->getSession()->write("WsrPayments.$sessionPaymentId.status", 'paid');
        $this->request->getSession()->write("WsrPayments.$sessionPaymentId.receipt", $paymentDetails);

        // Extract database payment ID if available
        $dbPaymentId = $paymentData['db_payment_id'] ?? null;

        // Ensure dbPaymentId is a string if it's not null
        if ($dbPaymentId !== null && !is_string($dbPaymentId)) {
            $dbPaymentId = (string)$dbPaymentId;
            $this->log('Converted dbPaymentId to string: ' . $dbPaymentId, 'debug');
        }

        // Update database payment record
        $dbUpdated = $this->_updateDatabasePaymentStatus($id, $dbPaymentId, $sessionPaymentId, $paymentDetails);

        // Update request status if needed
        if ($writingServiceRequest->request_status === 'pending') {
            $writingServiceRequest->request_status = 'in_progress';
            $this->WritingServiceRequests->save($writingServiceRequest);
        }

        // Include the payment ID in the details
        if ($dbPaymentId) {
            $paymentDetails['payment_id'] = $dbPaymentId;
        }

        // Add payment confirmation message
        $this->_addPaymentConfirmationMessage($id, $paymentDetails);

        if ($isAjax) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'paid' => true,
                    'message' => 'Payment has been processed successfully.',
                    'details' => $paymentDetails,
                ]));
        }

        // Show success message
        $this->Flash->success('Payment completed successfully!');

        // Redirect to view page with success parameter
        return $this->redirect(['action' => 'view', $id, '?' => [
            'payment_success' => 'true',
            'paymentId' => $sessionPaymentId,
        ]]);
    }

    /**
     * Mark a payment as paid (admin action)
     *
     * @param string|null $id Writing Service Request id
     * @return \Cake\Http\Response|null
     */
    public function markAsPaid(?string $id = null)
    {
        $this->request->allowMethod(['post']);
        $this->disableAutoRender();
        $this->response = $this->response->withType('application/json');

        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Not authorized',
            ]));
        }

        try {
            // Get the writing service request
            $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: ['WritingServicePayments']);

            // Generate a unique session payment ID
            $sessionPaymentId = 'pay_' . uniqid();

            // Get amount from the request data or prompt for it
            $amount = $this->request->getData('amount');
            if (empty($amount) || !is_numeric($amount) || (float)$amount <= 0) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Please provide a valid payment amount',
                ]));
            }

            // Generate a transaction ID based on request ID
            $paymentDetails = [
                'amount' => $amount,
                'customer_id' => $user->user_id,
                'payment_date' => gmdate('Y-m-d H:i:s'),
                'payment_method' => 'cash',
                'transaction_id' => 'R-' . substr($id, -7),
                'receipt_number' => uniqid('rcpt_'),
                'status' => 'paid',
            ];

            // Update session data
            $this->request->getSession()->write("WsrPayments.$sessionPaymentId.status", 'paid');
            $this->request->getSession()->write("WsrPayments.$sessionPaymentId.receipt", $paymentDetails);
            $this->request->getSession()->write("WsrPayments.$sessionPaymentId.amount", (string)$amount);
            $this->request->getSession()->write(
                "WsrPayments.$sessionPaymentId.description",
                'Writing service: ' . $writingServiceRequest->service_title,
            );

            // Update database payment record or create new one
            $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');

            // Check if there's an existing payment record
            $existingPayment = $writingServicePaymentsTable->find()
                ->where(['writing_service_request_id' => $id])
                ->first();

            // Save the payment record and get the DB payment ID
            if ($existingPayment) {
                // Update existing record
                $existingPayment->status = 'paid';
                $existingPayment->transaction_id = $paymentDetails['transaction_id'];
                $existingPayment->payment_date = date('Y-m-d H:i:s');
                $existingPayment->amount = $amount;
                $writingServicePaymentsTable->save($existingPayment);
                $dbPaymentId = $existingPayment->writing_service_payment_id;
            } else {
                // Create new payment record
                $newPayment = $writingServicePaymentsTable->newEntity([
                    'writing_service_request_id' => $id,
                    'amount' => $amount,
                    'transaction_id' => $paymentDetails['transaction_id'],
                    'payment_date' => date('Y-m-d H:i:s'),
                    'payment_method' => 'admin_manual',
                    'status' => 'paid',
                ]);
                $writingServicePaymentsTable->save($newPayment);
                $dbPaymentId = $newPayment->writing_service_payment_id;
            }

            // Update request status if needed
            if ($writingServiceRequest->request_status === 'pending') {
                $writingServiceRequest->request_status = 'in_progress';
                $this->WritingServiceRequests->save($writingServiceRequest);
            }

            // Include the DB payment ID in the payment details
            $paymentDetails['payment_id'] = $dbPaymentId;

            // Add payment confirmation message
            $this->_addPaymentConfirmationMessage($id, $paymentDetails);

            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'Payment has been marked as paid',
                'paymentId' => $sessionPaymentId . '|' . $dbPaymentId,
            ]));
        } catch (Exception $e) {
            $this->log('Error marking payment as paid: ' . $e->getMessage(), 'error');

            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]));
        }
    }

    /**
     * Alternative payment method that uses query parameters instead of URL segments
     * This provides a more reliable way to handle payments when URL routing is causing issues
     *
     * @return \Cake\Http\Response|null
     */
    public function payDirect()
    {
        $id = $this->request->getQuery('id');
        $paymentId = $this->request->getQuery('paymentId');

        // Log all incoming data
        $this->log('payDirect method called with query parameters: ' . json_encode([
                'id' => $id,
                'paymentId' => $paymentId,
                'all_query' => $this->request->getQueryParams(),
                'request_url' => $this->request->getRequestTarget(),
                'referer' => $this->request->referer(),
            ]), 'debug');

        if (empty($id) || empty($paymentId)) {
            $this->Flash->error('Missing required payment information.');
            $this->log('payDirect error: Missing required parameters', 'error');

            return $this->redirect(['action' => 'index']);
        }

        try {
            // Check if the writing service request exists
            $writingServiceRequest = $this->WritingServiceRequests->get($id);

            // The payment ID should be just the database payment ID now
            $dbPaymentId = trim($paymentId);

            // Try to get payment data from database first (new system)
            $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');
            $paymentData = null;

            try {
                $paymentEntity = $writingServicePaymentsTable->get($dbPaymentId);

                // If payment is already paid, redirect to view with success message
                if ($paymentEntity->status === 'paid') {
                    $this->Flash->success(__('This payment has already been completed.'));

                    return $this->redirect(['action' => 'view', $id]);
                }

                // Create session data for this payment (required for the pay method)
                $sessionPaymentId = 'db_' . $dbPaymentId . '_' . time();
                $paymentData = [
                    'amount' => (string)$paymentEntity->amount,
                    'description' => 'Writing Service Payment',
                    'writing_service_request_id' => $id,
                    'created' => time(),
                    'status' => $paymentEntity->status,
                    'db_payment_id' => $dbPaymentId,
                ];

                // Store in session for the pay method to use
                $this->request->getSession()->write("WsrPayments.$sessionPaymentId", $paymentData);

                // Create combined payment ID for the pay method
                $combinedPaymentId = $sessionPaymentId . '|' . $dbPaymentId;

                $this->log('Created payment data from database record: ' . json_encode($paymentData), 'debug');

                // Call the regular pay method with the extracted parameters
                return $this->pay($id, $combinedPaymentId);
            } catch (Exception $e) {
                $this->log('Error retrieving payment from database: ' . $e->getMessage(), 'error');
            }

            // Fallback: Try session-based payment (old system)
            $parts = explode('|', urldecode($paymentId));
            $sessionPaymentId = $parts[0];
            $sessionPaymentData = $this->request->getSession()->read("WsrPayments.$sessionPaymentId");

            $this->log('Payment data retrieved from session: ' . json_encode([
                    'sessionPaymentId' => $sessionPaymentId,
                    'paymentData' => $sessionPaymentData,
                ]), 'debug');

            if ($sessionPaymentData) {
                // Call the regular pay method with the original parameters
                return $this->pay($id, $paymentId);
            }

            // If we reach here, we couldn't find payment data anywhere
            $this->Flash->error('Invalid payment request. Payment information not found.');

            return $this->redirect(['action' => 'view', $id]);
        } catch (Exception $e) {
            $this->log('payDirect error: ' . $e->getMessage(), 'error');
            $this->Flash->error('Error processing payment: ' . $e->getMessage());

            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Upload document for a writing service request
     *
     * @param string|null $id Writing Service Request id
     * @return \Cake\Http\Response|null
     */
    public function uploadDocument(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user) {
            $this->Flash->error(__('You need to be logged in.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        try {
            $writingServiceRequest = $this->WritingServiceRequests->get($id);

            // Check permissions - admin or owner of the request
            if ($user->user_type !== 'admin' && $user->user_id !== $writingServiceRequest->user_id) {
                $this->Flash->error(__('You do not have permission to upload documents to this request.'));

                return $this->redirect(['action' => 'index']);
            }

            $data = $this->request->getData();

            // Handle file upload
            $file = $this->request->getUploadedFile('document');

            if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
                $this->Flash->error(__('No document uploaded or upload failed.'));

                return $this->redirect(['action' => 'view', $id]);
            }

            // Process the document upload
            $documentPath = $this->_handleDocumentUpload($file, 'view');

            if ($documentPath) {
                // Create a RequestDocument entity
                $requestDocumentsTable = $this->fetchTable('RequestDocuments');
                $requestDocument = $requestDocumentsTable->newEmptyEntity();

                // Handle different types of document paths (web-accessible vs temp storage)
                $isWebAccessible = strpos($documentPath, 'uploads/') === 0;
                
                $data = [
                    'request_document_id' => Text::uuid(),
                    'writing_service_request_id' => $id,
                    'user_id' => $user->user_id,
                    'document_path' => $documentPath,
                    'document_name' => $file->getClientFilename(),
                    'file_type' => $file->getClientMediaType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $user->user_type,
                    'is_deleted' => false,
                    'created_at' => new DateTime('now'),
                ];

                // Skip validation for the writing_service_request_id field
                $requestDocument = $requestDocumentsTable->patchEntity($requestDocument, $data, [
                    'validate' => false,
                ]);

                if ($requestDocumentsTable->save($requestDocument)) {
                    // Add a message to the chat about the upload
                    $message = 'Uploaded document: **' . $file->getClientFilename() . '**';
                    if (!$isWebAccessible) {
                        $message .= ' *(stored in secure location)*';
                    }
                    
                    $requestMessagesTable = $this->fetchTable('RequestMessages');
                    $newMessage = $requestMessagesTable->newEntity([
                        'writing_service_request_id' => $id,
                        'user_id' => $user->user_id,
                        'message' => $message,
                        'is_read' => false,
                        'is_deleted' => false,
                    ]);
                    $requestMessagesTable->save($newMessage);

                    $this->Flash->success(__('Document uploaded successfully.'));
                } else {
                    $this->Flash->error(__('Document uploaded but could not be saved in the database.'));
                }
            } else {
                $this->Flash->error(__('Failed to upload document. Please try again.'));
            }

            return $this->redirect(['action' => 'view', $id]);
        } catch (Exception $e) {
            $this->Flash->error(__('Error: {0}', $e->getMessage()));

            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Simple AJAX test endpoint to verify AJAX is working
     *
     * @return \Cake\Http\Response
     */
    public function ajaxTest()
    {
        $this->request->allowMethod(['post', 'ajax']);
        $this->disableAutoRender();
        $this->response = $this->response->withType('application/json');

        try {
            $data = $this->request->getData();
            $this->log('AJAX test endpoint called with data: ' . json_encode($data), 'debug');
            
            return $this->response->withStringBody(json_encode([
                'success' => true,
                'message' => 'AJAX test successful',
                'received_data' => $data,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
        } catch (Exception $e) {
            $this->log('AJAX test error: ' . $e->getMessage(), 'error');
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'AJAX test failed: ' . $e->getMessage()
            ]));
        }
    }
}
