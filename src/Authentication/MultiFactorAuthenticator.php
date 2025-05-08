<?php
declare(strict_types=1);

namespace App\Authentication;

use App\Mailer\UserMailer;
use App\Service\TwoFactorService;
use Authentication\Authenticator\AbstractAuthenticator;
use Authentication\Authenticator\FormAuthenticator;
use Authentication\Authenticator\Result;
use Authentication\Authenticator\ResultInterface;
use Authentication\Identifier\IdentifierInterface;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;
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
        'sessionKey' => 'TwoFactorUser',
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
     * TwoFactorService instance.
     *
     * @var \App\Service\TwoFactorService
     */
    protected TwoFactorService $twoFactorService;

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
        if (empty($config['twoFactorService']) || !$config['twoFactorService'] instanceof TwoFactorService) {
            throw new InvalidArgumentException('MultiFactorAuthenticator requires a TwoFactorService');
        }
        $this->twoFactorService = $config['twoFactorService'];
    }

    /**
     * Set the Firebase service.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Authentication\Authenticator\ResultInterface
     * @throws \Random\RandomException
     */
    public function authenticate(ServerRequestInterface $request): ResultInterface
    {
        $session = $request->getAttribute('session');
        $path = $request->getUri()->getPath();
        $verifyUrl = $this->_config['verifyUrl'];

        // ————————————— VERIFY STAGE —————————————
        if (str_ends_with($verifyUrl, $path)) {
            $data = $session->read($this->_config['sessionKey']);
            $userId = $data['id'] ?? null;
            $body = (array)$request->getParsedBody();
            $code = $body['verification_code'] ?? null;

            if (!$userId || !$code || !$this->twoFactorService->verifyCode($userId, $code)) {
                return new Result(null, ResultInterface::FAILURE_CREDENTIALS_INVALID);
            }

            $user = TableRegistry::getTableLocator()
                ->get('Users')
                ->get($userId);

            // clear session state
            $session->delete($this->_config['sessionKey']);

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

        $cookieParams = $request->getCookieParams();
        $deviceId = $cookieParams['trusted_device'] ?? null;

        if (!$this->twoFactorService->shouldRequire2FA($user->user_id, $deviceId)) {
            return new Result($user, ResultInterface::SUCCESS);
        }

        // 2FA required → generate & email code, stash in session
        $code = $this->twoFactorService->generateCode($user->user_id);
        $session->write($this->_config['sessionKey'], [
            'id' => $user->user_id,
            'redirect' => $request->getQueryParams()['redirect'] ?? ['_name' => 'home'],
        ]);

        $mailer = new UserMailer('default');
        $mailer->twoFactorAuth($user, $code);
        $mailer->deliver();

        // signal middleware to redirect to verify
        return new Result(null, '2FA_REQUIRED');
    }
}
