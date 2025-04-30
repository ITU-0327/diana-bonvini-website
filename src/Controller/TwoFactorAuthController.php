<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\UserMailer;
use App\Service\FirebaseService;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\DateTime;
use Cake\Utility\Text;

/**
 * TwoFactorAuth Controller
 *
 * Handles 2FA workflows including verification code sending and validation
 */
class TwoFactorAuthController extends AppController
{
    /**
     * @var \App\Service\FirebaseService
     */
    private FirebaseService $firebaseService;

    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->firebaseService = new FirebaseService();
        $this->fetchTable('Users');
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
    public function verify()
    {
        // For development, disable CSRF validation and add extra bypass
        // This is a TEMPORARY solution for development only
        if (Configure::read('debug')) {
            // Only try to disable FormProtection if it exists
            if (isset($this->FormProtection)) {
                $this->getEventManager()->off($this->FormProtection);
            }

            // For extreme cases, manually bypass CSRF token check
            $request = $this->request;
            if ($request->is('post')) {
                $request = $request->withData('_csrfToken', 'debug-bypass-token');
                $request = $request->withAttribute('csrfToken', true);
                $this->setRequest($request);
            }
        }

        // Get email from session
        $session = $this->request->getSession();
        $email = $session->read('Auth.2FA.email');

        if (!$email) {
            $this->Flash->error(__('Invalid 2FA verification attempt.'));

            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        if ($this->request->is('post')) {
            $code = $this->request->getData('verification_code');

            if (empty($code)) {
                $this->Flash->error(__('Please enter the verification code.'));

                return null;
            }

            // Verify the code
            if ($this->firebaseService->verifyCode($email, $code)) {
                // Get user from database
                $usersTable = $this->fetchTable('Users');
                $user = $usersTable->findByEmail($email)->first();

                if (!$user) {
                    $this->Flash->error(__('User not found.'));

                    return $this->redirect(['controller' => 'Users', 'action' => 'login']);
                }

                // Update last login time
                $user->last_login = DateTime::now();
                $usersTable->save($user);

                // Set auth identity
                $this->Authentication->setIdentity($user);

                // Check if "remember device" was selected
                if ($this->request->getData('trust_device')) {
                    // Generate device ID if not present
                    $deviceId = $this->getDeviceId(true);
                    $this->firebaseService->addTrustedDevice($email, $deviceId);

                    // Set a persistent cookie for this trusted device
                    $cookie = new Cookie(
                        'trusted_device',
                        $deviceId,
                        new DateTime('+30 days'), // 30-day cookie
                        '/',
                        '',
                        true, // secure
                        true,  // httpOnly
                    );

                    // Add SameSite attribute
                    $cookie = $cookie->withSameSite('Lax');

                    // In development environments, allow non-HTTPS cookies
                    if (Configure::read('debug')) {
                        $cookie = $cookie->withSecure(false);
                    }

                    $this->response = $this->response->withCookie($cookie);
                }

                // Clean up session data
                $session->delete('Auth.2FA');

                // Update login metadata for risk assessment
                $this->firebaseService->updateLoginMetadata($email, [
                    'ip' => $this->request->clientIp(),
                    'time' => time(),
                ]);

                // Redirect to intended destination
                $redirect = $session->read('Auth.redirect') ?? ['_name' => 'home']; // Route to landing page
                $session->delete('Auth.redirect');

                return $this->redirect($redirect);
            } else {
                $this->Flash->error(__('Invalid verification code. Please try again.'));
            }
        }

        // Just display the verification form
        $this->set('email', $email);
    }

    /**
     * Resend verification code
     *
     * @return \Cake\Http\Response|null
     */
    public function resendCode()
    {
        // Only handle POST requests
        $this->request->allowMethod(['post']);

        // For development, disable CSRF validation and add extra bypass
        // This is a TEMPORARY solution for development only
        if (Configure::read('debug')) {
            // Only try to disable FormProtection if it exists
            if (isset($this->FormProtection)) {
                $this->getEventManager()->off($this->FormProtection);
            }

            // For extreme cases, manually bypass CSRF token check
            $request = $this->request;
            if ($request->is('post')) {
                $request = $request->withData('_csrfToken', 'debug-bypass-token');
                $request = $request->withAttribute('csrfToken', true);
                $this->setRequest($request);
            }
        }

        $session = $this->request->getSession();
        $email = $session->read('Auth.2FA.email');

        if (!$email) {
            throw new BadRequestException('Invalid 2FA verification attempt');
        }

        // Generate a new verification code
        $code = $this->firebaseService->sendVerificationCode($email);

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
