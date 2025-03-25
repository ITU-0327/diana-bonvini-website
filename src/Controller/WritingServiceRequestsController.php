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
            'order' => ['WritingServiceRequests.created_at' => 'DESC']
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
        $user = $this->Authentication->getIdentity();
        $userId = $user?->get('user_id');
        $writingServiceRequest = $this->WritingServiceRequests->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $documentPath = $this->handleDocumentUpload($data['document'] ?? null, 'add');
            if ($this->response->getStatusCode() === 302) {
                return $this->response;
            }
            if ($documentPath) {
                $data['document'] = $documentPath;
            } else {
                unset($data['document']);
            }

            $data['service_type'] = $data['service_type_display'] ?? null;
            $data['word_count_range'] = $data['word_count_range_display'] ?? null;

            $data['estimated_price'] = $this->calculateEstimatedPrice($data['service_type'], $data['word_count_range']);
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

            $documentPath = $this->handleDocumentUpload($data['document'] ?? null, 'edit');
            if ($this->response->getStatusCode() === 302) {
                return $this->response;
            }
            if ($documentPath) {
                $data['document'] = $documentPath;
            } else {
                unset($data['document']);
            }

            $data['estimated_price'] = $this->calculateEstimatedPrice(
                $data['service_type'] ?? null,
                $data['word_count_range'] ?? null
            );
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

    private function handleDocumentUpload($file, string $redirectAction)
    {
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedMimeTypes = [
            'text/plain',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (!in_array($file->getClientMediaType(), $allowedMimeTypes)) {
            $this->Flash->error(__('Invalid file type. Please upload txt, pdf, or Word documents only.'));
            return $this->redirect(['action' => $redirectAction]);
        }

        $uploadPath = WWW_ROOT . 'uploads' . DS . 'documents';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $file->getClientFilename());
        $filePath = $uploadPath . DS . $filename;
        $file->moveTo($filePath);

        return 'uploads/documents/' . $filename;
    }

    private function calculateEstimatedPrice(?string $type, ?string $range): float
    {
        $priceMap = [
            'creative_writing' => 2,
            'editing' => 1.5,
            'proofreading' => 1.2,
        ];

        if (!isset($priceMap[$type])) {
            return 0;
        }

        $multiplier = $priceMap[$type];

        return match ($range) {
            'under_5000' => $multiplier * 5000,
            '5000_20000' => $multiplier * 20000,
            '20000_50000' => $multiplier * 50000,
            '50000_plus' => $multiplier * 50000,
            default => 0,
        };
    }
}
