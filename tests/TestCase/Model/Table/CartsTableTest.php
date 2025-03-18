<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CartsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CartsTable Test Case
 */
class CartsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CartsTable
     */
    protected $Carts;

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
        $config = $this->getTableLocator()->exists('Carts') ? [] : ['className' => CartsTable::class];
        /** @var \App\Model\Table\CartsTable $carts */
        $carts = $this->getTableLocator()->get('Carts', $config);
        $this->Carts = $carts;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Carts);

        parent::tearDown();
    }

    /**
     * Test Case: Create Cart for Logged-in User
     *
     * Objective: Verify that a cart can be created when a user is logged in.
     */
    public function testCreateCartForLoggedInUser(): void
    {
        $user_id = '17fe31f7-2f61-4176-a036-172eed559e6f';
        $data = ['user_id' => $user_id];
        $cart = $this->Carts->newEntity($data);
        $result = $this->Carts->save($cart);
        $this->assertNotFalse($result, 'Cart for logged-in user should be saved successfully.');
        $this->assertNotEmpty($result->cart_id, 'A cart_id should be generated.');
        $this->assertEquals($user_id, $result->user_id, 'The user_id should match the input.');
    }

    /**
     * Test Case: Create Cart for Guest User
     *
     * Objective: Verify that a cart can be created using a session_id for non-logged-in users.
     */
    public function testCreateCartForGuestUser(): void
    {
        $data = [
            'session_id' => 'session-12345',
        ];
        $cart = $this->Carts->newEntity($data);
        $result = $this->Carts->save($cart);
        $this->assertNotFalse($result, 'Cart for a guest user should be saved successfully.');
        $this->assertNotEmpty($result->cart_id, 'A cart_id should be generated.');
        $this->assertEquals('session-12345', $result->session_id, 'The session_id should match the input.');
    }

    /**
     * Test Case: Foreign Key Constraint on user_id
     *
     * Objective: Verify that saving a cart with a non-existent user_id fails.
     */
    public function testForeignKeyConstraint(): void
    {
        $data = ['user_id' => 'non-existent-user'];
        $cart = $this->Carts->newEntity($data);
        // Saving should fail because the user does not exist.
        $result = $this->Carts->save($cart);
        $this->assertFalse($result, 'Cart creation with a non-existent user_id should fail due to the foreign key constraint.');
    }
}
