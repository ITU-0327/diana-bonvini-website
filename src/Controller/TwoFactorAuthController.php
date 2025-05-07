<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\UserMailer;
use App\Model\Table\UsersTable;
use App\Service\TwoFactorService;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use Cake\Utility\Text;

/**
 * TwoFactorAuth Controller
 * Handles 2FA workflows including verification code sending and validation
 *
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class TwoFactorAuthController extends AppController
{
    /**
     * @var \App\Model\Table\UsersTable
     */
    protected UsersTable $Users;

    /**
     * Initialize method.
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->fetchTable('Users');
        $this->Users = $usersTable;
    }

    /**
     * Before filter method.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event The event object.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to verify
        $this->Authentication->addUnauthenticatedActions([
            'verify',
            'resendCode',
        ]);
    }

    /**
     * Verify 2FA code
     *
     * @return \Cake\Http\Response|null
     */
    public function verify(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $redirect = $this->request->getQuery('redirect', ['_name' => 'home']);

            return $this->redirect($redirect);
        }
        // Get email from the session
        $session = $this->request->getSession();
        $email = $session->read('TwoFactor.email');

        if (!$email) {
            $this->Flash->error(__('Invalid 2FA verification attempt.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $this->set('email', $email);

        return null;
    }

    /**
     * Resend verification code
     *
     * @param \App\Service\TwoFactorService $firebaseService
     * @return \Cake\Http\Response|null
     */
    public function resendCode(TwoFactorService $firebaseService): ?Response
    {
        // Only handle POST requests
        $this->request->allowMethod(['post']);

        $session = $this->request->getSession();
        $email = $session->read('TwoFactor.email');

        if (!$email) {
            throw new BadRequestException('Invalid 2FA verification attempt');
        }

        // Generate a new verification code
        $code = $firebaseService->sendVerificationCode($email);

        // Send the code via email
        $mailer = new UserMailer('default');
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->findByEmail($email)->first();
        $mailer->twoFactorAuth($user, $code);
        $mailer->deliver();

        $this->Flash->success(__('A new verification code has been sent to your email.'));

        return $this->redirect(['action' => 'verify']);
    }

    /**
     * Get or generate a unique device identifier
     *
     * @param bool $forceNew Whether to force a new device ID
     * @return string Device identifier
     */
    private function getDeviceId(bool $forceNew = false): string
    {
        $cookie = $this->request->getCookie('trusted_device');

        if (!$forceNew && $cookie) {
            return $cookie;
        }

        // Generate a new random device ID
        return Text::uuid();
    }
}
