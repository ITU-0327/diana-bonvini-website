<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * WritingServiceRequests Controller
 *
 * @property \App\Model\Table\WritingServiceRequestsTable $WritingServiceRequests
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
        $user = $this->Authentication->getIdentity();

        // Extract the user_id from the user object
        $userId = $user ? $user->get('user_id') : null;

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
    public function view($id = null)
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
        $userId = $user ? $user->get('user_id') : null;

        $writingServiceRequest = $this->WritingServiceRequests->newEmptyEntity();

        if ($this->request->is('post')) {
            $writingServiceRequest = $this->WritingServiceRequests->patchEntity($writingServiceRequest, $this->request->getData());

            $writingServiceRequest->user_id = $userId;

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
    public function edit($id = null)
    {
        $writingServiceRequest = $this->WritingServiceRequests->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $writingServiceRequest = $this->WritingServiceRequests->patchEntity($writingServiceRequest, $this->request->getData());
            if ($this->WritingServiceRequests->save($writingServiceRequest)) {
                $this->Flash->success(__('The writing service request has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The writing service request could not be saved. Please, try again.'));
        }
        $users = $this->WritingServiceRequests->Users->find('list', limit: 200)->all();
        $this->set(compact('writingServiceRequest', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Writing Service Request id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
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

    public function info()
    {
        $this->viewBuilder()->setLayout('default');
    }
}
