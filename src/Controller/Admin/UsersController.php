<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\UsersController as BaseUsersController;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Exception;

/**
 * Users Controller (Admin prefix)
 *
 * Manages users from an administrative perspective.
 * Uses dedicated admin templates.
 */
class UsersController extends BaseUsersController
{
    /**
     * Initialize method
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        // Use admin layout
        $this->viewBuilder()->setLayout('admin');

        // By default, use the Admin/Users templates for all actions
        $this->viewBuilder()->setTemplatePath('Admin/Users');
    }

    /**
     * Override the beforeFilter to set authentication requirements
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Remove any unauthenticated actions for admin
        $this->Authentication->addUnauthenticatedActions([]);

        // Check for admin user
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error('You must be logged in as an administrator to access this area.');
            $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        }
    }

    /**
     * Index method for admin - Shows all users with management capabilities
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $this->set('title', 'User Management');

        $query = $this->Users->find();
        $users = $this->paginate($query);

        // Calculate user statistics
        $totalUsers = count($users);
        $activeUsers = 0;
        $customerUsers = 0;
        $adminUsers = 0;

        foreach ($users as $user) {
            if ($user->active === true) {
                $activeUsers++;
            }

            if ($user->user_type === 'customer') {
                $customerUsers++;
            } elseif ($user->user_type === 'admin') {
                $adminUsers++;
            }
        }

        $this->set(compact('users', 'totalUsers', 'activeUsers', 'customerUsers', 'adminUsers'));
    }

    /**
     * View method - Shows user details
     *
     * @param string|null $id User id.
     * @return void Renders view.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        try {
            $user = $this->Users->get($id, [
                'contain' => ['Orders'],
            ]);

            $this->set('title', 'User Details: ' . $user->first_name . ' ' . $user->last_name);
            $this->set(compact('user'));
        } catch (Exception $e) {
            $this->Flash->error(__('The user could not be found.'));
            $this->redirect(['action' => 'index']);
        }
    }

    /**
     * Activate a user account
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function activate(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post']);
        $user = $this->Users->get($id);
        $user->active = true;

        if ($this->Users->save($user)) {
            $this->Flash->success(__('The user has been activated.'));
        } else {
            $this->Flash->error(__('The user could not be activated. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Deactivate a user account
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function deactivate(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post']);
        $user = $this->Users->get($id);
        $user->active = false;

        if ($this->Users->save($user)) {
            $this->Flash->success(__('The user has been deactivated.'));
        } else {
            $this->Flash->error(__('The user could not be deactivated. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Display the admin user profile
     *
     * @return void
     */
    public function profile(): void
    {
        $this->set('title', 'My Profile');

        // Get the current logged in user
        /** @var \App\Model\Entity\User|null $identity */
        $identity = $this->Authentication->getIdentity();

        try {
            // Use direct property access which is more reliable in this codebase
            $userId = $identity->user_id;

            if (!$userId) {
                throw new Exception('User ID not found in identity');
            }

            $user = $this->Users->get($userId);
            $this->set(compact('user'));
        } catch (Exception $e) {
            $this->Flash->error('Could not load user profile. Please try again.');
            $this->redirect(['controller' => 'Admin', 'action' => 'dashboard']);
        }
    }

    /**
     * Update the admin's profile information
     *
     * @return \Cake\Http\Response|null Redirects to profile on success
     */
    public function updateProfile(): ?Response
    {
        $this->request->allowMethod(['post', 'put']);

        // Get the current logged in user
        /** @var \App\Model\Entity\User|null $identity */
        $identity = $this->Authentication->getIdentity();
        $userId = $identity->user_id;

        try {
            if (!$userId) {
                throw new Exception('User ID not found in identity');
            }

            $user = $this->Users->get($userId);
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fields' => ['first_name', 'last_name', 'email', 'phone_number'],
            ]);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your profile has been updated.'));
            } else {
                $this->Flash->error(__('Unable to update your profile. Please, try again.'));
            }
        } catch (Exception $e) {
            $this->Flash->error(__('Unable to update your profile. Please, try again.'));
        }

        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Change the admin's password
     *
     * @return \Cake\Http\Response|null Redirects to profile on success
     */
    public function changePassword(): ?Response
    {
        $this->request->allowMethod(['post', 'put']);

        try {
            // Get the current logged in user
            /** @var \App\Model\Entity\User|null $identity */
            $identity = $this->Authentication->getIdentity();
            $userId = $identity->user_id;

            if (!$userId) {
                throw new Exception('User ID not found in identity');
            }

            $user = $this->Users->get($userId);
            $data = $this->request->getData();

            // Verify current password
            $currentPassword = $data['current_password'] ?? '';
            $hasher = new DefaultPasswordHasher();

            if (!$hasher->check($currentPassword, $user->password)) {
                $this->Flash->error(__('Current password is incorrect.'));

                return $this->redirect(['action' => 'profile']);
            }

            // Verify new password and confirmation match
            $newPassword = $data['new_password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';

            if ($newPassword !== $confirmPassword) {
                $this->Flash->error(__('New password and confirmation do not match.'));

                return $this->redirect(['action' => 'profile']);
            }

            // Update password
            $user = $this->Users->patchEntity($user, ['password' => $newPassword]);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your password has been changed successfully.'));
            } else {
                $this->Flash->error(__('Unable to change your password. Please, try again.'));
            }
        } catch (Exception $e) {
            $this->Flash->error(__('Unable to change your password. Please, try again.'));
        }

        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Add method - Create a new user
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Verify password and confirmation match
            if ($data['password'] !== $data['password_confirm']) {
                $this->Flash->error(__('Password and confirmation do not match.'));
            } else {
                $user = $this->Users->patchEntity($user, $data);
                $user->active = true; // Set user as active by default

                if ($this->Users->save($user)) {
                    $this->Flash->success(__('User has been created successfully.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }

        $userTypes = [
            'customer' => 'Customer',
            'admin' => 'Administrator',
        ];

        $this->set(compact('user', 'userTypes'));
    }

    /**
     * Edit method - Edit a user
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     */
    public function edit(?string $id = null)
    {
        try {
            $user = $this->Users->get($id);

            if ($this->request->is(['patch', 'post', 'put'])) {
                $data = $this->request->getData();

                // If password field is empty, remove it from data
                if (empty($data['password'])) {
                    unset($data['password']);
                } elseif ($data['password'] !== $data['password_confirm']) {
                    $this->Flash->error(__('Password and confirmation do not match.'));
                    $this->set(compact('user'));

                    return;
                }

                $user = $this->Users->patchEntity($user, $data);

                if ($this->Users->save($user)) {
                    $this->Flash->success(__('User has been updated successfully.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The user could not be updated. Please, try again.'));
            }

            $userTypes = [
                'customer' => 'Customer',
                'admin' => 'Administrator',
            ];

            $this->set(compact('user', 'userTypes'));
        } catch (Exception $e) {
            $this->Flash->error(__('The user could not be found.'));

            return $this->redirect(['action' => 'index']);
        }
    }
}
