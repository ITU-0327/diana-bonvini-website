<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\UserMailer;
use App\Model\Entity\User;
use App\Service\TwoFactorService;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
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

        // Allow unauthenticated access to log in, register, forgotPassword, and resetPassword
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
     * @return \Cake\Http\Response|null
     */
    public function login(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        // If the user is authenticated successfully...
        if ($result && $result->isValid()) {
            /** @var \App\Model\Entity\User $user */
            $user = $this->Authentication->getIdentity();

            // No 2FA needed, update last login time and set active status
            $userEntity = $this->Users->get($user->user_id);
            $userEntity->last_login = DateTime::now();
            $userEntity->active = true; // Mark user as active when they log in
            $this->Users->save($userEntity);

            // Redirect admin users to dashboard, others to home
            if ($user->user_type === 'admin') {
                return $this->redirect(['_name' => 'admin_dashboard']);
            }

            $redirect = $this->request->getQuery('redirect', ['_name' => 'home']);

            return $this->redirect($redirect);
        }

        // If it's a POST request and authentication failed, show an error.
        if ($this->request->is('post') && $result) {
            if ($result->getStatus() === '2FA_REQUIRED') {
                return $this->redirect(['controller' => 'TwoFactorAuth', 'action' => 'verify']);
            } elseif ($result->getStatus() === 'ACCOUNT_INACTIVE') {
                $this->Flash->error(__('Your account is inactive. Please contact support.'));
            } else {
                $this->Flash->error(__('Invalid username or password'));
            }
        }

        return null;
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

            return $this->redirect(['_name' => 'home']);
        }
    }

    /**
     * Register method - now redirects to the verification flow
     *
     * @param \App\Service\TwoFactorService $twoFactorService
     * @return \Cake\Http\Response|null Redirects to the registration verification flow
     * @throws \Random\RandomException
     */
    public function register(TwoFactorService $twoFactorService): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        $user = $this->Users->newEmptyEntity();
        $data = $this->request->getData();

        // Prevent oauth_provider injection
        if (!empty($data['oauth_provider'])) {
            $this->Flash->error(__('The user could not be saved. Please, try again.'));

            return $this->redirect(['action' => 'register']);
        }

        // Defaults
        $data['user_type'] = 'customer';
        $data['is_verified'] = false;

        if ($this->request->is('post')) {
            // Check if password and confirmation match
            if ($data['password'] !== $data['password_confirm']) {
                $this->Flash->error('Password and confirm password do not match');
                $this->set(compact('user'));

                return null;
            }

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->request->getSession()
                    ->write('TwoFactorUser', [
                        'id' => $user->user_id,
                        'redirect' => ['_name' => 'home'],
                    ]);

                // Generate & email the code
                $twoFactorService->generateCode($user->user_id);

                $this->Flash->success(__('User registered successfully, a verification code has been sent to your email.'));

                return $this->redirect(['controller' => 'TwoFactorAuth','action' => 'verify']);
            }
            $errors = $user->getErrors();
            if (!empty($errors['email']['unique']) || !empty($errors['email']['isUnique'])) {
                $this->Flash->error(__('An account with that email address already exists. Please log in.'));

                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $query = $this->Users->find();
        $users = $this->paginate($query);
        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
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
        $user = $this->Users->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your details were updated.'));
                // Update the identity in session to reflect changes
                $this->Authentication->setIdentity($user);

                return $this->redirect(['action' => 'edit', $user->user_id]);
            }

            $this->Flash->error(__('Changes could not be saved. Please, try again.'));
        }

        $this->set(compact('user'));
    }

    /**
     * Forgot Password method
     *
     * @return \Cake\Http\Response|null
     */
    public function forgotPassword(): ?Response
    {
        if ($this->request->is('get')) {
            return null;
        }

        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            /** @var \App\Model\Entity\User $user */
            $user = $this->Users->find()
                ->where(['email' => $email])
                ->first();

            if (!$user) {
                $this->Flash->error('No user found with that email address.');

                return null;
            }

            if ($this->_generateToken($user)) {
                return $this->redirect(['action' => 'login']);
            }
        }

        return null;
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
                'token_expiration >' => DateTime::now(),
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

    /**
     * Change Password method
     *
     * @return \Cake\Http\Response|null
     */
    public function changePassword(): ?Response
    {
        $this->request->allowMethod(['post']);

        /** @var \App\Model\Entity\User $identity */
        $identity = $this->request->getAttribute('identity');
        $user = $this->Users->get($identity->user_id);

        $this->_generateToken($user);

        return $this->redirect($this->referer());
    }

    /**
     * Generates a password reset token for the given user, saves it, and emails the reset link.
     *
     * @param \App\Model\Entity\User $user The user entity to generate the token for.
     * @return bool True if the token was successfully generated and emailed, false otherwise.
     */
    private function _generateToken(User $user): bool
    {
        $token = Security::hash(Text::uuid(), 'sha256', true);
        $user->password_reset_token = $token;
        $user->token_expiration = new DateTime('+1 hour');

        if ($this->Users->save($user)) {
            $resetLink = Router::url([
                'controller' => 'Users',
                'action' => 'resetPassword',
                $token,
            ], true);

            $mailer = new UserMailer('default');
            $mailer->resetPassword($user, $resetLink);
            $mailer->deliver();

            $this->Flash->success(__('A reset link has been sent to {0}.', $user->email));

            return true;
        }
        $this->Flash->error(__('Unable to generate reset link. Please try again later.'));

        return false;
    }
}
