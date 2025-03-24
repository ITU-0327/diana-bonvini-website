<?php
declare(strict_types=1);

namespace App\Controller;

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
        // Get the currently logged-in user identity
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();

        // Extract the user_id from the user object
        $userId = $user?->get('user_id');

        // Optional: Redirect to login if no user is found (user not authenticated)
        if (!$userId) {
            $this->Flash->error(__('You need to be logged in to view your writing service requests.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $query = $this->WritingServiceRequests->find()
            ->contain(['Users']) // Eager load related user data if needed in the view
            ->where(['WritingServiceRequests.user_id' => $userId]); // Filter requests by user_id

        $writingServiceRequests = $this->paginate($query);

        // Pass the variable to the view template
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
        $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: ['Users']);
        $this->set(compact('writingServiceRequest'));
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
        $userId = $user?->get('user_id');

        $writingServiceRequest = $this->WritingServiceRequests->newEmptyEntity();

        if ($this->request->is('post')) {
            $file = $this->request->getData('document');

            if ($file && $file->getError() == 0) {
                $allowedMimeTypes = [
                    'text/plain',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];

                if (!in_array($file->getClientMediaType(), $allowedMimeTypes)) {
                    $this->Flash->error(__('Invalid file type. Please upload txt, pdf, or Word documents only.'));

                    return $this->redirect(['action' => 'add']);
                }

                $uploadPath = WWW_ROOT . 'uploads' . DS . 'documents';

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $file->getClientFilename());
                $filePath = $uploadPath . DS . $filename;

                $file->moveTo($filePath);

                $data = $this->request->getData();
                $data['document'] = 'uploads/documents/' . $filename;
                $data['user_id'] = $userId;

                $writingServiceRequest = $this->WritingServiceRequests->patchEntity($writingServiceRequest, $data);

                if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                    $this->Flash->success(__('The writing service request has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }

                $this->Flash->error(__('The writing service request could not be saved. Please, try again.'));
            } else {
                $this->Flash->error(__('Please select a valid document to upload.'));
            }
        }

        $this->set(compact('writingServiceRequest', 'userId'));
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
        $user = $this->Authentication->getIdentity();
        $userId = $user?->get('user_id');
        $writingServiceRequest = $this->WritingServiceRequests->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $file = $data['document'] ?? null;
            if ($file && $file->getError() === UPLOAD_ERR_OK) {
                $allowedMimeTypes = [
                    'text/plain',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];
                if (!in_array($file->getClientMediaType(), $allowedMimeTypes)) {
                    $this->Flash->error(__('Invalid file type. Please upload txt, pdf, or Word documents only.'));
                    return $this->redirect(['action' => 'edit', $id]);
                }
                $uploadPath = WWW_ROOT . 'uploads' . DS . 'documents';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $file->getClientFilename());
                $filePath = $uploadPath . DS . $filename;
                $file->moveTo($filePath);
                $data['document'] = 'uploads/documents/' . $filename;
            } else {
                unset($data['document']);
            }
            $data['user_id'] = $userId;
            $writingServiceRequest = $this->WritingServiceRequests->patchEntity($writingServiceRequest, $data);
            if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                $this->Flash->success(__('The writing service request has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The writing service request could not be saved. Please, try again.'));
        }
        $this->set(compact('writingServiceRequest', 'userId'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Writing Service Request id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
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
     * Info method
     *
     * @return void
     */
    public function info(): void
    {
        $this->viewBuilder()->setLayout('default');
    }
}
