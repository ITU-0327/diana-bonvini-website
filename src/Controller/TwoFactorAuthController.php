<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\UsersTable;
use App\Service\TwoFactorService;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use DateTime;

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
     * @param \App\Service\TwoFactorService $twoFactorService
     * @return \Cake\Http\Response|null
     * @throws \Random\RandomException
     */
    public function verify(TwoFactorService $twoFactorService): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        $session = $this->request->getSession();

        if ($result && $result->isValid()) {
            /** @var \Authentication\Identity $identity */
            $identity = $this->Authentication->getIdentity();
            $user = $this->Users->get($identity->get('user_id'));

            // If they're registering, mark them verified
            if ($user->is_verified === false) {
                $user->is_verified = true;
                $this->Users->save($user);
            }

            if ($this->request->getData('trust_device')) {
                $deviceId = $this->getDeviceId(true);
                $twoFactorService->addTrustedDevice($user->user_id, $deviceId);
            }
            $redirect = $session->consume('TwoFactorUser.redirect') ?? ['_name' => 'home'];

            return $this->redirect($redirect);
        }
        // Get email from the session
        $userId = $session->read('TwoFactorUser.id') ?? null;

        if (!$userId) {
            $this->Flash->error(__('Invalid 2FA verification attempt.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        $email = $this->Users->get($userId)->email;

        $this->set('email', $email);

        return null;
    }

    /**
     * Resend verification code
     *
     * @param \App\Service\TwoFactorService $twoFactorService
     * @return \Cake\Http\Response|null
     * @throws \Random\RandomException
     */
    public function resendCode(TwoFactorService $twoFactorService): ?Response
    {
        $this->request->allowMethod(['post']);
        $session = $this->request->getSession();

        $data = $session->read('TwoFactorUser');
        $userId = $data['id'] ?? null;
        if (!$userId) {
            throw new BadRequestException('Invalid 2FA verification attempt');
        }

        // Generate a new verification code
        $twoFactorService->generateCode($userId);

        $this->Flash->success(__('A new verification code has been sent to your email.'));

        return $this->redirect(['action' => 'verify']);
    }

    /**
     * Get or generate a unique device identifier and set a 30-day cookie
     *
     * @param bool $forceNew Whether to force a new device ID
     * @return string Device identifier
     * @throws \Random\RandomException
     */
    private function getDeviceId(bool $forceNew = false): string
    {
        $cookies = $this->request->getCookieParams();
        if (!$forceNew && !empty($cookies['trusted_device'])) {
            return $cookies['trusted_device'];
        }

        // Create a new 32-byte hex string (64 chars)
        $deviceId = bin2hex(random_bytes(32));

        // And set a secure, HttpOnly, SameSite=Lax cookie for 30 days
        $expiry = new DateTime('+30 days');
        $cookie = new Cookie(
            'trusted_device',
            $deviceId,
            $expiry,
            '/',
            null,
            !Configure::read('debug'),
            true,
            'Lax',
        );
        $this->response = $this->response->withCookie($cookie);

        return $deviceId;
    }
}
