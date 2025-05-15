<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\WritingServiceRequest;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Routing\Router;
use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

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
            ->contain(['Users'])
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
                        ->order(['RequestMessages.created_at' => 'ASC']);
                },
                'WritingServicePayments' => function ($q) {
                    return $q->order(['WritingServicePayments.created_at' => 'DESC']);
                },
            ],
        );

        // Fetch appointments for this request to check if time slots have been accepted
        $appointmentsTable = $this->fetchTable('Appointments');
        $appointments = $appointmentsTable->find()
            ->where([
                'user_id' => $user->user_id,
                'is_deleted' => false,
            ])
            ->toArray();

        // Mark messages from admin as read when customer views them
        $this->markMessagesAsRead($writingServiceRequest, $user->user_id);

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            if (!empty($data['reply_message'])) {
                $data['request_messages'][] = [
                    'user_id' => $user->user_id,
                    'message' => $data['reply_message'],
                    'is_read' => false, // Initially not read by the admin
                ];

                $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
                    $writingServiceRequest,
                    $data,
                );

                if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                    $this->Flash->success(__('Message sent successfully.'));

                    // If the request status is pending, update it to in_progress
                    if ($writingServiceRequest->request_status === 'pending') {
                        $writingServiceRequest->request_status = 'in_progress';
                        $this->WritingServiceRequests->save($writingServiceRequest);
                    }

                    return $this->redirect(['action' => 'view', $id]);
                } else {
                    $this->Flash->error(__('Failed to send message. Please try again.'));
                }
            }
        }

        $this->set(compact('writingServiceRequest', 'appointments'));
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
                $writingServiceRequest = $this->WritingServiceRequests->get($id, [
                    'contain' => [
                        'RequestMessages' => function ($q) use ($lastMessageId) {
                            $query = $q->contain(['Users'])
                                ->order(['RequestMessages.created_at' => 'ASC']);

                            if (!empty($lastMessageId)) {
                                // Only get messages newer than the lastMessageId
                                $query->where(['RequestMessages.request_message_id >' => $lastMessageId]);
                            }

                            return $query;
                        },
                    ],
                ]);

                // Format messages for JSON response
                $messages = [];
                if (!empty($writingServiceRequest->request_messages)) {
                    foreach ($writingServiceRequest->request_messages as $message) {
                        $isAdmin = isset($message->user) && $message->user->user_type === 'admin';
                        $timeFormatted = $message->created_at->format('M j, Y g:i A');

                        $messages[] = [
                            'id' => $message->request_message_id,
                            'content' => $message->message,
                            'sender' => $isAdmin ? 'admin' : 'client',
                            'senderName' => $isAdmin ? 'Admin' : ($message->user->first_name . ' ' . $message->user->last_name),
                            'timestamp' => $timeFormatted,
                            'is_read' => (bool)$message->is_read,
                            'created_at' => $message->created_at->format('c'),
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
                } catch (\Exception $e) {
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
                } catch (\Exception $e) {
                    // Log error but don't block the response
                    $this->log('Warning: Could not get WritingServiceRequest: ' . $e->getMessage(), 'warning');
                }

                // Update session data
                $this->request->getSession()->write("WsrPayments.$sessionPaymentId.status", 'paid');
                $this->request->getSession()->write("WsrPayments.$sessionPaymentId.receipt", $paymentDetails);

                // Try to update database, but don't fail if can't
                try {
                    $this->_updateDatabasePaymentStatus($id, $dbPaymentId, $sessionPaymentId, $paymentDetails);
                } catch (\Exception $e) {
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
                } catch (\Exception $e) {
                    $this->log('Error fetching payment record: ' . $e->getMessage(), 'warning');
                }
            } catch (\Exception $e) {
                $this->log('WritingServicePayments table not available: ' . $e->getMessage(), 'warning');
            }
        }

        // Last resort - check for session parameters that indicate success
        if (!$isPaid && $paymentData) {
            // If we got here, we should check if the request has a final price and is not in pending status
            try {
                $writingServiceRequest = $this->WritingServiceRequests->get($id, [
                    'contain' => ['RequestMessages'],
                ]);

                // NEW: Check for payment confirmation messages in the messages
                $hasPaymentConfirmation = false;
                if (!empty($writingServiceRequest->request_messages)) {
                    foreach ($writingServiceRequest->request_messages as $message) {
                        // Only consider it confirmed if the confirmation message contains THIS payment ID
                        if (strpos($message->message, '[PAYMENT_CONFIRMATION]') !== false) {
                            // Check if this specific payment ID is mentioned in the confirmation message
                            if (strpos($message->message, "Payment ID: $sessionPaymentId") !== false ||
                                strpos($message->message, "Payment ID:$sessionPaymentId") !== false ||
                                ($dbPaymentId && (strpos($message->message, "Payment ID: $dbPaymentId") !== false ||
                                                strpos($message->message, "Payment ID:$dbPaymentId") !== false))) {
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
                        } catch (\Exception $e) {
                            // Log error but continue
                            $this->log('Error in DB update: ' . $e->getMessage(), 'error');
                        }
                    }
                }
            } catch (\Exception $e) {
                // If we can't get the writing service request, still provide a usable response
                $this->log('Error getting final status: ' . $e->getMessage(), 'error');
            }
        }

        // If payment is marked as paid, add a confirmation message
        if ($isPaid && $status === 'paid') {
            try {
                $this->_addPaymentConfirmationMessage($id, $paymentDetails);
            } catch (\Exception $e) {
                // Log error but continue
                $this->log('Error adding confirmation message: ' . $e->getMessage(), 'error');
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

            // Get the writing service request to ensure we have the correct final price
            $writingServiceRequest = $this->WritingServiceRequests->get($requestId);

            // Use the final price from the request as the authoritative source
            $amount = !empty($writingServiceRequest->final_price)
                ? (string)$writingServiceRequest->final_price
                : ($paymentDetails['amount'] ?? '0.00');

            $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');

            // Try to find existing payment record
            if ($dbPaymentId && $dbPaymentId !== 'pending') {
                try {
                    $paymentEntity = $writingServicePaymentsTable->get($dbPaymentId);
                } catch (\Exception $e) {
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
                $paymentEntity->amount = $amount; // Always use the authoritative amount
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
        } catch (\Exception $e) {
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
                'request_url' => $this->request->getRequestTarget(),
            ]), 'debug');

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
            $writingServiceRequest = $this->WritingServiceRequests->get($id, [
                'contain' => ['Users'],
            ]);
        } catch (\Exception $e) {
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
                } catch (\Exception $e) {
                    $this->log('Error fetching payment record: ' . $e->getMessage(), 'error');
                    // Continue with session data
                }
            } catch (\Exception $e) {
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
                ['controller' => 'WritingServiceRequests', 'action' => 'paymentSuccess', $id, $sessionPaymentId],
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
        } catch (\Exception $e) {
            $this->log('Stripe error: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), 'error');
            $this->Flash->error(__('There was an error processing your payment: ') . $e->getMessage());

            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Add a payment confirmation message to the chat
     *
     * @param string $requestId The writing service request ID
     * @param array $paymentDetails Payment details
     * @return bool True if successful, false otherwise
     */
    protected function _addPaymentConfirmationMessage(string $requestId, array $paymentDetails): bool
    {
        // Only add confirmation if we have the necessary data
        if (empty($requestId) || empty($paymentDetails)) {
            return false;
        }

        try {
            // Get the request
            $writingServiceRequest = $this->WritingServiceRequests->get($requestId);

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

            // Save the message
            return (bool)$this->WritingServiceRequests->save($writingServiceRequest);
        } catch (\Exception $e) {
            $this->log('Error adding payment confirmation message: ' . $e->getMessage(), 'error');

            return false;
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
            ->contain(['Users']);

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
                        ->order(['RequestMessages.created_at' => 'ASC']);
                },
                'WritingServicePayments' => function ($q) {
                    return $q->order(['WritingServicePayments.created_at' => 'DESC']);
                },
            ],
        );

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

        $this->set(compact('writingServiceRequest'));
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
            $writingServiceRequest = $this->WritingServiceRequests->get($id, [
                'contain' => ['Users'],
            ]);
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
            'image/jpeg',       // JPG/JPEG
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
        ];

        if (!in_array($file->getClientMediaType(), $allowedMimeTypes)) {
            $this->Flash->error(__('Invalid file type. Please upload txt, pdf, or Word documents only.'));

            return $this->redirect(['action' => $redirectAction]);
        }

        $uploadPath = WWW_ROOT . 'uploads' . DS . 'documents';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $file->getClientFilename() ?? '');
        $filePath = $uploadPath . DS . $filename;
        $file->moveTo($filePath);

        return 'uploads/documents/' . $filename;
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
        if (empty($id) || empty($sessionPaymentId)) {
            $this->Flash->error('Invalid payment information.');

            return $this->redirect(['action' => 'index']);
        }

        $this->log('Payment success received for request ' . $id . ' with session payment ID ' . $sessionPaymentId, 'debug');

        // Get the writing service request to ensure we have the correct price
        try {
            $writingServiceRequest = $this->WritingServiceRequests->get($id);
        } catch (\Exception $e) {
            $this->log('Error retrieving writing service request: ' . $e->getMessage(), 'error');
            $this->Flash->error('Error processing payment: Unable to find the writing service request.');

            return $this->redirect(['action' => 'index']);
        }

        // Get payment data from session
        $paymentData = $this->request->getSession()->read("WsrPayments.$sessionPaymentId");

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

        // Update database
        // Convert dbPaymentId to string again just to be safe
        if ($dbPaymentId !== null && !is_string($dbPaymentId)) {
            $dbPaymentId = (string)$dbPaymentId;
        }
        $this->_updateDatabasePaymentStatus($id, $dbPaymentId, $sessionPaymentId, $paymentDetails);

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

        // Show success message
        $this->Flash->success('Payment completed successfully!');

        // Redirect to view page with success parameter
        return $this->redirect(['action' => 'view', $id, '?' => ['payment_success' => true]]);
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
            $writingServiceRequest = $this->WritingServiceRequests->get($id);

            // Generate a unique session payment ID
            $sessionPaymentId = 'pay_' . uniqid();
            
            // Get amount from the request
            $amount = $writingServiceRequest->final_price ?? '0.00';

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
        } catch (\Exception $e) {
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

            // Check if we can find the payment data in the session
            $parts = explode('|', urldecode($paymentId));
            $sessionPaymentId = $parts[0];
            $paymentData = $this->request->getSession()->read("WsrPayments.$sessionPaymentId");

            $this->log('Payment data retrieved from session: ' . json_encode([
                    'sessionPaymentId' => $sessionPaymentId,
                    'paymentData' => $paymentData,
                ]), 'debug');

            // If payment data doesn't exist in the session, try to get it from the database
            if (!$paymentData && isset($parts[1])) {
                $dbPaymentId = $parts[1];

                // Get payment data from database
                $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');
                try {
                    $paymentEntity = $writingServicePaymentsTable->get($dbPaymentId);

                    // Create session data for this payment
                    $paymentData = [
                        'amount' => (string)$paymentEntity->amount,
                        'description' => 'Writing service payment',
                        'writing_service_request_id' => $id,
                        'created' => time(),
                        'status' => $paymentEntity->status,
                        'db_payment_id' => $dbPaymentId,
                    ];

                    $this->request->getSession()->write("WsrPayments.$sessionPaymentId", $paymentData);
                    $this->log('Created payment data from database record: ' . json_encode($paymentData), 'debug');
                } catch (\Exception $e) {
                    $this->log('Error retrieving payment from database: ' . $e->getMessage(), 'error');
                }
            }

            // If we still don't have payment data, fail
            if (!$paymentData) {
                $this->Flash->error('Invalid payment request. Payment information not found.');

                return $this->redirect(['action' => 'view', $id]);
            }

            // Call the regular pay method with the extracted parameters
            return $this->pay($id, $paymentId);
        } catch (\Exception $e) {
            $this->log('payDirect error: ' . $e->getMessage(), 'error');
            $this->Flash->error('Error processing payment: ' . $e->getMessage());

            return $this->redirect(['action' => 'view', $id]);
        }
    }
}
