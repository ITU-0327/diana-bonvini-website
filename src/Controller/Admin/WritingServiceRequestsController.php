<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController as BaseAdminController;
use App\Mailer\PaymentMailer;
use App\Model\Entity\WritingServiceRequest;
use App\Service\GoogleCalendarService;
use Cake\Http\Response;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use DateTime;
use DateTimeZone;
use Exception;
use Psr\Http\Message\UploadedFileInterface;

/**
 * WritingServiceRequests Controller (Admin prefix)
 *
 * Admin interface for managing writing service requests.
 * Uses dedicated admin templates.
 *
 * @property \App\Model\Table\WritingServiceRequestsTable $WritingServiceRequests
 */
class WritingServiceRequestsController extends BaseAdminController
{
    /**
     * Index method - Shows all writing service requests with admin functionality
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $query = $this->WritingServiceRequests->find()
            ->contain([
                'Users' => function ($q) {
                    return $q->where(['Users.is_deleted' => false]);
                },
                'WritingServicePayments' => function ($q) {
                    return $q->orderBy(['WritingServicePayments.created_at' => 'DESC']);
                },
            ])
            ->where(['WritingServiceRequests.is_deleted' => false])
            ->orderBy(['WritingServiceRequests.created_at' => 'DESC']);

        $writingServiceRequests = $this->paginate($query);

        // Count all unread messages across all requests
        $totalUnreadCount = $this->WritingServiceRequests->RequestMessages->find()
            ->where([
                'is_read' => false,
                'user_id NOT IN' => $this->WritingServiceRequests->Users->find()
                    ->select(['user_id'])
                    ->where(['user_type' => 'admin']),
            ])
            ->count();

        // Calculate statistics using fresh queries for each count
        $totalRequests = $this->WritingServiceRequests->find()
            ->where(['WritingServiceRequests.is_deleted' => false])
            ->count();

        $pendingRequests = $this->WritingServiceRequests->find()
            ->where([
                'WritingServiceRequests.is_deleted' => false,
                'WritingServiceRequests.request_status' => 'pending',
            ])
            ->count();

        $inProgressRequests = $this->WritingServiceRequests->find()
            ->where([
                'WritingServiceRequests.is_deleted' => false,
                'WritingServiceRequests.request_status' => 'in_progress',
            ])
            ->count();

        $totalRevenue = $this->WritingServiceRequests->WritingServicePayments->find()
            ->where([
                'WritingServicePayments.status' => 'paid',
            ])
            ->select(['total' => $this->WritingServiceRequests->WritingServicePayments->find()->func()->sum('WritingServicePayments.amount')])
            ->first()
            ->total ?? 0;

        $this->set(compact(
            'writingServiceRequests',
            'totalUnreadCount',
            'totalRequests',
            'pendingRequests',
            'inProgressRequests',
            'totalRevenue',
        ));
    }

    /**
     * View method - Shows details of a writing service request
     *
     * @param string|null $id WritingServiceRequest id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: [
            'Users',
            'RequestMessages' => function ($q) {
                return $q->contain(['Users'])
                    ->orderBy(['RequestMessages.created_at' => 'ASC']);
            },
            'WritingServicePayments' => function ($q) {
                return $q
                    ->orderBy(['WritingServicePayments.created_at' => 'DESC']);
            },
        ]);

        // Fetch request documents
        $requestDocumentsTable = $this->fetchTable('RequestDocuments');
        $requestDocuments = $requestDocumentsTable->find()
            ->where([
                'writing_service_request_id' => $id,
                'is_deleted' => false,
            ])
            ->orderBy(['created_at' => 'DESC'])
            ->toArray();

        // Mark messages from client as read when admin views them
        $this->markMessagesAsRead($writingServiceRequest, $user->user_id);

        // Handle admin reply
        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            if (!empty($data['message_text'])) {
                $data['request_messages'][] = [
                    'user_id' => $user->user_id,
                    'message' => $data['message_text'],
                    'is_read' => false, // Initially not read by the client
                    'is_deleted' => false, // Ensure is_deleted is set
                    'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                ];

                $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
                    $writingServiceRequest,
                    $data,
                );

                if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                    $this->Flash->success(__('Reply sent successfully.'));

                    // If the request status is pending, update it to in_progress
                    if ($writingServiceRequest->request_status === 'pending') {
                        $writingServiceRequest->request_status = 'in_progress';
                        $this->WritingServiceRequests->save($writingServiceRequest);
                        $this->Flash->info(__('Request status updated to "In Progress"'));
                    }

                    // Send email notification to customer
                    try {
                        // Get a fresh copy of the request with user data
                        $requestWithUser = $this->WritingServiceRequests->get($id, contain: ['Users']);

                        if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                            $adminName = 'Diana Bonvini';

                            // Send customer notification
                            $mailer = new PaymentMailer('default');
                            $mailer->customerMessageNotification(
                                $requestWithUser,
                                $data['message_text'],
                                $adminName,
                            );
                            $result = $mailer->deliverAsync();

                            if ($result) {
                                $this->log('Customer message notification sent successfully to ' . $requestWithUser->user->email, 'info');
                            } else {
                                $this->log('Customer message notification failed to send to ' . $requestWithUser->user->email, 'warning');
                            }
                        }
                    } catch (Exception $e) {
                        $this->log('Error sending customer message notification: ' . $e->getMessage(), 'error');
                    }

                    return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
                } else {
                    $this->Flash->error(__('Failed to send reply. Please try again.'));
                }
            }
        }

        $this->set(compact('writingServiceRequest', 'requestDocuments'));
    }

    /**
     * Update request status method
     *
     * @param string|null $id WritingServiceRequest id.
     * @return \Cake\Http\Response|null Redirects to view page
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function updateStatus(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'put']);

        $writingServiceRequest = $this->WritingServiceRequests->get($id);
        $status = $this->request->getData('status');

        if (empty($status)) {
            $this->Flash->error(__('No status provided.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $writingServiceRequest->request_status = $status;

        if ($this->WritingServiceRequests->save($writingServiceRequest)) {
            $this->Flash->success(__('The request status has been updated.'));

            // Add automatic message about status change
            if ($this->sendStatusChangeMessage($writingServiceRequest, $status)) {
                $this->Flash->info(__('Status change notification sent to client.'));
            }
        } else {
            $this->Flash->error(__('The request status could not be updated. Please, try again.'));
        }

        return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
    }

    /**
     * Send a message notifying the client about a status change
     *
     * @param \App\Model\Entity\WritingServiceRequest $writingServiceRequest The writing service request
     * @param string $status The new status
     * @return bool Success flag
     */
    private function sendStatusChangeMessage(WritingServiceRequest $writingServiceRequest, string $status): bool
    {
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();

        $statusMessage = match ($status) {
            'in_progress' => 'Your request is now in progress. We are actively working on it.',
            'completed' => 'Great news! Your request has been marked as completed.',
            default => 'The status of your request has been updated to: ' . Inflector::humanize($status)
        };

        $data = [
            'request_messages' => [
                [
                    'user_id' => $admin->user_id,
                    'message' => 'Status Update: ' . $statusMessage,
                    'is_read' => false,
                    'is_deleted' => false, // Ensure is_deleted is set
                    'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                ],
            ],
        ];

        $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
            $writingServiceRequest,
            $data,
        );

        return (bool)$this->WritingServiceRequests->save($writingServiceRequest);
    }

    /**
     * Set price for a writing service request
     *
     * @param string|null $id WritingServiceRequest id.
     * @return \Cake\Http\Response|null Redirects to view page
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function setPrice(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'put']);

        $writingServiceRequest = $this->WritingServiceRequests->get($id);
        $price = $this->request->getData('final_price');

        if (empty($price) || !is_numeric($price) || $price < 0) {
            $this->Flash->error(__('Please provide a valid price amount.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $writingServiceRequest->final_price = (float)$price;

        if ($this->WritingServiceRequests->save($writingServiceRequest)) {
            $this->Flash->success(__('The price has been set successfully.'));

            // Add automatic message about price update
            $this->sendPriceUpdateMessage($writingServiceRequest, $price);
        } else {
            $this->Flash->error(__('The price could not be updated. Please, try again.'));
        }

        return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
    }

    /**
     * Send a message notifying the client about a price update
     *
     * @param \App\Model\Entity\WritingServiceRequest $writingServiceRequest The writing service request
     * @param float $price The price amount
     * @return void Success flag
     */
    private function sendPriceUpdateMessage(WritingServiceRequest $writingServiceRequest, float $price): void
    {
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();

        $formattedPrice = '$' . number_format($price, 2);
        $message = "Price Update: We've set the price for your request at $formattedPrice. ";
        $message .= "If you'd like to proceed, please reply to this message or use the payment option that will be added soon.";

        $data = [
            'request_messages' => [
                [
                    'user_id' => $admin->user_id,
                    'message' => $message,
                    'is_read' => false,
                    'is_deleted' => false, // Ensure is_deleted is set
                    'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                ],
            ],
        ];

        $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
            $writingServiceRequest,
            $data,
        );

        $this->WritingServiceRequests->save($writingServiceRequest);
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
            // Only mark messages from other users (non-admin) as read when viewed by admin
            if ($message->user_id !== $userId && !$message->is_read) {
                $message->is_read = true;
                $requestMessagesTable->save($message);
                $updatedCount++;
            }
        }

        // Log how many messages were marked as read for debugging
        if ($updatedCount > 0) {
            $this->log("Marked $updatedCount messages as read for admin user $userId", 'info');
        }
    }

    /**
     * Send payment request to client
     *
     * @param string|null $id Writing Service Request id.
     * @return \Cake\Http\Response|null
     */
    public function sendPaymentRequest(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();
        $writingServiceRequest = $this->WritingServiceRequests->get($id);

        $data = $this->request->getData();
        $amount = $data['amount'] ?? null;
        $description = $data['description'] ?? 'Writing service fee';

        // Validate amount
        if (empty($amount) || !is_numeric($amount) || (float)$amount <= 0) {
            $this->Flash->error(__('Please provide a valid payment amount.'));

            return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
        }

        $amount = (float)$amount;
        $formattedAmount = '$' . number_format($amount, 2);

        try {
            // Create payment record in database
            $writingServicePaymentsTable = $this->fetchTable('WritingServicePayments');
            $paymentEntity = $writingServicePaymentsTable->newEntity([
                'writing_service_request_id' => $id,
                'amount' => $amount,
                'transaction_id' => null, // Will be filled when payment is completed
                'payment_method' => 'stripe',
                'status' => 'pending',
                'is_deleted' => false,
            ]);

            if (!$writingServicePaymentsTable->save($paymentEntity)) {
                $errors = $paymentEntity->getErrors();
                $this->log('Failed to create payment record. Errors: ' . json_encode($errors), 'error');
                $this->log('Payment entity data: ' . json_encode($paymentEntity->toArray()), 'error');
                $this->Flash->error(__('Failed to create payment request. Please try again.'));

                return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
            }

            // Create payment ID for the payment button
            $paymentId = $paymentEntity->writing_service_payment_id;

            // Create message with payment button
            $messageText = "**Payment Request**\n\n";
            $messageText .= '**Service:** ' . $description . "\n";
            $messageText .= '**Amount:** ' . $formattedAmount . "\n\n";
            $messageText .= "Please click the button below to complete your payment. Once payment is processed, you'll receive a confirmation and we'll continue with your request.\n\n";
            $messageText .= '[PAYMENT_BUTTON]' . $paymentId . '[/PAYMENT_BUTTON]';

            // Save the message
            $messageData = [
                'request_messages' => [
                    [
                        'user_id' => $admin->user_id,
                        'message' => $messageText,
                        'is_read' => false,
                        'is_deleted' => false, // Ensure is_deleted is set
                        'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                    ],
                ],
            ];

            $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
                $writingServiceRequest,
                $messageData,
            );

            if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                $this->Flash->success(__('Payment request has been sent to the client.'));

                // Send email notification to customer
                try {
                    // Get the writing service request with user information for email
                    $requestWithUser = $this->WritingServiceRequests->get($id, contain: ['Users']);

                    // Create and send payment request email
                    $mailer = new PaymentMailer('default');
                    $mailer->paymentRequest($requestWithUser, $paymentEntity, $amount);
                    $result = $mailer->deliverAsync();

                    if ($result) {
                        $this->log('Payment request email sent successfully to ' . $requestWithUser->user->email, 'info');
                    } else {
                        $this->log('Payment request email failed to send to ' . $requestWithUser->user->email, 'warning');
                    }
                } catch (Exception $emailException) {
                    // Log email error but don't fail the payment request creation
                    $this->log('Error sending payment request email: ' . $emailException->getMessage(), 'error');
                }
            } else {
                $messageErrors = $writingServiceRequest->getErrors();
                $this->log('Failed to save payment request message. Errors: ' . json_encode($messageErrors), 'error');
                $this->Flash->error(__('Failed to send payment request message. Please try again.'));
            }
        } catch (Exception $e) {
            $this->log('Error creating payment request: ' . $e->getMessage(), 'error');
            $this->log('Stack trace: ' . $e->getTraceAsString(), 'error');
            $this->Flash->error(__('An error occurred while creating the payment request. Please try again.'));
        }

        return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
    }

    /**
     * Send message to client
     *
     * @param string|null $id Writing Service Request id.
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

        $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: ['Users']);

        $data = $this->request->getData();
        $messageText = $data['message_text'] ?? '';

        if (empty(trim($messageText))) {
            $this->Flash->error(__('Please enter a message.'));

            return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
        }

        // Prepare message data
        $messageData = [
            'request_messages' => [
                [
                    'user_id' => $admin->user_id,
                    'message' => $messageText,
                    'is_read' => false, // Initially not read by the client
                    'is_deleted' => false, // Ensure is_deleted is set
                    'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                ],
            ],
        ];

        // Add the message to the request
        $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
            $writingServiceRequest,
            $messageData,
        );

        if ($this->WritingServiceRequests->save($writingServiceRequest)) {
            $this->Flash->success(__('Message sent successfully.'));

            // If the request status is pending, update it to in_progress
            if ($writingServiceRequest->request_status === 'pending') {
                $writingServiceRequest->request_status = 'in_progress';
                $this->WritingServiceRequests->save($writingServiceRequest);
            }

            // Send email notification to customer
            try {
                // Get a fresh copy of the request with user data
                $requestWithUser = $this->WritingServiceRequests->get($id, contain: ['Users']);

                if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                    $adminName = 'Diana Bonvini';

                    // Send customer notification
                    $mailer = new PaymentMailer('default');
                    $mailer->customerMessageNotification(
                        $requestWithUser,
                        $messageText,
                        $adminName,
                    );
                    $result = $mailer->deliverAsync();

                    if ($result) {
                        $this->log('Customer message notification sent successfully to ' . $requestWithUser->user->email, 'info');
                    } else {
                        $this->log('Customer message notification failed to send to ' . $requestWithUser->user->email, 'warning');
                    }
                }
            } catch (Exception $e) {
                $this->log('Error sending customer message notification: ' . $e->getMessage(), 'error');
            }
        } else {
            $this->Flash->error(__('Failed to send message. Please try again.'));
        }

        return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
    }

    /**
     * Fetch new messages via AJAX
     *
     * @param string|null $id Writing Service Request id
     * @param string|null $lastMessageId Last message ID for incremental fetching
     * @return \Cake\Http\Response|null
     */
    public function fetchMessages(?string $id = null, ?string $lastMessageId = null)
    {
        $this->request->allowMethod(['get', 'ajax']);

        if ($this->request->is('ajax')) {
            $this->disableAutoRender();
            $this->response = $this->response->withType('application/json');

            if (empty($id)) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Request ID is required',
                ]));
            }

            /** @var \App\Model\Entity\User|null $user */
            $user = $this->Authentication->getIdentity();

            if (!$user || $user->user_type !== 'admin') {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Authentication required',
                ]));
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

                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'messages' => $messages,
                    'count' => count($messages),
                ]));
            } catch (Exception $e) {
                return $this->response->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ]));
            }
        }

        return $this->response->withStringBody(json_encode([
            'success' => false,
            'message' => 'Invalid request',
        ]));
    }

    /**
     * Test method to check payment status by ID
     * This is a development/debugging endpoint
     *
     * @param string|null $paymentId The payment ID to check
     * @return \Cake\Http\Response|null
     */
    public function testPaymentStatus(?string $paymentId = null)
    {
        $this->disableAutoRender();
        $this->response = $this->response->withType('application/json');

        if (!$paymentId) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'No payment ID provided',
            ]));
        }

        // Check if this is a combined ID
        $parts = explode('|', $paymentId);
        $sessionPaymentId = $parts[0] ?? null;
        $dbPaymentId = $parts[1] ?? null;

        $result = [
            'paymentId' => $paymentId,
            'sessionId' => $sessionPaymentId,
            'dbId' => $dbPaymentId,
            'sessionData' => null,
            'dbData' => null,
        ];

        // Check session data
        if ($sessionPaymentId) {
            $sessionData = $this->request->getSession()->read("WsrPayments.$sessionPaymentId");
            $result['sessionData'] = $sessionData;
        }

        // Check database data
        if ($dbPaymentId && $dbPaymentId !== 'pending') {
            try {
                $paymentTable = $this->fetchTable('WritingServicePayments');
                $payment = $paymentTable->find()
                    ->where(['writing_service_payment_id' => $dbPaymentId])
                    ->first();

                if ($payment) {
                    $result['dbData'] = [
                        'id' => $payment->writing_service_payment_id,
                        'request_id' => $payment->writing_service_request_id,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'payment_date' => $payment->payment_date,
                        'transaction_id' => $payment->transaction_id,
                    ];
                }
            } catch (Exception $e) {
                $result['error'] = $e->getMessage();
            }
        }

        // Determine overall status
        $isPaid = false;

        if (isset($result['dbData']['status']) && $result['dbData']['status'] === 'paid') {
            $isPaid = true;
        } elseif (isset($result['sessionData']['status']) && $result['sessionData']['status'] === 'paid') {
            $isPaid = true;
        }

        $result['isPaid'] = $isPaid;

        return $this->response->withStringBody(json_encode($result));
    }

    /**
     * Get available time slots from Google Calendar for a specific date
     *
     * @return \Cake\Http\Response|null
     */
    public function getAvailableTimeSlots()
    {
        $this->request->allowMethod(['get', 'ajax']);

        // Return JSON response
        $this->viewBuilder()->setClassName('Json');
        $this->viewBuilder()->setOption('serialize', ['success', 'timeSlots']);

        $success = false;
        $timeSlots = [];

        // Get date from query parameter
        $date = $this->request->getQuery('date');

        $this->log('getAvailableTimeSlots called with date: ' . $date, 'debug');

        if (!empty($date) && strtotime($date)) {
            /** @var \App\Model\Entity\User $user */
            $user = $this->Authentication->getIdentity();
            $userId = $user->user_id;

            try {
                // Initialize the GoogleCalendarService
                $googleCalendarService = new GoogleCalendarService();

                // Check if we have active Google Calendar settings for this user
                $googleCalendarSettings = null;

                if (isset($this->GoogleCalendarSettings)) {
                    try {
                        $googleCalendarSettings = $this->GoogleCalendarSettings->find()
                            ->where(['user_id' => $userId, 'is_active' => true])
                            ->first();
                    } catch (Exception $e) {
                        $this->log('Error retrieving Google Calendar settings: ' . $e->getMessage(), 'error');
                    }
                }

                $dateObj = new DateTime($date, new DateTimeZone(date_default_timezone_get()));

                // Define working hours (24 hours a day)
                $workingHours = [
                    'start' => '00:00',
                    'end' => '23:59',
                ];

                if ($googleCalendarSettings) {
                    $this->log('Using Google Calendar for time slots', 'debug');

                    // Use Google Calendar to get free time slots
                    try {
                        $timeSlots = $googleCalendarService->getFreeTimeSlots($userId, $dateObj, $workingHours);
                        $success = true;
                        $this->log('Retrieved ' . count($timeSlots) . ' time slots from Google Calendar', 'debug');
                    } catch (Exception $e) {
                        $this->log('Error retrieving time slots from Google Calendar: ' . $e->getMessage(), 'error');
                        // Fall back to mock data
                        $timeSlots = $this->createMockTimeSlots($dateObj);
                        $success = true;
                    }
                } else {
                    $this->log('No Google Calendar settings found, using mock data', 'debug');

                    // No Google Calendar settings, use mock data
                    $timeSlots = $this->createMockTimeSlots($dateObj);
                    $success = true;
                }
            } catch (Exception $e) {
                $this->log('Error in getAvailableTimeSlots: ' . $e->getMessage(), 'error');
                // Always provide some mock data as fallback
                try {
                    $dateObj = new DateTime($date, new DateTimeZone(date_default_timezone_get()));
                    $timeSlots = $this->createMockTimeSlots($dateObj);
                    $success = true;
                } catch (Exception $innerEx) {
                    $this->log('Error creating mock time slots: ' . $innerEx->getMessage(), 'error');
                    // Even if all else fails, create a few basic time slots
                    $timeSlots = $this->createBasicTimeSlots($date);
                }
            }
        } else {
            $this->log('Invalid date parameter: ' . $date, 'error');
            // Return some basic slots for the current date as fallback
            $timeSlots = $this->createBasicTimeSlots(date('Y-m-d'));
        }

        // Ensure we always return something
        if (empty($timeSlots)) {
            $timeSlots = $this->createBasicTimeSlots(date('Y-m-d'));
            $success = true;
        }

        // Set response variables
        $this->set('success', $success);
        $this->set('timeSlots', $timeSlots);
    }

    /**
     * Create mock time slots for a given date
     *
     * @param \DateTime $date The date to create slots for
     * @return array The mock time slots
     */
    private function createMockTimeSlots(DateTime $date)
    {
        // Create mock slots every hour from midnight to 11 PM
        $slots = [];
        $startHour = 0;   // Midnight
        $endHour = 24;    // End of day

        $this->log('Creating mock time slots for date: ' . $date->format('Y-m-d'), 'debug');

        $slotDate = clone $date;

        for ($hour = $startHour; $hour < $endHour; $hour++) {
            // Skip some slots randomly to simulate busy times (reduce the skip rate for more availability)
            if (rand(0, 100) < 15) { // Only 15% chance of being unavailable
                    continue;
            }

            $slotDate->setTime($hour, 0, 0); // Start at the top of each hour

                $startTime = $slotDate->format('H:i');
            $slotDate->modify('+1 hour');
                $endTime = $slotDate->format('H:i');

            // Format for display (12-hour format)
            $startTimeFormatted = $slotDate->setTime($hour, 0, 0)->format('g:i A');
            $endTimeFormatted = $slotDate->modify('+1 hour')->format('g:i A');

                $slots[] = [
                    'date' => $date->format('Y-m-d'),
                    'start' => $startTime,
                    'end' => $endTime,
                'formatted' => $startTimeFormatted . ' - ' . $endTimeFormatted,
                ];
        }

        $this->log('Created ' . count($slots) . ' mock time slots', 'debug');

        return $slots;
    }

    /**
     * Create very basic time slots for a given date string
     * This is used as a last-resort fallback when all other methods fail
     *
     * @param string $dateString Date string in Y-m-d format
     * @return array Array of basic time slots
     */
    private function createBasicTimeSlots(string $dateString): array
    {
        $slots = [];

        // Create hourly slots throughout the day as fallback
        $availableHours = [
            ['start' => '00:00', 'end' => '01:00', 'formatted' => '12:00 AM - 1:00 AM'],
            ['start' => '02:00', 'end' => '03:00', 'formatted' => '2:00 AM - 3:00 AM'],
            ['start' => '06:00', 'end' => '07:00', 'formatted' => '6:00 AM - 7:00 AM'],
            ['start' => '08:00', 'end' => '09:00', 'formatted' => '8:00 AM - 9:00 AM'],
            ['start' => '09:00', 'end' => '10:00', 'formatted' => '9:00 AM - 10:00 AM'],
            ['start' => '10:00', 'end' => '11:00', 'formatted' => '10:00 AM - 11:00 AM'],
            ['start' => '11:00', 'end' => '12:00', 'formatted' => '11:00 AM - 12:00 PM'],
            ['start' => '12:00', 'end' => '13:00', 'formatted' => '12:00 PM - 1:00 PM'],
            ['start' => '13:00', 'end' => '14:00', 'formatted' => '1:00 PM - 2:00 PM'],
            ['start' => '14:00', 'end' => '15:00', 'formatted' => '2:00 PM - 3:00 PM'],
            ['start' => '15:00', 'end' => '16:00', 'formatted' => '3:00 PM - 4:00 PM'],
            ['start' => '16:00', 'end' => '17:00', 'formatted' => '4:00 PM - 5:00 PM'],
            ['start' => '17:00', 'end' => '18:00', 'formatted' => '5:00 PM - 6:00 PM'],
            ['start' => '18:00', 'end' => '19:00', 'formatted' => '6:00 PM - 7:00 PM'],
            ['start' => '19:00', 'end' => '20:00', 'formatted' => '7:00 PM - 8:00 PM'],
            ['start' => '20:00', 'end' => '21:00', 'formatted' => '8:00 PM - 9:00 PM'],
            ['start' => '21:00', 'end' => '22:00', 'formatted' => '9:00 PM - 10:00 PM'],
            ['start' => '22:00', 'end' => '23:00', 'formatted' => '10:00 PM - 11:00 PM'],
            ['start' => '23:00', 'end' => '23:59', 'formatted' => '11:00 PM - 11:59 PM'],
        ];

        foreach ($availableHours as $slot) {
            $slots[] = [
            'date' => $dateString,
                'start' => $slot['start'],
                'end' => $slot['end'],
                'formatted' => $slot['formatted'],
            ];
        }

        return $slots;
    }

    /**
     * Test routing method to debug URL generation and parameter passing
     *
     * @param string|null $id Writing Service Request id
     * @return \Cake\Http\Response|null
     */
    public function testRouting(?string $id = null)
    {
        $this->disableAutoRender();
        $this->response = $this->response->withType('application/json');

        $result = [
            'method' => $this->request->getMethod(),
            'url' => $this->request->getRequestTarget(),
            'id_parameter' => $id,
            'route_params' => $this->request->getParam('pass'),
            'all_params' => $this->request->getAttribute('params'),
            'post_data' => $this->request->getData(),
            'is_admin_prefix' => $this->request->getParam('prefix') === 'Admin',
            'controller' => $this->request->getParam('controller'),
            'action' => $this->request->getParam('action'),
        ];

        return $this->response->withStringBody(json_encode($result, JSON_PRETTY_PRINT));
    }

    /**
     * Send time slots to customer via chat
     *
     * @param string|null $id Writing Service Request id
     * @return \Cake\Http\Response|null
     */
    public function sendTimeSlots(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        $this->log('SendTimeSlots called with ID: ' . $id, 'debug');
        $this->log('POST data: ' . json_encode($this->request->getData()), 'debug');
        $this->log('Request URL: ' . $this->request->getRequestTarget(), 'debug');
        $this->log('Route params: ' . json_encode($this->request->getParam('pass')), 'debug');
        $this->log('All params: ' . json_encode($this->request->getAttribute('params')), 'debug');

        // Try to get ID from route parameter first, then from POST data as fallback
        if (empty($id)) {
            $id = $this->request->getData('writing_service_request_id');
            $this->log('ID was empty, trying from POST data: ' . $id, 'debug');
        }

        if (empty($id)) {
            $this->log('No ID found in route parameter or POST data', 'error');
            $this->Flash->error(__('Invalid writing service request. Please try again.'));

            return $this->redirect(['action' => 'index']);
        }

        $this->log('Using ID: ' . $id, 'debug');

        try {
            $writingServiceRequest = $this->WritingServiceRequests->get(
                $id,
                contain: ['Users'],
            );

            // Get time slots and message from POST data
            $timeSlots = $this->request->getData('time_slots');
            $messageText = $this->request->getData('message_text');

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
                $date = new DateTime();
                $decodedTimeSlots = $this->createMockTimeSlots($date);
                $this->log('Created fallback time slots: ' . json_encode($decodedTimeSlots), 'debug');
            }

            // Format time slots for display in the message
            $formattedSlots = [];
            foreach ($decodedTimeSlots as $slot) {
                if (isset($slot['date']) && isset($slot['formatted'])) {
                    try {
                        $formattedDate = new DateTime($slot['date']);
                        $dayName = $formattedDate->format('l');
                        $formattedSlots[] = "- {$dayName}, {$formattedDate->format('F j, Y')}: {$slot['formatted']}";
                    } catch (Exception $e) {
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
            $message .= "\n\n";
            $message .= "[CALENDAR_BOOKING_LINK]\n";
            $message .= 'Click here to book a time slot or propose another time that works better for you.';
            $message .= "\n[/CALENDAR_BOOKING_LINK]";

            // Create a message entity
            /** @var \App\Model\Entity\User $user */
            $user = $this->Authentication->getIdentity();

            $messageData = [
                'request_messages' => [
                    [
                        'message' => $message,
                        'is_read' => false,
                        'is_deleted' => false, // Ensure is_deleted is set
                        'user_id' => $user->user_id,
                        'writing_service_request_id' => $writingServiceRequest->writing_service_request_id,
                    ],
                ],
            ];

            // Add the message to the request
            $writingServiceRequest = $this->WritingServiceRequests->patchEntity(
                $writingServiceRequest,
                $messageData,
            );

            if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                // Store time slots in session for later use when client books
                $this->request->getSession()->write(
                    "TimeSlots.{$id}",
                    [
                        'slots' => $decodedTimeSlots,
                        'expires' => time() + (7 * 24 * 60 * 60), // Expire after 7 days
                    ],
                );

                $this->Flash->success(__('Time slots sent successfully.'));

                // Notify the client via email
                try {
                    $requestWithUser = $this->WritingServiceRequests->get($id, contain: ['Users']);

                    if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                        // Use the already formatted time slots for email
                        $timeSlotsText = implode("\n", $formattedSlots);

                        // Send notification
                        $mailer = new PaymentMailer('default');
                        $mailer->customerWritingTimeSlotsNotification(
                            $requestWithUser,
                            $timeSlotsText,
                            'Diana Bonvini',
                        );
                        $mailer->deliverAsync();
                    }
                } catch (Exception $e) {
                    $this->log('Failed to send time slots notification: ' . $e->getMessage(), 'error');
                }

                // If the request status is pending, update it to in_progress
                if ($writingServiceRequest->request_status === 'pending') {
                    $writingServiceRequest->request_status = 'in_progress';
                    $this->WritingServiceRequests->save($writingServiceRequest);
                }
            } else {
                $this->log('Error saving time slots message: ' . json_encode($writingServiceRequest->getErrors()), 'error');
                $this->Flash->error(__('Failed to send time slots. Please try again.'));
            }

            return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
        } catch (Exception $e) {
            $this->log('Error in sendTimeSlots: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('An error occurred: {0}', $e->getMessage()));

            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Handle document upload in admin context
     *
     * @param \Psr\Http\Message\UploadedFileInterface|null $file The uploaded file
     * @param string $redirectAction The action to redirect to on error
     * @return \Cake\Http\Response|string|null The file path or a redirect Response on error
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
     * Upload document for a writing service request (Admin)
     *
     * @param string|null $id Writing Service Request id
     * @return \Cake\Http\Response|null
     */
    public function uploadDocument(?string $id = null)
    {
        $this->request->allowMethod(['post']);

        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error(__('You need to be an admin to upload documents.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        try {
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
                    'uploaded_by' => 'admin',
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
                        'is_deleted' => false, // Ensure is_deleted is set
                    ]);
                    $requestMessagesTable->save($newMessage);

                    $this->Flash->success(__('Document uploaded successfully.'));
                } else {
                    // Log the validation errors for debugging
                    $this->log('Document upload validation errors: ' . json_encode($requestDocument->getErrors()), 'error');
                    $this->Flash->error(__('Document uploaded but could not be saved in the database.'));
                }
            } else {
                $this->Flash->error(__('Failed to upload document. Please try again.'));
            }

            return $this->redirect(['action' => 'view', $id]);
        } catch (Exception $e) {
            $this->log('Error in admin document upload: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('Error: {0}', $e->getMessage()));

            return $this->redirect(['action' => 'view', $id]);
        }
    }
}
