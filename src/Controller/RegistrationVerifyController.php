<?php
declare(strict_types=1);

namespace App\Controller;

use App\Mailer\UserMailer;
use App\Service\FirebaseService;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Exception;

/**
 * RegistrationVerify Controller
 *
 * Handles email verification during registration process
 */
class RegistrationVerifyController extends AppController
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
        // Correctly load the Users model
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

        // Allow unauthenticated access to all actions
        $this->Authentication->addUnauthenticatedActions([
            'index',
            'verify',
            'resendCode',
        ]);
    }

    /**
     * Registration start method - collects user information and sends verification code
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
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

        $session = $this->request->getSession();

        // Clear any existing session data when the form is first loaded (GET request)
        if ($this->request->is('get') && !$this->request->getQuery('retry')) {
            $session->delete('Registration.data');
        }

        $registrationData = $session->read('Registration.data');

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Check if password and confirmation match
            if ($data['password'] !== $data['password_confirm']) {
                $this->Flash->error('Password and confirm password do not match');

                return null;
            }

            // Set the default user_type to 'customer'
            $data['user_type'] = 'customer';

            // Validate the registration data
            $usersTable = $this->fetchTable('Users');
            $user = $usersTable->newEmptyEntity();
            $user = $usersTable->patchEntity($user, $data);

            if (!empty($user->getErrors())) {
                // Instead of relying only on flash message, pass the entity with errors to the view
                $this->set('user', $user);

                return null;
            }

            // Store registration data in session for later use
            $session->write('Registration.data', $data);

            // Send verification code
            try {
                $code = $this->firebaseService->sendVerificationCode($data['email']);

                // Send verification email
                $user->email = $data['email'];
                $user->first_name = $data['first_name'];
                $user->last_name = $data['last_name'];
                $mailer = new UserMailer('default');
                $mailer->twoFactorAuth($user, $code);
                $mailer->deliver();

                $this->Flash->success(__('Please check your email for a verification code.'));

                return $this->redirect(['action' => 'verify']);
            } catch (Exception $e) {
                $this->Flash->error(__('There was a problem sending the verification email. Please try again.'));

                return null;
            }
        }

        // For GET request, create a fresh entity
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->newEmptyEntity();

        // If we have saved registration data, pre-fill the form
        if ($registrationData) {
            $user = $usersTable->patchEntity($user, $registrationData);
        }

        $this->set(compact('user'));
    }

    /**
     * Verify the email verification code and create user account
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

        $session = $this->request->getSession();
        $registrationData = $session->read('Registration.data');

        if (!$registrationData) {
            $this->Flash->error(__('Registration data not found. Please start over.'));

            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            $code = $this->request->getData('verification_code');

            if (empty($code)) {
                $this->Flash->error(__('Please enter the verification code.'));

                return null;
            }

            // Get any debug information about verification
            $debugInfo = '';
            if ($this->request->getSession()->check('LastVerificationCode')) {
                $lastCode = $this->request->getSession()->read('LastVerificationCode');
                if ($lastCode['email'] === $registrationData['email']) {
                    // For development mode, show the actual code in the log
                    if (Configure::read('debug')) {
                        $this->log("Verification Debug - Last code sent to {$lastCode['email']}: {$lastCode['code']} at {$lastCode['time']}");
                        $debugInfo = "Last verification code: {$lastCode['code']}";
                    }
                }
            }

            // In development mode, always accept the code
            $codeVerified = $this->firebaseService->verifyCode($registrationData['email'], $code);

            if ($codeVerified) {
                // Code is valid, create the user account
                $usersTable = $this->fetchTable('Users');
                $user = $usersTable->newEmptyEntity();

                // Ensure user_type is set to 'customer'
                if (!isset($registrationData['user_type']) || empty($registrationData['user_type'])) {
                    $registrationData['user_type'] = 'customer';
                }

                $user = $usersTable->patchEntity($user, $registrationData);

                if ($usersTable->save($user)) {
                    // Clear session data
                    $session->delete('Registration.data');
                    $session->delete('LastVerificationCode');
                    $session->delete('VerificationCodes');

                    $this->Flash->success(__('Your account has been created successfully. You can now log in.'));

                    return $this->redirect(['controller' => 'Users', 'action' => 'login']);
                } else {
                    $this->Flash->error(__('There was a problem creating your account. Please try again.'));
                    if (Configure::read('debug')) {
                        $this->log('User save errors: ' . json_encode($user->getErrors()));
                    }
                }
            } else {
                // For debugging, let's check what went wrong
                if (Configure::read('debug')) {
                    $this->Flash->error(__('Invalid verification code. Please try again. ' . $debugInfo));
                    $this->log("Code verification failed for {$registrationData['email']} - User entered: $code");
                } else {
                    $this->Flash->error(__('Invalid verification code. Please try again.'));
                }
            }
        }

        $this->set('email', $registrationData['email'] ?? '');
    }

    /**
     * Resend verification code
     *
     * @return \Cake\Http\Response|null
     */
    public function resendCode()
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

        $session = $this->request->getSession();
        $registrationData = $session->read('Registration.data');

        if (!$registrationData || empty($registrationData['email'])) {
            $this->Flash->error(__('Registration data not found. Please start over.'));

            return $this->redirect(['action' => 'index']);
        }

        try {
            $code = $this->firebaseService->sendVerificationCode($registrationData['email']);

            // Send verification email
            $usersTable = $this->fetchTable('Users');
            $user = $usersTable->newEmptyEntity();
            $user->email = $registrationData['email'];
            $user->first_name = $registrationData['first_name'];
            $user->last_name = $registrationData['last_name'];

            $mailer = new UserMailer('default');
            $mailer->twoFactorAuth($user, $code);
            $mailer->deliver();

            $this->Flash->success(__('A new verification code has been sent to your email.'));
        } catch (Exception $e) {
            $this->Flash->error(__('There was a problem sending the verification email. Please try again.'));
        }

        return $this->redirect(['action' => 'verify']);
    }
}
