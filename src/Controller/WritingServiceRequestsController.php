<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\WritingServiceRequest;
use Cake\Http\Response;
use Exception;
use Psr\Http\Message\UploadedFileInterface;

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

        $writingServiceRequest = $this->WritingServiceRequests->get(
            $id,
            contain: ['Users', 'RequestMessages' => function ($q) {
                return $q->contain(['Users'])
                    ->order(['RequestMessages.created_at' => 'ASC']);
            }],
        );

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

        $this->set(compact('writingServiceRequest'));
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
            $data   = $this->request->getData();

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
            contain: ['Users', 'RequestMessages.Users'],
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
}
