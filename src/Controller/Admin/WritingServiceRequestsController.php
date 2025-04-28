<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\WritingServiceRequestsController as BaseWritingServiceRequestsController;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * WritingServiceRequests Controller (Admin prefix)
 *
 * Admin interface for managing writing service requests.
 * Uses dedicated admin templates.
 */
class WritingServiceRequestsController extends BaseWritingServiceRequestsController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Use admin layout
        $this->viewBuilder()->setLayout('admin');

        // By default, use the Admin/WritingServiceRequests templates for all actions
        $this->viewBuilder()->setTemplatePath('Admin/WritingServiceRequests');
    }

    /**
     * Override the beforeFilter to set authentication requirements
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // Remove any unauthenticated actions for admin
        $this->Authentication->addUnauthenticatedActions([]);

        // Check for admin user
        $user = $this->Authentication->getIdentity();
        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error('You must be logged in as an administrator to access this area.');

            return $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        }
    }

    /**
     * Index method - Shows all writing service requests with admin functionality
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $this->set('title', 'Writing Service Requests');

        // Look for admin_index.php in WritingServiceRequests folder until we create a dedicated admin template
        $this->viewBuilder()->setTemplate('admin_index');
        $this->viewBuilder()->setTemplatePath('WritingServiceRequests');

        $query = $this->WritingServiceRequests->find()
            ->contain(['Users']);

        $writingServiceRequests = $this->paginate($query);

        $this->set(compact('writingServiceRequests'));
    }

    /**
     * View method - Shows details of a writing service request
     *
     * @param string|null $id WritingServiceRequest id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $this->set('title', 'View Service Request');

        // Look for admin_view.php in WritingServiceRequests folder until we create a dedicated admin template
        $this->viewBuilder()->setTemplate('admin_view');
        $this->viewBuilder()->setTemplatePath('WritingServiceRequests');

        $writingServiceRequest = $this->WritingServiceRequests->get($id, [
            'contain' => ['Users', 'RequestMessages'],
        ]);

        $this->set(compact('writingServiceRequest'));
    }

    /**
     * Update request status method
     *
     * @param string|null $id WritingServiceRequest id.
     * @param string $status New status value.
     * @return \Cake\Http\Response|null Redirects to view page
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function updateStatus(?string $id = null, string $status = 'pending'): ?Response
    {
        $this->request->allowMethod(['post', 'put']);

        $writingServiceRequest = $this->WritingServiceRequests->get($id);
        $writingServiceRequest->status = $status;

        if ($this->WritingServiceRequests->save($writingServiceRequest)) {
            $this->Flash->success(__('The request status has been updated.'));
        } else {
            $this->Flash->error(__('The request status could not be updated. Please, try again.'));
        }

        return $this->redirect(['action' => 'view', $id]);
    }
}
