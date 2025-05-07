<?php
declare(strict_types=1);

namespace App\Authentication;

use App\Mailer\UserMailer;
use App\Service\FirebaseService;
use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\FormAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\Identifier\IdentifierInterface;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ServerRequestInterface;

class MultiFactorAuthenticator extends AbstractAuthenticator
{
    /**
     * Default configuration.
     *
     * - `loginUrl`: URL to redirect to if authentication fails.
     * - `verifyUrl`: URL to redirect to for 2FA verification.
     * - `sessionKey`: Session key for storing the 2FA code.
     * - `fields`: Fields used for authentication.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'loginUrl' => '/users/login',
        'verifyUrl' => '/two-factor-auth/verify',
        'sessionKey' => 'TwoFactor.code',
        'fields' => [
            'username' => 'email',
            'password' => 'password',
        ],
    ];

    /**
     * FormAuthenticator instance.
     *
     * @var \Authentication\Authenticator\FormAuthenticator
     */
    protected FormAuthenticator $formAuth;

    /**
     * Constructor
     *
     * @param \Authentication\Identifier\IdentifierInterface $identifier Identifier or identifiers collection.
     * @param array<string, mixed> $config Configuration settings.
     */
    public function __construct(IdentifierInterface $identifier, array $config = [])
    {
        parent::__construct($identifier, $config);
        // prepare an internal FormAuthenticator
        $this->formAuth = new FormAuthenticator($identifier, [
            'fields' => $this->_config['fields'],
            'loginUrl' => $this->_config['loginUrl'],
        ]);
    }

    /**
     * Set the Firebase service.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \App\Service\FirebaseService $firebaseService
     * @return \Authentication\Authenticator\ResultInterface
     */
    public function authenticate(ServerRequestInterface $request, FirebaseService $firebaseService = new FirebaseService()): ResultInterface
    {
        $session = $request->getAttribute('session');
        $path = $request->getUri()->getPath();
        $verifyUrl = $this->_config['verifyUrl'];

        // ————————————— VERIFY STAGE —————————————
        if (str_ends_with($verifyUrl, $path)) {
            // Pull email & code from the session
            $email = $session->read('TwoFactor.email');
            /** @var array<string,mixed> $body */
            $body = $request->getParsedBody();
            $postedCode = $body['verification_code'] ?? null;

            if (!$email || !$postedCode || !$firebaseService->verifyCode($email, $postedCode)) {
                return new Result(null, ResultInterface::FAILURE_CREDENTIALS_INVALID);
            }

            // Code is correct — load the User
            $session->delete($this->_config['sessionKey']);
            $session->delete('TwoFactor.email');

            $user = TableRegistry::getTableLocator()
                ->get('Users')->find()
                ->where(['email' => $email])
                ->firstOrFail();

            $firebaseService->updateLoginMetadata($email, [
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? null,
                'time' => time(),
            ]);

            return new Result($user, ResultInterface::SUCCESS);
        }

        // ————————————— LOGIN STAGE —————————————
        $formResult = $this->formAuth->authenticate($request);
        if (!$formResult->isValid()) {
            return $formResult;
        }

        /** @var \App\Model\Entity\User $user */
        $user = $formResult->getData();
        if ($user->is_deleted) {
            return new Result(
                null,
                ResultInterface::FAILURE_CREDENTIALS_INVALID,
                ['Account inactive'],
            );
        }

        // Risk payload
        $serverParams = $request->getServerParams();
        $cookies = $request->getCookieParams();
        $requestData = [
            'ip' => $serverParams['REMOTE_ADDR'] ?? null,
            'time' => time(),
            'deviceId' => $cookies['trusted_device'] ?? null,
        ];

        // No 2FA needed → update metadata & succeed
        if (!$firebaseService->shouldRequire2FA($user->email, $requestData)) {
            $firebaseService->updateLoginMetadata($user->email, $requestData);

            return new Result($user, ResultInterface::SUCCESS);
        }

        // 2FA required → generate & email code, stash in session
        $newCode = $firebaseService->sendVerificationCode($user->email);
        $session->write($this->_config['sessionKey'], $newCode);
        $session->write('TwoFactor.email', $user->email);
        $session->write('TwoFactor.redirect', $request->getQueryParams()['redirect'] ?? ['_name' => 'home']);

        // send mail
        $userEntity = TableRegistry::getTableLocator()
            ->get('Users')->find()
            ->where(['email' => $user->email])
            ->firstOrFail();

        $mailer = new UserMailer('default');
        $mailer->twoFactorAuth($userEntity, $newCode);
        $mailer->deliver();

        // signal middleware to redirect to verify
        return new Result(null, ResultInterface::FAILURE_CREDENTIALS_MISSING);
    }
}
