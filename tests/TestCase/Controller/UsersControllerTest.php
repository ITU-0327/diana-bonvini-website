<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\UsersController Test Case
 *
 * @uses \App\Controller\UsersController
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
        $data = [
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace.hopper@example.com',
            'password' => 'StrongP@ssw0rd',
            'phone_number' => '0412345789',
            'address' => '404 NotFound Blvd',
            'user_type' => 'customer',
        ];
        $this->post('/users/register', $data);
        $this->assertResponseSuccess();
        $this->assertResponseContains('User registered successfully');
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
        $data = [
            // Missing first_name and email.
            'last_name' => 'Hopper',
            'password' => 'StrongP@ssw0rd',
            'phone_number' => '0412345789',
            'address' => '404 NotFound Blvd',
            'user_type' => 'customer',
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
        $data = [
            'email' => 'tony.hsieh@example.com',
            'password' => 'password', // Ensure that the fixture’s hashed password verifies against this.
        ];
        $this->post('/users/login', $data);
        $this->assertResponseSuccess();
        // Verify that the session contains the user email.
        $this->assertSession('tony.hsieh@example.com', 'Auth.User.email');
    }

    /**
     * Test Case 2.2: Login with Invalid Password
     *
     * @return void
     */
    public function testLoginInvalidPassword(): void
    {
        $data = [
            'email' => 'tony.hsieh@example.com',
            'password' => 'wrongpassword',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseContains('Invalid credentials');
    }

    /**
     * Test Case 2.3: Login with Non-Existent Email
     *
     * @return void
     */
    public function testLoginNonExistentEmail(): void
    {
        $data = [
            'email' => 'nonexistent@example.com',
            'password' => 'anyPassword',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseContains('Invalid credentials');
    }

    /**
     * Test Case 2.4: Login Attempt for a Soft-Deleted User
     *
     * @return void
     */
    public function testLoginSoftDeletedUser(): void
    {
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
        $usersTable = $this->getTableLocator()->get('Users');
        $userBefore = $usersTable->find()->where(['email' => 'tony.hsieh@example.com'])->first();
        $oldLastLogin = $userBefore->last_login;

        $data = [
            'email' => 'tony.hsieh@example.com',
            'password' => 'wrongpassword',
        ];
        $this->post('/users/login', $data);
        $this->assertResponseContains('Invalid credentials');

        $userAfter = $usersTable->find()->where(['email' => 'tony.hsieh@example.com'])->first();
        $this->assertSame($oldLastLogin, $userAfter->last_login);
    }

    /**
     * Test Case 2.5.2: Verify last_login Field Updated on Successful Login
     *
     * @return void
     */
    public function testLastLoginFieldUpdated(): void
    {
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
        $this->assertNotSame($oldLastLogin, $userAfter->last_login);
    }

    /**
     * Test Case 3.1: SQL Injection Attempt in Registration
     *
     * @return void
     */
    public function testRegistrationSqlInjectionAttempt(): void
    {
        $data = [
            'first_name'   => "Robert'); DROP TABLE users; --",
            'last_name'    => 'Hacker',
            'email'        => 'sqlinjection@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '1234567890',
            'address'      => '123 Injection Ln',
            'user_type'    => 'customer',
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
        $data = [
            'first_name'   => '<script>alert("XSS")</script>',
            'last_name'    => 'Attacker',
            'email'        => 'xss@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '1234567890',
            'address'      => '123 XSS Blvd',
            'user_type'    => 'customer',
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
        // Set up a dummy session for a logged-in user.
        $this->session([
            'Auth.User' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);
        $this->get('/users/logout');
        $this->assertRedirect('/');
        $this->assertEmpty($this->_session('Auth.User'), 'User session should be cleared after logout.');
    }
}
