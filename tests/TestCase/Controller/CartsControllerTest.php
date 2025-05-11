<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\CartsController Test Case
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
        'app.ArtworkVariantCarts',
        'app.Carts',
        'app.ContentBlocks',
    ];

    /**
     * Test Case 1.1: Add Item to Cart (Logged-in User)
     *
     * @return void
     */
    public function testAddItemToCartLoggedInWithoutView(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

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

        $this->assertSession($artworkId, 'Cart.items.0.artwork_id', 'The artwork should be added to the cart in the session.');
    }

    /**
     * Test Case 1.2: Add Item to Cart (Non-logged-in User)
     *
     * @return void
     */
    public function testAddItemToCartNonLoggedIn(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // No session set, simulate non-logged in user.
        $artworkId = '8424e85e-f1b2-41f5-85cb-bfbe115b45bc';
        $data = ['artwork_id' => $artworkId];

        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();

        $this->assertFlashMessage('Item added to cart.');

        $this->assertSession($artworkId, 'Cart.items.0.artwork_id', 'The artwork should be added to the cart in the session.');
    }

    /**
     * Test Case 1.3: Preventing Duplicate Items in Cart
     *
     * @return void
     */
    public function testPreventDuplicateItems(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

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
     * Test Case 1.4: Do not add a sold item to cart.
     *
     * @return void
     */
    public function testAddSoldItemToCart(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

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
     * Test Case 1.5: Do not add a deleted item to cart.
     *
     * @return void
     */
    public function testAddDeletedItemToCart(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

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
     * Test Case: 1.6 Test Missing Artwork ID on Add.
     *
     * @return void
     */
    public function testAddItemWithoutArtworkId(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // Post with no artwork_id in data.
        $this->post('/carts/add');
        $this->assertResponseSuccess();
        $this->assertFlashMessage('No artwork specified.');
    }

    /**
     * Test Case: 1.7 Invalid Request Method for Add.
     *
     * @return void
     */
    public function testAddItemInvalidMethod(): void
    {
        $this->get('/carts/add?artwork_id=some-artwork-id');
        $this->assertResponseCode(404);
    }

    /**
     * Test Case 2.1: Remove Item from Cart
     *
     * @return void
     */
    public function testRemoveItemFromCart(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

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
     * Test Case 2.2: Invalid Request Method for Remove.
     *
     * @return void
     */
    public function testRemoveItemInvalidMethod(): void
    {
        $this->get('/carts/remove?artwork_id=some-artwork-id');
        $this->assertResponseCode(404);
    }

    /**
     * Test Case 2.3: Remove Non-Existent Cart Item.
     *
     * @return void
     */
    public function testRemoveNonExistentCartItem(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // Simulate a logged-in user.
        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);

        // Ensure the cart exists but has no items.
        // This can be simulated by manually writing an empty cart to the session,
        // or relying on fixture data where no cart item exists for this user.
        // For this example, we'll assume the user already has an empty cart.
        // Now attempt to remove an artwork not present.
        $data = ['artwork_id' => 'non-existent-artwork-id'];
        $this->post('/carts/remove', $data);
        $this->assertResponseSuccess();

        $this->assertFlashMessage('Cart item not found.');
    }

    /**
     * Test Case 3.1: Cart Persistence During Session
     *
     * @return void
     */
    public function testCartPersistenceDuringSession(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

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
     * Test Case 3.2: Cart Persistence After Logout
     *
     * @return void
     */
    public function testCartPersistenceAfterLogout(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // Assume business rule: cart is cleared after logout.
        $artworkId = '5492e85e-f1b2-41f5-85cb-bfbe115b69ea';
        $this->session([
            'Auth' => [
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
     * Test Case 3.3: Cart Index Does Not Show Deleted Artwork
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
     * Test Case 3.4: Cart Index Does Not Show Sold Artwork
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
     * Test Case 3.5: Empty Cart Index.
     *
     * @return void
     */
    public function testEmptyCartIndex(): void
    {
        // Simulate a logged-in user with no cart.
        $this->session([
            'Auth' => [
                'user_id' => 'no-cart-user',
                'email' => 'nocart@example.com',
            ],
        ]);

        $this->get('/carts');
        $this->assertResponseOk();

        $this->assertResponseContains('Your cart is empty');
    }
}
