<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Cake\Utility\Text;

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

        // Allow unauthenticated access to login, register, forgotPassword, and resetPassword
        $this->Authentication->addUnauthenticatedActions([
            'login',
            'register',
            'forgotPassword',
            'resetPassword',
        ]);
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
        $user = $this->Users->get($id, ['contain' => []]);
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
        $user = $this->Users->get($id, ['contain' => []]);
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

    /**
     * Forgot Password method
     *
     * Renders a form on GET and processes the reset request on POST.
     */
    public function forgotPassword()
    {
        // Render the form view if GET request
        if ($this->request->is('get')) {
            return;
        }

        // Handle POST request
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $user = $this->Users->findByEmail($email)->first();

            if (!$user) {
                $this->Flash->error('No user found with that email address.');

                return;
            }

            // Generate a secure token
            $token = Security::hash(Text::uuid(), 'sha256', true);

            // Set token and expiration (e.g., 1 hour from now)
            $user->password_reset_token = $token;
            $user->token_expiration = new FrozenTime('+1 hour');

            if ($this->Users->save($user)) {
                // Build a reset link
                $resetLink = Router::url([
                    'controller' => 'Users',
                    'action' => 'resetPassword',
                    $token,
                ], true);

                // Send an email with the reset link
                $mailer = new Mailer('default');
                $mailer->setTo($user->email)
                    ->setSubject('Your Password Reset Request')
                    ->deliver("Hello {$user->first_name},\n\nPlease click the following link to reset your password:\n\n{$resetLink}\n\nThis link will expire in 1 hour.");

                $this->Flash->success('A password reset link has been sent to your email address.');

                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error('Unable to save reset token. Please try again.');
            }
        }
    }

    /**
     * Reset Password method
     *
     * Validates the token, renders a reset form, and processes the new password.
     *
     * @param string|null $token
     */
    public function resetPassword(?string $token = null)
    {
        if (!$token) {
            $this->Flash->error('Invalid password reset token.');

            return $this->redirect(['action' => 'login']);
        }

        // Find user by token and check if not expired
        $user = $this->Users->find()
            ->where([
                'password_reset_token' => $token,
                'token_expiration >' => FrozenTime::now(),
            ])
            ->first();

        if (!$user) {
            $this->Flash->error('Invalid or expired token. Please request a new one.');

            return $this->redirect(['action' => 'forgotPassword']);
        }

        // Process the reset form submission
        if ($this->request->is(['post', 'put'])) {
            $newPassword = $this->request->getData('password');
            $confirmPassword = $this->request->getData('password_confirm');

            if ($newPassword !== $confirmPassword) {
                $this->Flash->error('Passwords do not match. Please try again.');

                return;
            }

            // Update the user's password and clear the token fields
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user->password_reset_token = null;
            $user->token_expiration = null;

            if ($this->Users->save($user)) {
                $this->Flash->success('Your password has been updated. You may now log in.');

                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error('Unable to reset your password. Please try again.');
            }
        }

        // Pass the user entity to the view for form creation
        $this->set(compact('user'));
    }
}
