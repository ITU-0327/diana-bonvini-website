<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\CoachingServiceRequest;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Routing\Router;
use DateTimeInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Cake\Utility\Text;
use Cake\Event\EventInterface;

/**
 * CoachingServiceRequests Controller
 *
 * @property \App\Model\Table\CoachingServiceRequestsTable $CoachingServiceRequests
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class CoachingServiceRequestsController extends AppController
{
    /**
     * Before filter method.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event The event object.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to payment success callback from Stripe
        $this->Authentication->addUnauthenticatedActions([
            'paymentSuccess',
        ]);
        
        // Ensure FormProtection allows paymentSuccess action
        $this->FormProtection->setConfig('unlockedActions', [
            'add', 'edit', 'uploadDocument', 'paymentSuccess'
        ]);
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
        $userId = $user?->get('user_id');

        if (!$userId) {
            $this->Flash->error(__('You need to be logged in to view your coaching service requests.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $query = $this->CoachingServiceRequests->find()
            ->contain(['Users', 'CoachingServicePayments'])
            ->where(['CoachingServiceRequests.user_id' => $userId]);

        $this->paginate = [
            'order' => ['CoachingServiceRequests.created_at' => 'DESC'],
        ];

        $coachingServiceRequests = $this->paginate($query);

        $this->set(compact('coachingServiceRequests'));
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
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user) {
            $this->Flash->error(__('You need to be logged in.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        // Use basic contain without CoachingServicePayments to ensure it works
        $coachingServiceRequest = $this->CoachingServiceRequests->get(
            $id,
            contain: [
                'Users',
                'CoachingRequestMessages' => function ($q) {
                    return $q->contain(['Users'])
                        ->orderBy(['CoachingRequestMessages.created_at' => 'ASC']);
                },
                'CoachingServicePayments' => function ($q) {
                    return $q->orderBy(['CoachingServicePayments.created_at' => 'DESC']);
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
            
        // Fetch request documents
        $coachingRequestDocumentsTable = $this->fetchTable('CoachingRequestDocuments');
        $coachingRequestDocuments = $coachingRequestDocumentsTable->find()
            ->where([
                'coaching_service_request_id' => $id,
                'is_deleted' => false,
            ])
            ->orderBy(['created_at' => 'DESC'])
            ->toArray();

        // Mark messages from admin as read when customer views them
        $this->markMessagesAsRead($coachingServiceRequest, $user->user_id);

        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();

            if (!empty($data['reply_message'])) {
                $data['coaching_request_messages'][] = [
                    'user_id' => $user->user_id,
                    'message' => $data['reply_message'],
                    'is_read' => false, // Initially not read by the admin
                ];

                $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity(
                    $coachingServiceRequest,
                    $data,
                );

                if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
                    $this->Flash->success(__('Message sent successfully.'));

                    // If the request status is pending, update it to in_progress
                    if ($coachingServiceRequest->request_status === 'pending') {
                        $coachingServiceRequest->request_status = 'in_progress';
                        $this->CoachingServiceRequests->save($coachingServiceRequest);
                    }
                    
                    // Send notification email to admin
                    try {
                        // Get the latest message that was just added
                        $latestMessage = end($coachingServiceRequest->coaching_request_messages);
                        
                        if ($latestMessage) {
                            // Get a fresh copy of the request with user data
                            $requestWithUser = $this->CoachingServiceRequests->get($id, contain: ['Users']);
                            
                            // Fixed admin email
                            $adminEmail = 'diana@dianabonvini.com';
                            $adminName = 'Diana Bonvini';
                            
                            // Send admin notification
                            $mailer = new \App\Mailer\PaymentMailer('default');
                            $mailer->newCoachingMessageNotification(
                                $requestWithUser, 
                                $data['reply_message'], 
                                $adminEmail, 
                                $adminName
                            );
                            $mailer->deliverAsync();
                        }
                    } catch (\Exception $e) {
                        // Log critical errors only
                        $this->log('Error sending message notification: ' . $e->getMessage(), 'error');
                    }

                    return $this->redirect(['action' => 'view', $id]);
                } else {
                    $this->Flash->error(__('Failed to send message. Please try again.'));
                }
            }
        }

        $this->set(compact('coachingServiceRequest', 'appointments', 'coachingRequestDocuments'));
    }

    /**
     * Marks messages as read for the given user
     *
     * @param \App\Model\Entity\CoachingServiceRequest $coachingServiceRequest The coaching service request
     * @param string $userId The ID of the current user
     * @return void
     */
    private function markMessagesAsRead(CoachingServiceRequest $coachingServiceRequest, string $userId): void
    {
        if (empty($coachingServiceRequest->coaching_request_messages)) {
            return;
        }

        $coachingRequestMessagesTable = $this->fetchTable('CoachingRequestMessages');
        $updatedCount = 0;

        foreach ($coachingServiceRequest->coaching_request_messages as $message) {
            // Only mark messages from other users (admin) as read
            if ($message->user_id !== $userId && !$message->is_read) {
                $message->is_read = true;
                $coachingRequestMessagesTable->save($message);
                $updatedCount++;
            }
        }

        // Log how many messages were marked as read for debugging
        if ($updatedCount > 0) {
            $this->log("Marked $updatedCount messages as read for user $userId", 'info');
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user) {
            $this->Flash->error(__('You need to be logged in to create a coaching service request.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $coachingServiceRequest = $this->CoachingServiceRequests->newEmptyEntity();
        
        if ($this->request->is('post')) {
            // Log the raw request data for debugging
            $this->log('Coaching service request POST received. Request data: ' . json_encode($this->request->getData()), 'debug');
            $this->log('Request method: ' . $this->request->getMethod(), 'debug');
            $this->log('Request URL: ' . $this->request->getRequestTarget(), 'debug');
            
            // Extract file before patchEntity to avoid validation issues
            $file = $this->request->getUploadedFile('document');
            
            // Prepare the data for saving - remove the document field to prevent validation issues
            $data = $this->request->getData();
            
            // Handle document separately to avoid validation issues
            if (isset($data['document'])) {
                unset($data['document']);
            }
            
            $data['user_id'] = $user->get('user_id');
            $data['request_status'] = $data['request_status'] ?? 'pending';
            $data['is_deleted'] = $data['is_deleted'] ?? false;
            
            // Apply the data to the entity first without the document
            $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity($coachingServiceRequest, $data);
            
            // Handle document upload if present
            if ($file && $file->getError() === UPLOAD_ERR_OK) {
                try {
                    $this->log('Processing file upload', 'debug');
                    $uploadResult = $this->_handleDocumentUpload($file, 'add');
                    
                    if ($uploadResult instanceof \Cake\Http\Response) {
                        return $uploadResult; 
                    }
                    
                    // Set document path directly on the entity
                    $coachingServiceRequest->document = $uploadResult;
                    $this->log('Document uploaded successfully: ' . $uploadResult, 'debug');
                } catch (\Exception $e) {
                    $this->log('File upload error: ' . $e->getMessage(), 'error');
                    $this->Flash->error('Error uploading file: ' . $e->getMessage());
                    // Don't redirect, allow the form to be displayed again with errors
                }
            } elseif ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                $this->log('File upload error code: ' . $file->getError(), 'error');
                $this->Flash->error('There was an error uploading your file. Please try again.');
            }
            
            // Log validation errors if any
            if ($coachingServiceRequest->hasErrors()) {
                $this->log('Validation errors: ' . json_encode($coachingServiceRequest->getErrors()), 'debug');
                $this->Flash->error(__('The form contains errors. Please check and try again.'));
            } else {
                try {
                    // Initialize the ID before saving
                    if (method_exists($coachingServiceRequest, 'initializeCoachingServiceRequestId')) {
                        $coachingServiceRequest->initializeCoachingServiceRequestId();
                    }
                    
                    if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
                        $this->log('Coaching service request saved successfully. ID: ' . $coachingServiceRequest->coaching_service_request_id, 'debug');
                        $this->Flash->success(__('Your coaching service request has been saved.'));
                        
                        // Send notification email to admin about new coaching request
                        try {
                            // Get a fresh copy of the request with user data
                            $requestWithUser = $this->CoachingServiceRequests->get($coachingServiceRequest->coaching_service_request_id, contain: ['Users']);
                            
                            if (!empty($requestWithUser->user) && !empty($requestWithUser->user->email)) {
                                $adminEmail = 'diana@dianabonvini.com';
                                $adminName = 'Diana Bonvini';
                                
                                // Send admin notification
                                $mailer = new \App\Mailer\PaymentMailer('default');
                                $mailer->newCoachingRequestNotification(
                                    $requestWithUser,
                                    $adminEmail,
                                    $adminName
                                );
                                $result = $mailer->deliverAsync();
                                
                                if ($result) {
                                    $this->log('New coaching request notification sent successfully to ' . $adminEmail, 'info');
                                } else {
                                    $this->log('New coaching request notification failed to send to ' . $adminEmail, 'warning');
                                }
                            }
                        } catch (\Exception $e) {
                            $this->log('Error sending new coaching request notification: ' . $e->getMessage(), 'error');
                        }
                        
                        return $this->redirect(['action' => 'view', $coachingServiceRequest->coaching_service_request_id]);
                    } else {
                        $this->log('Failed to save coaching service request. General error.', 'error');
                        $this->Flash->error(__('The coaching service request could not be saved. Please try again.'));
                    }
                } catch (\Exception $e) {
                    $this->log('Exception during save: ' . $e->getMessage(), 'error');
                    $this->Flash->error(__('An error occurred while saving your request. Please try again.'));
                }
            }
        }

        $this->set(compact('coachingServiceRequest'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        if (!$user) {
            $this->Flash->error(__('You need to be logged in to edit a coaching service request.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: []);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Handle document upload if a new file is submitted
            $file = $this->request->getUploadedFile('document');
            if ($file && $file->getError() === UPLOAD_ERR_OK) {
                try {
                    $uploadResult = $this->_handleDocumentUpload($file, 'edit');
                    
                    if ($uploadResult instanceof Response) {
                        return $uploadResult; 
                    }
                    
                    $data['document'] = $uploadResult;
                } catch (\Exception $e) {
                    $this->Flash->error('Error uploading file: ' . $e->getMessage());
                    return $this->redirect(['action' => 'edit', $id]);
                }
            }
            
            $coachingServiceRequest = $this->CoachingServiceRequests->patchEntity($coachingServiceRequest, $data);
            
            if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
                $this->Flash->success(__('The coaching service request has been updated.'));

                return $this->redirect(['action' => 'view', $id]);
            }
            $this->Flash->error(__('The coaching service request could not be updated. Please, try again.'));
        }
        
        $this->set(compact('coachingServiceRequest'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id);
        
        // Soft delete - just set the is_deleted flag to true
        $coachingServiceRequest->is_deleted = true;
        
        if ($this->CoachingServiceRequests->save($coachingServiceRequest)) {
            $this->Flash->success(__('The coaching service request has been deleted.'));
        } else {
            $this->Flash->error(__('The coaching service request could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Helper method to handle document uploads
     *
     * @param \Psr\Http\Message\UploadedFileInterface|null $file The uploaded file
     * @param string $redirectAction The action to redirect to on error
     * @return string|Response|null The path to the saved file or a redirect Response on error
     */
    protected function _handleDocumentUpload(?UploadedFileInterface $file, string $redirectAction): string|Response|null
    {
        if (!$file) {
            return null;
        }
        
        $originalFilename = $file->getClientFilename();
        $mimeType = $file->getClientMediaType();
        
        // Validate file type
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($mimeType, $allowedTypes)) {
            $this->Flash->error(__('Invalid file type. Please upload a PDF, JPG, or DOCX file.'));
            return $this->redirect(['action' => $redirectAction]);
        }
        
        // Generate a unique filename
        $uniqueFilename = Text::uuid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $originalFilename);
        
        // Define the upload directory
        $uploadDir = WWW_ROOT . 'uploads' . DS . 'coaching_docs';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move the file
        $file->moveTo($uploadDir . DS . $uniqueFilename);
        
        // Return the relative path for storage in the database
        return 'uploads/coaching_docs/' . $uniqueFilename;
    }

    /**
     * Upload document - endpoint to handle document uploads
     *
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null Redirects on success or error.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function uploadDocument(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post']);
        
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            $this->Flash->error(__('You need to be logged in to upload documents.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        
        $coachingServiceRequest = $this->CoachingServiceRequests->get($id);
        
        // Check if document was uploaded
        $file = $this->request->getUploadedFile('document');
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            $this->Flash->error(__('No file was uploaded or there was an error with the upload.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        try {
            // Handle file upload
            $uploadResult = $this->_handleDocumentUpload($file, 'view');
            
            if ($uploadResult instanceof Response) {
                return $uploadResult;
            }
            
            // Create record in the CoachingRequestDocuments table
            $coachingRequestDocumentsTable = $this->fetchTable('CoachingRequestDocuments');
            
            $originalFilename = $file->getClientFilename();
            $mimeType = $file->getClientMediaType();
            $fileSize = $file->getSize();
            
            $documentEntity = $coachingRequestDocumentsTable->newEntity([
                'coaching_service_request_id' => $id,
                'user_id' => $user->get('user_id'),
                'document_path' => $uploadResult,
                'document_name' => $originalFilename,
                'file_type' => $mimeType,
                'file_size' => $fileSize,
                'uploaded_by' => 'customer',
                'is_deleted' => false,
            ]);
            
            if ($coachingRequestDocumentsTable->save($documentEntity)) {
                $this->Flash->success(__('Document has been uploaded successfully.'));
            } else {
                $this->Flash->error(__('Could not save document information. Please try again.'));
            }
            
        } catch (\Exception $e) {
            $this->Flash->error(__('Error uploading document: {0}', $e->getMessage()));
        }
        
        return $this->redirect(['action' => 'view', $id]);
    }
    
    /**
     * Check payment status for multiple payment IDs
     * 
     * @param string|null $id Coaching Service Request id.
     * @return \Cake\Http\Response|null Returns JSON response.
     */
    public function checkPaymentStatus(?string $id = null)
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setOption('serialize', ['success', 'payments']);
        
        // Default response
        $response = [
            'success' => false,
            'payments' => [],
        ];
        
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            $this->set($response);
            return null;
        }
        
        // Check if we have a JSON body with payment IDs
        $body = $this->request->getData();
        if (empty($body) || empty($body['paymentIds']) || !is_array($body['paymentIds'])) {
            $this->set($response);
            return null;
        }
        
        // Get the coaching service request with payments
        try {
            $coachingServiceRequest = $this->CoachingServiceRequests->get(
                $id,
                contain: [
                    'CoachingServicePayments' => function ($q) {
                        return $q->orderBy(['CoachingServicePayments.created_at' => 'DESC']);
                    },
                ],
            );
            
            if ($coachingServiceRequest->user_id !== $user->user_id) {
                $this->set($response);
                return null;
            }
            
            // Prepare payment status data
            $payments = [];
            foreach ($body['paymentIds'] as $paymentId) {
                $isPaid = false;
                $paidDate = null;
                
                // Look for this payment in the loaded payments
                if (!empty($coachingServiceRequest->coaching_service_payments)) {
                    foreach ($coachingServiceRequest->coaching_service_payments as $payment) {
                        if ($payment->payment_id === $paymentId && $payment->status === 'paid') {
                            $isPaid = true;
                            $paidDate = $payment->payment_date ? $payment->payment_date->jsonSerialize() : null;
                            break;
                        }
                    }
                }
                
                $payments[] = [
                    'id' => $paymentId,
                    'isPaid' => $isPaid,
                    'paidDate' => $paidDate,
                ];
            }
            
            $response = [
                'success' => true,
                'payments' => $payments,
            ];
        } catch (\Exception $e) {
            $this->log('Error checking payment status: ' . $e->getMessage(), 'error');
        }
        
        $this->set($response);
        return null;
    }
    
    /**
     * Process payment for a coaching service request
     *
     * @param string|null $id Coaching Service Request id.
     * @param string|null $paymentId The payment ID to process.
     * @return \Cake\Http\Response|null Redirects after payment processing.
     */
    public function pay(?string $id = null, ?string $paymentId = null)
    {
        if (!$id || !$paymentId) {
            $this->Flash->error(__('Invalid request parameters.'));
            return $this->redirect(['action' => 'index']);
        }
        
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            $this->Flash->error(__('You need to be logged in to make payments.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        
        try {
            // Get the coaching service request
            $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: ['CoachingServicePayments', 'Users']);
            
            // Set payment amount
            $amount = 100.00; // Default amount if none found
            
            // Find the specific payment record using the primary key
            $paymentTable = $this->fetchTable('CoachingServicePayments');
            $payment = $paymentTable->find()
                ->where([
                    'coaching_service_payment_id' => $paymentId,
                ])
                ->first();
                
            if ($payment) {
                $amount = $payment->amount;
            } else {
                // If not found by ID, it might be a database record payment
                // Try to get from session if it's a session-based payment
                $sessionData = $this->request->getSession()->read("CsrPayments.$paymentId");
                if ($sessionData && isset($sessionData['db_payment_id'])) {
                    // Try to find by the database payment ID
                    $payment = $paymentTable->find()
                        ->where([
                            'coaching_service_payment_id' => $sessionData['db_payment_id'],
                        ])
                        ->first();
                    
                    if ($payment) {
                        $amount = $payment->amount;
                        $paymentId = $payment->coaching_service_payment_id; // Use the actual payment ID
                    }
                }
                
                if (!$payment) {
                    $this->Flash->error(__('Payment record not found. Please try again.'));
                    return $this->redirect(['action' => 'view', $id]);
                }
            }
            
            // Initialize Stripe for payment processing
            Stripe::setApiKey(Configure::read('Stripe.secret'));
            
            $this->log('Using Stripe secret key: ' . substr(Configure::read('Stripe.secret'), 0, 10) . '...', 'debug');
            
            // Create checkout session for payment with correct URLs
            $currentHost = $this->request->getEnv('HTTP_HOST');
            $scheme = $this->request->getEnv('HTTPS') ? 'https' : 'http';
            
            $successUrl = $scheme . '://' . $currentHost . '/coaching-service-requests/payment-success/' . $id . '/' . $paymentId;
            $cancelUrl = $scheme . '://' . $currentHost . '/coaching-service-requests/view/' . $id;
            
            $this->log('Creating Stripe session with params: ' . json_encode([
                'line_items' => [
                    'price_data' => [
                        'currency' => 'aud',
                        'product_data' => [
                            'name' => 'Coaching Service: ' . $coachingServiceRequest->service_title,
                        ],
                        'unit_amount' => (int)($amount * 100),
                    ],
                    'quantity' => 1,
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]), 'debug');
            
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'aud',
                        'product_data' => [
                            'name' => 'Coaching Service: ' . $coachingServiceRequest->service_title,
                        ],
                        'unit_amount' => (int)($amount * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'coaching_service_request_id' => $id,
                    'payment_id' => $paymentId,
                    'user_id' => $user->user_id,
                ],
            ]);
            
            $this->log('Stripe session created successfully. Redirecting to: ' . $session->url, 'debug');
            
            // Redirect to Stripe checkout
            return $this->redirect($session->url);
            
        } catch (\Exception $e) {
            $this->log('Payment error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('There was an error processing your payment. Please try again later.'));
            return $this->redirect(['action' => 'view', $id]);
        }
    }
    
    /**
     * Handle successful payment
     * 
     * @param string|null $id Coaching Service Request id.
     * @param string|null $paymentId The payment ID.
     * @return \Cake\Http\Response|null Redirects after processing successful payment.
     */
    public function paymentSuccess(?string $id = null, ?string $paymentId = null)
    {
        $this->request->allowMethod(['get', 'post']);
        $this->log('paymentSuccess called with id: ' . $id . ', paymentId: ' . $paymentId, 'debug');
        
        if (!$id || !$paymentId) {
            $this->Flash->error(__('Invalid request parameters.'));
            return $this->redirect(['action' => 'index']);
        }
        
        try {
            // Update payment status in database
            $paymentTable = $this->fetchTable('CoachingServicePayments');
            $payment = $paymentTable->find()
                ->where([
                    'coaching_service_payment_id' => $paymentId,
                ])
                ->first();
                
            $this->log('Payment found: ' . ($payment ? 'yes' : 'no'), 'debug');
                
            if ($payment) {
                $payment->status = 'paid';
                $payment->payment_date = new \DateTime();
                $payment->transaction_id = 'stripe_' . date('YmdHis') . '_' . substr($paymentId, 0, 8);
                
                $this->log('Attempting to save payment with status: paid', 'debug');
                
                if ($paymentTable->save($payment)) {
                    $this->log('Payment saved successfully', 'debug');
                    $this->Flash->success(__('Payment was successful! Thank you for your payment.'));
                    
                    // Update request status if needed
                    try {
                        $this->log('Getting coaching service request for status update', 'debug');
                        $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: []);
                        if ($coachingServiceRequest->request_status === 'pending') {
                            $coachingServiceRequest->request_status = 'in_progress';
                            $this->CoachingServiceRequests->save($coachingServiceRequest);
                            $this->log('Request status updated to in_progress', 'debug');
                        }
                    } catch (\Exception $e) {
                        $this->log('Error updating request status: ' . $e->getMessage(), 'error');
                    }
                    
                    // Send payment confirmation email
                    try {
                        $this->log('Getting request with user for email', 'debug');
                        $requestWithUser = $this->CoachingServiceRequests->get($id, contain: ['Users']);
                        
                        // Send customer confirmation
                        $this->log('Creating mailer for customer', 'debug');
                        $customerMailer = new \App\Mailer\PaymentMailer('default');
                        $customerMailer->sendCoachingPaymentConfirmation($requestWithUser, $payment);
                        $customerMailer->deliverAsync();
                        $this->log('Customer confirmation email sent successfully', 'debug');
                        
                        // Send admin notification
                        try {
                            $adminEmail = 'diana@dianabonvini.com';
                            $adminName = 'Diana Bonvini';
                            
                            $this->log('Creating mailer for admin notification', 'debug');
                            $adminMailer = new \App\Mailer\PaymentMailer('default');
                            $adminMailer->adminCoachingPaymentNotification(
                                $requestWithUser, 
                                $payment, 
                                $adminEmail, 
                                $adminName
                            );
                            $adminMailer->deliverAsync();
                            $this->log('Admin notification email sent successfully', 'debug');
                            
                        } catch (\Exception $adminEmailError) {
                            $this->log('Error sending admin notification email: ' . $adminEmailError->getMessage(), 'error');
                        }
                        
                    } catch (\Exception $e) {
                        $this->log('Error sending payment confirmation email: ' . $e->getMessage(), 'error');
                    }
                } else {
                    $this->log('Failed to save payment', 'error');
                    $this->Flash->error(__('Your payment was processed, but there was an error updating the payment record.'));
                }
            } else {
                $this->log('Payment record not found', 'debug');
                $this->Flash->warning(__('Payment record not found, but your payment may have been processed.'));
            }
            
        } catch (\Exception $e) {
            $this->log('Payment success handling error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('There was an error updating your payment record.'));
        }
        
        // Since the user might not be authenticated (coming from Stripe), we need to redirect to login with a message
        // Check if user is authenticated
        $user = $this->Authentication->getIdentity();
        
        if ($user) {
            // User is authenticated, redirect to view page
            $this->log('User is authenticated, redirecting to view page', 'debug');
            return $this->redirect(['action' => 'view', $id, '#' => 'messages']);
        } else {
            // User is not authenticated (normal for Stripe callback), redirect to login with success message
            $this->log('User not authenticated, redirecting to login', 'debug');
            $this->Flash->success(__('Payment completed successfully! Please log in to view your request.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
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
            // Check if the coaching service request exists
            $coachingServiceRequest = $this->CoachingServiceRequests->get($id);

            // First try to get payment data from database (new approach)
            $coachingServicePaymentsTable = $this->fetchTable('CoachingServicePayments');
            $paymentData = null;
            
            try {
                $paymentEntity = $coachingServicePaymentsTable->get($paymentId);
                
                // Check if payment is still pending
                if ($paymentEntity->status !== 'pending') {
                    $this->Flash->error('This payment has already been processed.');
                    return $this->redirect(['action' => 'view', $id]);
                }
                
                // Create session data from database record for compatibility with existing pay method
                $sessionPaymentId = 'db_' . $paymentId;
                $paymentData = [
                    'amount' => (string)$paymentEntity->amount,
                    'description' => 'Coaching service payment',
                    'coaching_service_request_id' => $id,
                    'created' => time(),
                    'status' => $paymentEntity->status,
                    'db_payment_id' => $paymentId,
                ];
                
                $this->request->getSession()->write("CsrPayments.$sessionPaymentId", $paymentData);
                $this->log('Created payment data from database record: ' . json_encode($paymentData), 'debug');
                
                // Call the pay method with the new session-based payment ID
                return $this->pay($id, $sessionPaymentId);
                
            } catch (\Exception $dbException) {
                $this->log('Could not find payment in database: ' . $dbException->getMessage(), 'debug');
                
                // Fallback to legacy session-based system
                $parts = explode('|', urldecode($paymentId));
                $sessionPaymentId = $parts[0];
                $paymentData = $this->request->getSession()->read("CsrPayments.$sessionPaymentId");

                $this->log('Payment data retrieved from session: ' . json_encode([
                        'sessionPaymentId' => $sessionPaymentId,
                        'paymentData' => $paymentData,
                    ]), 'debug');

                // If still no payment data found, fail
                if (!$paymentData) {
                    $this->Flash->error('Invalid payment request. Payment information not found.');
                    return $this->redirect(['action' => 'view', $id]);
                }
                
                // Call the regular pay method with the legacy parameters
                return $this->pay($id, $paymentId);
            }

        } catch (\Exception $e) {
            $this->log('payDirect error: ' . $e->getMessage(), 'error');
            $this->Flash->error('Error processing payment: ' . $e->getMessage());
            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Fetch messages for AJAX requests
     * 
     * @param string|null $id Coaching Service Request id.
     * @param string|null $lastMessageId The ID of the last message received.
     * @return void
     */
    public function fetchMessages(?string $id = null, ?string $lastMessageId = null)
    {
        $this->request->allowMethod(['get']);

        // Configure the response for JSON
        $this->viewBuilder()->setClassName('Json');
        $this->response = $this->response->withType('application/json');

        // Default response
        $response = [
            'success' => false,
            'messages' => [],
            'lastMessageId' => $lastMessageId
        ];

        // Get parameters from query if not provided in route
        if (empty($id)) {
            $id = $this->request->getQuery('id');
        }
        if (empty($lastMessageId)) {
            $lastMessageId = $this->request->getQuery('lastMessageId');
        }

        // Check if the request includes the necessary parameters
        if (empty($id)) {
            $response['error'] = 'Missing request ID';
            return $this->response->withStringBody(json_encode($response));
        }

        try {
            // Get the current user
            /** @var \App\Model\Entity\User|null $user */
            $user = $this->Authentication->getIdentity();

            if (!$user) {
                $response['error'] = 'Authentication required';
                return $this->response->withStringBody(json_encode($response));
            }

            // Find the coaching service request
            $coachingServiceRequest = $this->CoachingServiceRequests->get($id, contain: ['Users']);

            // Ensure the user has access to this request
            if ($user->user_type !== 'admin' && $coachingServiceRequest->user_id !== $user->get('user_id')) {
                $response['error'] = 'You do not have permission to access this request';
                return $this->response->withStringBody(json_encode($response));
            }

            // Get new messages
            $messagesQuery = $this->CoachingServiceRequests->CoachingRequestMessages->find()
                ->contain(['Users'])
                ->where([
                    'coaching_service_request_id' => $id,
                    'is_deleted' => false,
                ])
                ->orderBy(['created_at' => 'ASC']);

            if (!empty($lastMessageId)) {
                $messagesQuery->andWhere([
                    'CoachingRequestMessages.coaching_request_message_id !=' => $lastMessageId,
                    'CoachingRequestMessages.created_at >' => function ($exp) use ($lastMessageId) {
                        return $exp->add('(SELECT created_at FROM coaching_request_messages WHERE coaching_request_message_id = \'' . $lastMessageId . '\')');
                    }
                ]);
            }

            $messages = $messagesQuery->all();
            $formattedMessages = [];

            foreach ($messages as $message) {
                // Skip deleted messages
                if ($message->is_deleted) {
                    continue;
                }

                // Format message for JSON response
                $formattedMessages[] = [
                    'id' => $message->coaching_request_message_id,
                    'content' => $message->message,
                    'sender' => [
                        'id' => $message->user->user_id,
                        'name' => $message->user->full_name,
                        'type' => $message->user->user_type
                    ],
                    'timestamp' => $message->created_at->jsonSerialize(),
                    'formattedTime' => $message->created_at->format('M j, Y g:i A'),
                    'isRead' => $message->is_read,
                ];

                // Update last message ID
                $response['lastMessageId'] = $message->coaching_request_message_id;
            }

            // Mark messages as read if any were found
            if (count($formattedMessages) > 0) {
                // Only mark messages from other users as read
                $this->CoachingServiceRequests->CoachingRequestMessages->updateAll(
                    ['is_read' => true],
                    [
                        'coaching_service_request_id' => $id,
                        'user_id !=' => $user->get('user_id'),
                        'is_read' => false
                    ]
                );
            }

            $response['success'] = true;
            $response['messages'] = $formattedMessages;
        } catch (\Exception $e) {
            $response['error'] = 'An error occurred: ' . $e->getMessage();
            $this->log('Error in fetchMessages: ' . $e->getMessage(), 'error');
        }

        return $this->response->withStringBody(json_encode($response));
    }
} 