<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    protected UsersTable $Users;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];

        /** @var \App\Model\Table\UsersTable $users */
        $users = $this->getTableLocator()->get('Users', $config);
        $this->Users = $users;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->getTableLocator()->clear();

        parent::tearDown();
    }

    /**
     * Test Case 1.1: Successful Registration with Valid Data
     *
     * @return void
     */
    public function testSuccessfulRegistration(): void
    {
        $data = [
            'first_name'   => 'Alice',
            'last_name'    => 'Smith',
            'email'        => 'alice.smith@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-1234',
            'address'      => '456 Another St',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $this->assertEmpty($user->getErrors(), 'There should be no validation errors.');
        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User should be saved successfully.');

        $savedUser = $this->Users->get($result->user_id);
        $this->assertNotEmpty($savedUser->user_id, 'User ID should be generated.');
        $this->assertFalse($savedUser->is_deleted, 'Newly created user should have is_deleted set to false by default.');
    }

    /**
     * Test Case 1.2: Registration with Missing Required Fields
     *
     * @return void
     */
    public function testRegistrationMissingRequiredFields(): void
    {
        $data = [
            // missing first_name and email
            'last_name'    => 'Smith',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-1234',
            'address'      => '456 Another St',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $errors = $user->getErrors();
        $this->assertArrayHasKey('first_name', $errors, 'Missing first_name should trigger an error.');
        $this->assertArrayHasKey('email', $errors, 'Missing email should trigger an error.');
        $this->assertFalse($this->Users->save($user), 'User with missing fields should not be saved.');
    }

    /**
     * Test Case 1.3: Registration with Invalid Email Format
     *
     * @return void
     */
    public function testRegistrationInvalidEmailFormat(): void
    {
        $data = [
            'first_name'   => 'Alice',
            'last_name'    => 'Smith',
            'email'        => 'user@invalid',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-1234',
            'address'      => '456 Another St',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $errors = $user->getErrors();
        $this->assertArrayHasKey('email', $errors, 'Invalid email should be flagged.');
        $this->assertFalse($this->Users->save($user), 'User with invalid email should not be saved.');
    }

    /**
     * Test Case 1.4: Registration with Duplicate Email
     *
     * @return void
     */
    public function testRegistrationDuplicateEmail(): void
    {
        $data = [
            'first_name'   => 'Alice',
            'last_name'    => 'Smith',
            'email'        => 'duplicate@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-1234',
            'address'      => '456 Another St',
            'user_type'    => 'customer',
        ];
        // First registration should succeed.
        $user1 = $this->Users->newEntity($data);
        $result = $this->Users->save($user1);
        $this->assertNotFalse($result, 'User should be saved successfully.');

        // Second registration with the same email should fail.
        $data['first_name'] = 'Bob';
        $user2 = $this->Users->newEntity($data);
        $this->assertFalse($this->Users->save($user2), 'Duplicate email should not be allowed.');
    }

    /**
     * Test Case 1.6: Password Complexity
     *
     * @return void
     */
    public function testPasswordComplexity(): void
    {
        $data = [
            'first_name'   => 'Charlie',
            'last_name'    => 'Brown',
            'email'        => 'charlie.brown@example.com',
            'password'     => '12345', // weak password
            'phone_number' => '555-6789',
            'address'      => '789 Some St',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $errors = $user->getErrors();
        $this->assertArrayHasKey('password', $errors, 'Weak password should trigger a validation error.');
        $this->assertFalse($this->Users->save($user), 'User with weak password should not be saved.');
    }

    /**
     * Test Case 2.4: Login Attempt for a Soft-Deleted User
     *
     * @return void
     */
    public function testLoginSoftDeletedUser(): void
    {
        $data = [
            'first_name'   => 'Frank',
            'last_name'    => 'White',
            'email'        => 'frank.white@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-3333',
            'address'      => '3030 Demo Ln',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User should be saved successfully.');
        $savedUser = $this->Users->get($result->user_id);
        $this->assertFalse($savedUser->is_deleted, 'User should not be soft-deleted initially.');

        $user->is_deleted = true;
        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User should be saved successfully.');
        $savedUser = $this->Users->get($result->user_id);
        $this->assertTrue($savedUser->is_deleted, 'Soft-deleted user should have is_deleted set to 1.');
    }

    /**
     * Test Case 2.5: Verify last_login Field Update
     *
     * @return void
     */
    public function testLastLoginUpdate(): void
    {
        $data = [
            'first_name'   => 'David',
            'last_name'    => 'Green',
            'email'        => 'david.green@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-0000',
            'address'      => '1010 Test Ave',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $this->Users->save($user);

        $originalLastLogin = $user->last_login;
        // Simulate successful login by updating last_login.
        $user->last_login = DateTime::now();
        $this->Users->save($user);
        $this->assertNotEquals($originalLastLogin, $user->last_login, 'last_login should update on login.');
    }

    /**
     * Test Case 3.4: Verify Password Hashing
     *
     * @return void
     */
    public function testPasswordHashing(): void
    {
        $password = 'PlainText@Password1';
        $data = [
            'first_name'   => 'Test',
            'last_name'    => 'User',
            'email'        => 'test.user@example.com',
            'password'     => $password,
            'phone_number' => '555-1111',
            'address'      => '123 Test Blvd',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User should be saved successfully.');

        $savedPassword = $result->password;

        // Assert that the saved password is not null
        $this->assertNotNull($savedPassword, 'Password hash should not be null.');

        // Assert that the saved password does not match the plaintext
        $this->assertNotEquals($password, $savedPassword, 'Password should be hashed and not match plaintext.');

        // Verify that the password hash verifies the original password
        $hasher = new DefaultPasswordHasher();
        $this->assertTrue($hasher->check($password, $savedPassword), 'The password hash should verify the original plaintext password.');
    }

    /**
     * Test Case 4.2: Handling Special Characters and Unicode in Registration
     *
     * @return void
     */
    public function testSpecialCharactersAndUnicode(): void
    {
        $data = [
            'first_name'   => 'Àlïçé-测试測試', // Includes accented letters and Chinese characters
            'last_name'    => 'O’Conñór',
            'email'        => 'special.chars@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '+123-456-7890',
            'address'      => '123 Emoji 😀 St',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $this->assertEmpty($user->getErrors(), 'Special characters and Unicode should not trigger errors.');
        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User with special characters should be saved successfully.');
    }

    /**
     * Test Case 5.1: Traditional Registration Requires Password
     *
     * @return void
     */
    public function testTraditionalRegistrationRequiresPassword(): void
    {
        $data = [
            'first_name'   => 'Test',
            'last_name'    => 'User',
            'email'        => 'test.user@example.com',
            'password'     => '', // empty password
            'phone_number' => '1234567890',
            'address'      => 'Test Address',
            'user_type'    => 'customer',
        ];
        $user = $this->Users->newEntity($data);
        $errors = $user->getErrors();
        $this->assertArrayHasKey('password', $errors, 'Password is required for traditional registration.');
        $this->assertFalse($this->Users->save($user), 'Traditional registration without a password should not be saved.');
    }

    /**
     * Test Case 5.2: OAuth Registration Allows Empty Password
     *
     * @return void
     */
    public function testOauthRegistrationAllowsEmptyPassword(): void
    {
        // Data for OAuth registration. Here, providing an oauth_provider flag
        $data = [
            'first_name'   => 'OAuth',
            'last_name'    => 'User',
            'email'        => 'oauth.user@example.com',
            'password'     => '',  // Password is empty
            'phone_number' => '1234567890',
            'address'      => 'OAuth Address',
            'user_type'    => 'customer',
            'oauth_provider' => 'google', // Indicates this is an OAuth registration.
        ];
        $user = $this->Users->newEntity($data);
        $errors = $user->getErrors();
        $this->assertEmpty($errors['password'] ?? [], 'Password should be allowed to be empty for OAuth registration.');

        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User created via OAuth should be saved successfully without a password.');
    }
}
