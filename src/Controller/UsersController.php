<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\UserMailer;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\FrozenTime;
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
                    'controller' => 'Artworks',
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

            return $this->redirect(['controller' => 'Pages', 'action' => 'display', 'landing']);
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
    public function delete(?string $id = null): ?Response
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
     * @return \Cake\Http\Response|null|void
     */
    public function forgotPassword()
    {
        if ($this->request->is('get')) {
            return;
        }

        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $user = $this->Users->find()->where(['email' => $email])->first();

            if (!$user) {
                $this->Flash->error('No user found with that email address.');

                return;
            }

            // Generate a secure token & save to DB
            $token = Security::hash(Text::uuid(), 'sha256', true);
            $user->password_reset_token = $token;
            $user->token_expiration = new FrozenTime('+1 hour');

            if ($this->Users->save($user)) {
                // Build a reset link
                $resetLink = Router::url([
                    'controller' => 'Users',
                    'action' => 'resetPassword',
                    $token,
                ], true);

                $mailer = new UserMailer('default');
                $mailer->resetPassword($user, $resetLink);
                $mailer->deliver();

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
     * @return \Cake\Http\Response|null|void
     */
    public function resetPassword(?string $token = null)
    {
        if (!$token) {
            $this->Flash->error('Invalid password reset token.');

            return $this->redirect(['action' => 'login']);
        }

        // Find user by token and ensure token has not expired
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

        // Process the form submission for resetting the password
        if ($this->request->is(['post', 'put'])) {
            $newPassword = $this->request->getData('password');
            $confirmPassword = $this->request->getData('password_confirm');
            if ($this->request->getData('oauth_provider')) {
                $this->Flash->error('Invalid password reset request.');

                return $this->redirect(['action' => 'login']);
            }

            if ($newPassword !== $confirmPassword) {
                $this->Flash->error('Passwords do not match. Please try again.');
            } else {
                // Update the password and clear the reset token fields
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
        }

        // Clear the password field for security
        $user->password = '';
        $this->set(compact('user'));
    }
}
