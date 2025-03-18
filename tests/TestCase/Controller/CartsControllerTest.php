<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\CartsController Test Case
 *
 * @uses \App\Controller\CartsController
 */
class CartsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Artworks',
        'app.ArtworkCarts',
        'app.Carts',
    ];

    /**
     * Test Case 1.1: Add Item to Cart (Logged-in User)
     */
    public function testAddItemToCartLoggedInWithoutView(): void
    {
        $this->enableCsrfToken();

        // Simulate logged-in user and initialize an empty cart in session.
        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
            'Cart.items' => [],
        ]);

        // artwork ID from fixtures.
        $artworkId = '5492e85e-f1b2-41f5-85cb-bfbe115b69ea';
        $data = ['artwork_id' => $artworkId];

        // POST request to add the item.
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();
        $this->assertFlashMessage('Item added to cart.');

        $cartItems = $this->getSession()->read('Cart.items');
        $this->assertNotEmpty($cartItems, 'Cart should contain at least one item.');

        $found = false;
        foreach ($cartItems as $item) {
            if ($item->artwork_id === $artworkId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'The artwork should be added to the cart in the session.');
    }

    /**
     * Test Case 1.2: Add Item to Cart (Non-logged-in User)
     */
    public function testAddItemToCartNonLoggedIn(): void
    {
        $this->enableCsrfToken();

        // No session set, simulate non-logged in user.
        $artworkId = '8424e85e-f1b2-41f5-85cb-bfbe115b45bc';
        $data = ['artwork_id' => $artworkId];

        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();
        debug($this->getSession());
        $this->assertFlashMessage('Item added to cart.');

        $cartItems = $this->getSession()->read('Cart.items');
        $this->assertNotEmpty($cartItems, 'Cart should contain at least one item.');

        $found = false;
        foreach ($cartItems as $item) {
            if ($item->artwork_id === $artworkId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'The artwork should be added to the cart in the session.');
    }

    /**
     * Test Case 1.3: Preventing Duplicate Items in Cart
     */
    public function testPreventDuplicateItems(): void
    {
        $this->enableCsrfToken();

        $artworkId = '5492e85e-f1b2-41f5-85cb-bfbe115b69ea';
        $data = ['artwork_id' => $artworkId];

        // First addition should succeed.
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();

        // Attempt to add the same item again.
        $this->post('/carts/add', $data);
        // Expect a message that the item is already in the cart.
        $this->assertFlashMessage('Item already in cart.');
    }

    /**
     * Test Case: Do not add a sold item to cart.
     */
    public function testAddSoldItemToCart(): void
    {
        $this->enableCsrfToken();

        $artworkId = 'artwork-sold';

        // Simulate logged-in user.
        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);

        // Try to add a sold artwork.
        $this->post('/carts/add', ['artwork_id' => $artworkId]);
        $this->assertResponseSuccess();
        // Verify that the response contains the error message.
        $this->assertFlashMessage('Artwork is not available.');
    }

    /**
     * Test Case: Do not add a deleted item to cart.
     */
    public function testAddDeletedItemToCart(): void
    {
        $this->enableCsrfToken();

        $artworkId = 'artwork-deleted';

        // Simulate logged-in user.
        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);

        // Try to add a deleted artwork.
        $this->post('/carts/add', ['artwork_id' => $artworkId]);
        $this->assertResponseSuccess();

        $this->assertFlashMessage('Artwork is not available.');
    }

    /**
     * Test Case 1.4: Remove Item from Cart
     */
    public function testRemoveItemFromCart(): void
    {
        $this->enableCsrfToken();

        $artworkId = '5492e85e-f1b2-41f5-85cb-bfbe115b69ea';
        $data = ['artwork_id' => $artworkId];

        // Add item to cart.
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();

        // Now remove the item.
        $this->post('/carts/remove', $data);
        $this->assertResponseSuccess();

        // Verify that the item is no longer in the cart.
        $this->get('/carts');
        $this->assertResponseNotContains('Sunset Over the Ocean');
    }

    /**
     * Test Case 1.5: Cart Persistence During Session
     */
    public function testCartPersistenceDuringSession(): void
    {
        $this->enableCsrfToken();

        $artworkId = '5492e85e-f1b2-41f5-85cb-bfbe115b69ea';
        $data = ['artwork_id' => $artworkId];

        // Add an item to the cart.
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();

        // Simulate navigating to another page.
        $this->get('/some/other/page');
        // Check that the cart still contains the item.
        $this->get('/carts');
        $this->assertResponseContains('Sunset Over the Ocean');
    }

    /**
     * Test Case 1.6: Cart Persistence After Logout
     */
    public function testCartPersistenceAfterLogout(): void
    {
        $this->enableCsrfToken();

        // Assume business rule: cart is cleared after logout.
        $artworkId = '5492e85e-f1b2-41f5-85cb-bfbe115b69ea';
        $this->session([
            'Auth.User' => [
                'user_id' => 'user-uuid',
                'email' => 'user@example.com',
            ],
        ]);

        // Add an item to the cart.
        $data = ['artwork_id' => $artworkId];
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();

        // Logout.
        $this->get('/users/logout');
        $this->assertRedirect();

        // After logout, view cart should not contain the item.
        $this->get('/carts');
        $this->assertResponseNotContains('Sunset Over the Ocean');
    }

    /**
     * Test that deleted artwork items do not appear in the cart index.
     *
     * @return void
     */
    public function testIndexDoesNotShowDeletedArt(): void
    {
        // Simulate a logged-in user with user_id "user-1234" and a fixed session id.
        $this->session([
            'Auth' => [
                'user_id' => 'user-1234',
            ],
        ]);

        $this->get('/carts');
        $this->assertResponseOk();

        $this->assertResponseContains('Valid Art');

        $this->assertResponseNotContains('Deleted Art');
    }

    /**
     * Test that sold artwork items do not appear in the cart index.
     *
     * @return void
     */
    public function testIndexDoesNotShowSoldArt(): void
    {
        $this->session([
            'Auth' => [
                'user_id' => 'user-1234',
            ],
        ]);

        $this->get('/carts');
        $this->assertResponseOk();

        $this->assertResponseContains('Valid Art');

        $this->assertResponseNotContains('Sold Art');
    }

    /**
     * Test Case 1.7: Checkout via Bank Transfer
     */
    public function testCheckoutViaBankTransfer(): void
    {
        $this->enableCsrfToken();

        // Simulate logged in user.
        $this->session([
            'Auth.User' => [
                'user_id' => 'user-uuid-7',
                'email' => 'user7@example.com',
            ],
        ]);

        // Add an item to the cart.
        $artworkId = 'artwork-uuid-7';
        $data = ['artwork_id' => $artworkId];
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();

        // Proceed to checkout using bank transfer.
        $checkoutData = ['payment_method' => 'bank_transfer'];
        $this->post('/carts/checkout', $checkoutData);
        $this->assertResponseSuccess();

        // Verify order confirmation.
        $this->assertResponseContains('order confirmation');
    }

    /**
     * Test Case 1.8: Inventory Synchronization
     */
    public function testInventorySynchronization(): void
    {
        $this->enableCsrfToken();

        // Assume an artwork is available with a unique copy.
        $artworkId = 'artwork-uuid-8';
        // Simulate logged in user.
        $this->session([
            'Auth.User' => [
                'user_id' => 'user-uuid-8',
                'email' => 'user8@example.com',
            ],
        ]);

        // Add the artwork to the cart and checkout.
        $data = ['artwork_id' => $artworkId];
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();
        $checkoutData = ['payment_method' => 'bank_transfer'];
        $this->post('/carts/checkout', $checkoutData);
        $this->assertResponseSuccess();

        // After checkout, assume the artwork's status is updated to 'sold'.
        $artworksTable = $this->getTableLocator()->get('Artworks');
        $artwork = $artworksTable->find()->where(['artwork_id' => $artworkId])->first();
        $this->assertEquals('sold', $artwork->availability_status, 'Artwork should be marked as sold after purchase.');
    }

    /**
     * Test Case 1.9: Session Expiration and Cart
     */
    public function testSessionExpirationAndCart(): void
    {
        $this->enableCsrfToken();

        $artworkId = '5492e85e-f1b2-41f5-85cb-bfbe115b69ea';
        $data = ['artwork_id' => $artworkId];

        // Add an item to the cart.
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();

        // Simulate session expiration by clearing session data.
        $this->session([]); // Clear session

        // Attempt to view the cart; expect that the cart contents are cleared.
        $this->get('/carts');
        $this->assertResponseNotContains('Sunset Over the Ocean');
    }
}
