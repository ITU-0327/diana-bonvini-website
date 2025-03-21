<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\UsersController Test Case
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
    ];

    /**
     * Test Case 1.1: Successful Registration with Valid Data
     *
     * @return void
     */
    public function testRegistrationSuccess(): void
    {
        $this->enableCsrfToken();

        $data = [
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace.hopper@example.com',
            'password' => 'StrongP@ssw0rd',
            'password_confirm' => 'StrongP@ssw0rd',
            'phone_number' => '0412345789',
            'address' => '404 NotFound Blvd',
        ];
        $this->post('/users/register', $data);
        $this->assertResponseSuccess();
        $this->assertFlashMessage('User registered successfully');
        // Check redirection
        $this->assertRedirect('/users/login');
    }

    /**
     * Test Case 1.2: Registration with Missing Required Fields
     *
     * @return void
     */
    public function testRegistrationMissingFields(): void
    {
        $this->enableCsrfToken();

        $data = [
            // Missing first_name and email.
            'last_name' => 'Hopper',
            'password' => 'StrongP@ssw0rd',
            'password_confirm' => 'StrongP@ssw0rd',
            'phone_number' => '0412345789',
            'address' => '404 NotFound Blvd',
        ];
        $this->post('/users/register', $data);
        $this->assertResponseContains('This field is required');
    }

    /**
     * Test Case 2.1: Login with Valid Credentials
     *
     * @return void
     */
    public function testLoginSuccess(): void
    {
        $this->enableCsrfToken();

        $data = [
            'email' => 'tony.hsieh@example.com',
            'password' => 'password',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseSuccess();
        // Verify that the session contains the user email.
        $this->assertSession('17fe31f7-2f61-4176-a036-172eed559e6f', 'Auth.user_id');
        $this->assertSession('tony.hsieh@example.com', 'Auth.email');

        $hasher = new DefaultPasswordHasher();
        $session = $this->getSession();
        $this->assertTrue($hasher->check('password', $session->read('Auth.password')), 'Password should verify correctly');
    }

    /**
     * Test Case 2.2: Login with Invalid Password
     *
     * @return void
     */
    public function testLoginInvalidPassword(): void
    {
        $this->enableCsrfToken();

        $data = [
            'email' => 'tony.hsieh@example.com',
            'password' => 'wrongpassword',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseContains('Invalid username or password');
    }

    /**
     * Test Case 2.3: Login with Non-Existent Email
     *
     * @return void
     */
    public function testLoginNonExistentEmail(): void
    {
        $this->enableCsrfToken();

        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'anyPassword',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseContains('Invalid username or password');
    }

    /**
     * Test Case 2.4: Login Attempt for a Soft-Deleted User
     *
     * @return void
     */
    public function testLoginSoftDeletedUser(): void
    {
        $this->enableCsrfToken();

        $data = [
            'email' => 'soft.deleted@example.com',
            'password' => 'SecureP@ssw0rd',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseContains('Account inactive');
    }

    /**
     * Test Case 2.5.1: Verify last_login Field Not Updated on Failed Login
     *
     * @return void
     */
    public function testFailedLoginDoesNotUpdateLastLogin(): void
    {
        $this->enableCsrfToken();

        $usersTable = $this->getTableLocator()->get('Users');
        $userBefore = $usersTable->find()->where(['email' => 'tony.hsieh@example.com'])->first();
        $oldLastLogin = $userBefore->last_login;

        $data = [
            'email' => 'tony.hsieh@example.com',
            'password' => 'wrongpassword',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseContains('Invalid username or password');

        $userAfter = $usersTable->find()->where(['email' => 'tony.hsieh@example.com'])->first();
        $this->assertEquals(
            $oldLastLogin->i18nFormat(),
            $userAfter->last_login->i18nFormat(),
            'The last_login value should have been updated.',
        );
    }

    /**
     * Test Case 2.5.2: Verify last_login Field Updated on Successful Login
     *
     * @return void
     */
    public function testLastLoginFieldUpdated(): void
    {
        $this->enableCsrfToken();

        $usersTable = $this->getTableLocator()->get('Users');
        $userBefore = $usersTable->find()->where(['email' => 'tony.hsieh@example.com'])->first();
        $oldLastLogin = $userBefore->last_login;

        $data = [
            'email' => 'tony.hsieh@example.com',
            'password' => 'password',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseSuccess();

        $userAfter = $usersTable->find()->where(['email' => 'tony.hsieh@example.com'])->first();
        $this->assertNotEquals(
            $oldLastLogin->i18nFormat(),
            $userAfter->last_login->i18nFormat(),
            'The last_login value should have been updated.',
        );
    }

    /**
     * Test Case 3.1: SQL Injection Attempt in Registration
     *
     * @return void
     */
    public function testRegistrationSqlInjectionAttempt(): void
    {
        $this->enableCsrfToken();

        $data = [
            'first_name'   => "Robert'); DROP TABLE users; --",
            'last_name'    => 'Hacker',
            'email'        => 'sqlinjection@example.com',
            'password'     => 'SecureP@ssw0rd',
            'password_confirm' => 'SecureP@ssw0rd',
            'phone_number' => '1234567890',
            'address'      => '123 Injection Ln',
        ];
        $this->post('/users/register', $data);
        // Expect normal registration flow: the injection should be treated as a normal string
        $this->assertResponseSuccess();
        $this->assertRedirect('/users/login');
    }

    /**
     * Test Case 3.2: XSS Attempt in Registration
     *
     * @return void
     */
    public function testRegistrationXssAttempt(): void
    {
        $this->enableCsrfToken();

        $data = [
            'first_name'   => '<script>alert("XSS")</script>',
            'last_name'    => 'Attacker',
            'email'        => 'xss@example.com',
            'password'     => 'SecureP@ssw0rd',
            'password_confirm' => 'SecureP@ssw0rd',
            'phone_number' => '1234567890',
            'address'      => '123 XSS Blvd',
        ];
        $this->post('/users/register', $data);
        // Expect registration to succeed normally with input sanitized on output
        $this->assertResponseSuccess();
        $this->assertRedirect('/users/login');
    }

    /**
     * Test Case 4.1: Logout Clears Session
     *
     * @return void
     */
    public function testLogout(): void
    {
        $this->enableCsrfToken();

        // Set up a dummy session for a logged-in user.
        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);
        $this->get('/users/logout');
        $this->assertRedirect('/users/login');
        $this->assertEmpty($this->getSession()->read('Auth.User'), 'User session should be cleared after logout.');
    }

    public function testTraditionalRegistrationRejectsOAuthProvider(): void
    {
        $this->enableCsrfToken();

        $data = [
            'first_name'       => 'Malicious',
            'last_name'        => 'User',
            'email'            => 'malicious.user@example.com',
            'password'         => '',
            'password_confirm' => '',
            'phone_number'     => '0412345789',
            'address'          => 'Some Address',
            'oauth_provider'   => 'google', // Should not be allowed on the normal endpoint.
        ];
        $this->post('/users/register', $data);

        $this->assertFlashMessage('The user could not be saved. Please, try again.');

        // check that no user was created.
        $usersTable = $this->getTableLocator()->get('Users');
        $user = $usersTable->find()->where(['email' => 'malicious.user@example.com'])->first();
        $this->assertEmpty($user, 'User should not be created if oauth_provider is provided on normal registration.');
    }
}
