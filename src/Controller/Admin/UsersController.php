<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController as BaseAdminController;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Http\Response;
use Exception;

/**
 * Users Controller (Admin prefix)
 *
 * Manages users from an administrative perspective.
 * Uses dedicated admin templates.
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends BaseAdminController
{
    /**
     * Index method for admin - Shows all users with management capabilities
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $query = $this->Users->find();
        /** @var iterable<\App\Model\Entity\User> $users */
        $users = $this->paginate($query);

        // Calculate user statistics using fresh queries for each count
        $totalUsers = $this->Users->find()
            ->all()
            ->count();

        $activeUsers = $this->Users->find()
            ->where([
                'Users.is_deleted' => false,
                'Users.is_verified' => true,
            ])
            ->count();

        $customerUsers = $this->Users->find()
            ->where([
                'Users.is_deleted' => false,
                'Users.is_verified' => true,
                'Users.user_type' => 'customer',
            ])
            ->count();

        $adminUsers = $this->Users->find()
            ->where([
                'Users.is_deleted' => false,
                'Users.is_verified' => true,
                'Users.user_type' => 'admin',
            ])
            ->count();

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
}
