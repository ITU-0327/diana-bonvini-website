<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class UsersController extends AppController
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

        $this->Authentication->addUnauthenticatedActions(['login', 'register']);
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        // If the user is authenticated successfully...
        if ($result && $result->isValid()) {
            /** @var \App\Model\Entity\User $user */
            $user = $this->Authentication->getIdentity();

            // Check if the user is soft-deleted
            if (!empty($user->is_deleted) && $user->is_deleted == 1) {
                $this->Flash->error(__('Account inactive'));
                // Log the user out so the identity is not kept in session
                $this->Authentication->logout();
            } else {
                // Retrieve the full user entity from the Users table
                $usersTable = $this->getTableLocator()->get('Users');
                /** @var \App\Model\Entity\User $userEntity */
                $userEntity = $usersTable->get($user->user_id);
                $userEntity->last_login = FrozenTime::now();
                $usersTable->save($userEntity);

                $redirect = $this->request->getQuery('redirect', [
                    'controller' => 'Users',
                    'action' => 'index',
                ]);

                return $this->redirect($redirect);
            }
        }

        // If it's a POST request and authentication failed, show an error.
        if ($this->request->is('post') && (!$result || !$result->isValid())) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function logout()
    {
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {
            $this->Authentication->logout();

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }

    /**
     * Register method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful register, renders view otherwise.
     */
    public function register()
    {
        $user = $this->Users->newEmptyEntity();
        $data = $this->request->getData();

        // If someone tries to include an oauth_provider in the normal registration,
        // treat it as an invalid registration.
        if (!empty($data['oauth_provider'])) {
            $this->Flash->error(__('The user could not be saved. Please, try again.'));

            return $this->redirect(['action' => 'register']);
        }

        // Set the default user_type to 'customer'
        $data['user_type'] = 'customer';

        if ($this->request->is('post')) {
            // Check if password and confirmation match
            if ($data['password'] !== $data['password_confirm']) {
                $this->Flash->error('Password and confirm password do not match');
                $this->set(compact('user'));

                return;
            }

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('User registered successfully'));

                return $this->redirect(['controller' => 'Users', 'action' => 'login']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Users->find();
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $user = $this->Users->get($id, contain: []);
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $user = $this->Users->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
