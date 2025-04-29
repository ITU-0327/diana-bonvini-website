<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\User;
use Cake\Mailer\Mailer;

/**
 * User mailer.
 */
class UserMailer extends Mailer
{
    /**
     * Mailer's name.
     *
     * @var string
     */
    public static string $name = 'User';

    /**
     * Sends a reset password email.
     *
     * @param \App\Model\Entity\User $user The user entity.
     * @param string $resetLink The full URL to the reset password page.
     * @return void
     */
    public function resetPassword(User $user, string $resetLink): void
    {
        $this
            ->setTo($user->email)
            ->setSubject('Your Password Reset Request')
            ->setEmailFormat('html')
            ->setViewVars([
                'user' => $user,
                'resetLink' => $resetLink,
            ])
            ->viewBuilder()
            ->setTemplate('reset_password')
            ->setLayout('default');
    }
    
    /**
     * Sends a 2FA verification code email.
     *
     * @param \App\Model\Entity\User $user The user entity.
     * @param string $code The verification code.
     * @return void
     */
    public function twoFactorAuth(User $user, string $code): void
    {
        $this
            ->setTo($user->email)
            ->setSubject('Your Verification Code')
            ->setEmailFormat('html')
            ->setViewVars([
                'user' => $user,
                'code' => $code,
            ])
            ->viewBuilder()
            ->setTemplate('two_factor_auth')
            ->setLayout('default');
    }
}
