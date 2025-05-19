<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController as BaseAdminController;
use App\Model\Entity\WritingServiceRequest;
use Cake\Http\Response;
use Cake\Utility\Inflector;
use Exception;

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
        $this->set('title', 'Writing Service Requests');

        // Use index.php in Admin/WritingServiceRequests folder
        $this->viewBuilder()->setTemplate('index');
        $this->viewBuilder()->setTemplatePath('Admin/WritingServiceRequests');

        // Get all writing service requests with users
        $query = $this->WritingServiceRequests->find()
            ->contain(['Users']);

//        /** @var array<\App\Model\Entity\WritingServiceRequest> $writingServiceRequests */
        $writingServiceRequests = $this->paginate($query);

        // Calculate unread counts manually instead of in the query
        foreach ($writingServiceRequests as $request) {
            try {
                $unreadCount = $this->WritingServiceRequests->RequestMessages->find()
                    ->where([
                        'writing_service_request_id' => $request->writing_service_request_id,
                        'is_read' => false,
                        'user_id NOT IN' => $this->WritingServiceRequests->Users->find()
                            ->select(['user_id'])
                            ->where(['user_type' => 'admin']),
                    ])
                    ->count();

                $request->unread_count = $unreadCount;
            } catch (Exception $e) {
                $this->log('Error calculating unread count: ' . $e->getMessage(), 'error');
                $request->unread_count = 0;
            }
        }

        // Get total unread messages across all requests
        $totalUnreadCount = 0;
        foreach ($writingServiceRequests as $request) {
            $totalUnreadCount += $request->unread_count ?? 0;
        }

        $this->set(compact('writingServiceRequests', 'totalUnreadCount'));
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
        $this->set('title', 'View Service Request');

        // Use view.php in Admin/WritingServiceRequests folder
        $this->viewBuilder()->setTemplate('view');
        $this->viewBuilder()->setTemplatePath('Admin/WritingServiceRequests');

        /** @var \App\Model\Entity\User $user */
        $user = $this->Authentication->getIdentity();

        $writingServiceRequest = $this->WritingServiceRequests->get($id, [
            'contain' => ['Users', 'RequestMessages' => function ($q) {
                return $q->contain(['Users'])
                    ->order(['RequestMessages.created_at' => 'ASC']);
            }],
        ]);

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

                    return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
                } else {
                    $this->Flash->error(__('Failed to send reply. Please try again.'));
                }
            }
        }

        $this->set(compact('writingServiceRequest'));
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

        if (!$user) {
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
            'cancelled' => 'Your request has been cancelled. Please contact us if you have any questions.',
            default => 'The status of your request has been updated to: ' . Inflector::humanize($status)
        };

        $data = [
            'request_messages' => [
                [
                    'user_id' => $admin->user_id,
                    'message' => 'Status Update: ' . $statusMessage,
                    'is_read' => false,
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
     * @return bool Success flag
     */
    private function sendPriceUpdateMessage(WritingServiceRequest $writingServiceRequest, float $price): bool
    {
        /** @var \App\Model\Entity\User $admin */
        $admin = $this->Authentication->getIdentity();

        $formattedPrice = '$' . number_format((float)$price, 2);
        $message = "Price Update: We've set the price for your request at {$formattedPrice}. ";
        $message .= "If you'd like to proceed, please reply to this message or use the payment option that will be added soon.";

        $data = [
            'request_messages' => [
                [
                    'user_id' => $admin->user_id,
                    'message' => $message,
                    'is_read' => false,
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
}
