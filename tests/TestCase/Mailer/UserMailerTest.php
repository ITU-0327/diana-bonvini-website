<?php
declare(strict_types=1);

namespace App\Test\TestCase\Mailer;

use App\Mailer\UserMailer;
use App\Model\Entity\User;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Mailer\UserMailer Test Case
 */
class UserMailerTest extends TestCase
{
    use EmailTrait;

    /**
     * Test Case: Reset Password Email
     *
     * This test verifies that the reset password email is sent to the correct
     * recipient with the expected subject and content.
     *
     * @return void
     */
    public function testResetPasswordEmail(): void
    {
        // Create a dummy user entity.
        $user = new User([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        // Set a dummy reset link.
        $resetLink = 'http://example.com/users/reset-password/token123';

        // Instantiate the UserMailer and send the email.
        $mailer = new UserMailer('default');
        $mailer->resetPassword($user, $resetLink);
        $mailer->deliver();

        // Assert that an email was sent to the user.
        $this->assertMailSentTo('john.doe@example.com');

        // Assert that the email subject contains the correct text.
        $this->assertMailSubjectContains('Your Password Reset Request');

        // Assert that the email body contains expected content.
        $this->assertMailContainsHtml($resetLink);
        $this->assertMailContainsHtml('Please click the following link to reset your password');
    }
}
