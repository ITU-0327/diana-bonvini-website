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
            $user = $this->Users->get($id, contain: ['Orders']);

            $this->set(compact('user'));
        } catch (Exception) {
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
     * Display and handle editing of the admin user profile
     *
     * @return \Cake\Http\Response|null|void
     */
    public function profile()
    {
        // Get the current logged in user
        /** @var \App\Model\Entity\User $identity*/
        $identity = $this->Authentication->getIdentity();
        $user = $this->Users->get($identity->user_id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fields' => ['first_name', 'last_name', 'email', 'phone_number'],
            ]);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your profile has been updated.'));
                $this->Authentication->setIdentity($user);

                return $this->redirect(['action' => 'profile']);
            }
            $this->Flash->error(__('Unable to update your profile. Please, try again.'));
        }

        $this->set(compact('user'));
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
            $userId = $identity?->user_id;

            if (!$userId) {
                throw new Exception('User ID not found in identity');
            }

            $user = $this->Users->get($userId);
            $data = $this->request->getData();

            // Verify current password
            $currentPassword = $data['current_password'] ?? '';
            $hasher = new DefaultPasswordHasher();

            if (!$hasher->check($currentPassword, (string)$user->password)) {
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
