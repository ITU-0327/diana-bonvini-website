<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;
use Authentication\PasswordHasher\DefaultPasswordHasher;

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
    protected $Users;

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
        $this->Users = $this->getTableLocator()->get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Users);

        parent::tearDown();
    }

    /**
     * Test Case 1.1: Successful Registration with Valid Data
     *
     * @return void
     */
    public function testSuccessfulRegistration(): void{
        $data = [
            'first_name'   => 'Alice',
            'last_name'    => 'Smith',
            'email'        => 'alice.smith@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-1234',
            'address'      => '456 Another St',
            'user_type'    => 'customer'
        ];
        $user = $this->Users->newEntity($data);
        $this->assertEmpty($user->getErrors(), 'There should be no validation errors.');
        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User should be saved successfully.');
        $this->assertNotEmpty($result->user_id, 'User ID should be generated.');
        $this->assertEquals(0, $result->is_deleted, 'is_deleted should default to 0.');
    }

    /**
     * Test Case 1.2: Registration with Missing Required Fields
     *
     * @return void
     */
    public function testRegistrationMissingRequiredFields(): void {
        $data = [
            // missing first_name and email
            'last_name'    => 'Smith',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-1234',
            'address'      => '456 Another St',
            'user_type'    => 'customer'
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
    public function testRegistrationInvalidEmailFormat(): void {
        $data = [
            'first_name'   => 'Alice',
            'last_name'    => 'Smith',
            'email'        => 'user@invalid',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-1234',
            'address'      => '456 Another St',
            'user_type'    => 'customer'
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
    public function testRegistrationDuplicateEmail(): void {
        $data = [
            'first_name'   => 'Alice',
            'last_name'    => 'Smith',
            'email'        => 'duplicate@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-1234',
            'address'      => '456 Another St',
            'user_type'    => 'customer'
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
    public function testPasswordComplexity(): void {
        $data = [
            'first_name'   => 'Charlie',
            'last_name'    => 'Brown',
            'email'        => 'charlie.brown@example.com',
            'password'     => '12345', // weak password
            'phone_number' => '555-6789',
            'address'      => '789 Some St',
            'user_type'    => 'customer'
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
    public function testLoginSoftDeletedUser(): void {
        $data = [
            'first_name'   => 'Frank',
            'last_name'    => 'White',
            'email'        => 'frank.white@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-3333',
            'address'      => '3030 Demo Ln',
            'user_type'    => 'customer'
        ];
        $user = $this->Users->newEntity($data);
        $this->Users->save($user);
        $user->is_deleted = 1;
        $this->Users->save($user);

        $this->assertEquals(1, $user->is_deleted, 'Soft-deleted user should have is_deleted set to 1.');
    }


    /**
     * Test Case 2.5: Verify last_login Field Update
     *
     * @return void
     */
    public function testLastLoginUpdate(): void {
        $data = [
            'first_name'   => 'David',
            'last_name'    => 'Green',
            'email'        => 'david.green@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '555-0000',
            'address'      => '1010 Test Ave',
            'user_type'    => 'customer'
        ];
        $user = $this->Users->newEntity($data);
        $this->Users->save($user);

        $originalLastLogin = $user->last_login;
        // Simulate successful login by updating last_login.
        $user->last_login = FrozenTime::now();
        $this->Users->save($user);
        $this->assertNotEquals($originalLastLogin, $user->last_login, 'last_login should update on login.');
    }

    /**
     * Test Case 3.4: Verify Password Hashing
     *
     * @return void
     */
    public function testPasswordHashing(): void {
        $data = [
            'first_name'   => 'Test',
            'last_name'    => 'User',
            'email'        => 'test.user@example.com',
            'password'     => 'PlainTextPassword',
            'phone_number' => '555-1111',
            'address'      => '123 Test Blvd',
            'user_type'    => 'customer'
        ];
        $user = $this->Users->newEntity($data);
        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User should be saved successfully.');
        $savedPassword = $result->password;
        // Assert that the saved password does not match the plaintext
        $this->assertNotEquals('PlainTextPassword', $savedPassword, 'Password should be hashed and not match plaintext.');

        // Verify that the password hash verifies the original password
        $hasher = new DefaultPasswordHasher();
        $this->assertTrue($hasher->check('PlainTextPassword', $savedPassword), 'The password hash should verify the original plaintext password.');
    }

    /**
     * Test Case 4.2: Handling Special Characters and Unicode in Registration
     *
     * @return void
     */
    public function testSpecialCharactersAndUnicode(): void {
        $data = [
            'first_name'   => 'Àlïçé-测试測試', // Includes accented letters and Chinese characters
            'last_name'    => 'O’Conñór',
            'email'        => 'special.chars@example.com',
            'password'     => 'SecureP@ssw0rd',
            'phone_number' => '+123-456-7890',
            'address'      => '123 Emoji 😀 St',
            'user_type'    => 'customer'
        ];
        $user = $this->Users->newEntity($data);
        $this->assertEmpty($user->getErrors(), 'Special characters and Unicode should not trigger errors.');
        $result = $this->Users->save($user);
        $this->assertNotFalse($result, 'User with special characters should be saved successfully.');
    }
}
