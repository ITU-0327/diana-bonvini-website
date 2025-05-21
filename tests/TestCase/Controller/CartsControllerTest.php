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
        'app.ArtworkVariants',
        'app.Carts',
        'app.ArtworkVariantCarts',
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
        $variantId = '5492e85e-f1b2-41f5-0000-000000000000';
        $data = [
            'artwork_variant_id' => $variantId,
            'quantity' => 1,
        ];

        // POST request to add the item.
        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();
        $this->assertFlashMessage('Item added to cart.');

        $this->assertSession(
            $variantId,
            'Cart.items.0.artwork_variant_id',
            'The variant should be added to the cart key in session',
        );
        $this->assertSession(
            1,
            'Cart.items.0.quantity',
            'Quantity should be saved too',
        );
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
        $variantId = '8424e85e-f1b2-41f5-0000-000000000000';
        $data = [
            'artwork_variant_id' => $variantId,
            'quantity' => 1,
        ];

        $this->post('/carts/add', $data);
        $this->assertResponseSuccess();

        $this->assertFlashMessage('Item added to cart.');

        $this->assertSession(
            $variantId,
            'Cart.items.0.artwork_variant_id',
            'The variant should be added to the cart key in session',
        );
        $this->assertSession(
            1,
            'Cart.items.0.quantity',
            'Quantity should be saved too',
        );
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

        $variantId = 'artwork-sold-0000000000000000';

        // Simulate logged-in user.
        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);

        // Try to add a sold artwork.
        $this->post('/carts/add', ['artwork_variant_id' => $variantId]);
        $this->assertResponseSuccess();
        // Verify that the response contains the error message.
        $this->assertFlashMessage('That artwork/size is not available.');
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

        $variantId = 'artwork-deleted-0000000000000000';

        // Simulate logged-in user.
        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);

        // Try to add a deleted artwork.
        $this->post('/carts/add', ['artwork_variant_id' => $variantId]);
        $this->assertResponseSuccess();

        $this->assertFlashMessage('That artwork/size is not available.');
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

        // Post with no artwork_variant_id in data.
        $this->post('/carts/add');
        $this->assertResponseSuccess();
        $this->assertFlashMessage('No variant selected.');
    }

    /**
     * Test Case: 1.7 Invalid Request Method for Add.
     *
     * @return void
     */
    public function testAddItemInvalidMethod(): void
    {
        $this->get('/carts/add');
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

        $variantId  = '5492e85e-f1b2-41f5-0000-000000000000';
        $data = ['artwork_variant_id' => $variantId ];

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
        $this->get('/carts/remove?artwork_variant_id=some-artwork-id');
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
        $data = ['artwork_variant_id' => 'non-existent-artwork-id'];
        $this->post('/carts/remove', $data);
        $this->assertResponseSuccess();

        $this->assertFlashMessage('Item not in cart.');
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

        $variantId = '5492e85e-f1b2-41f5-0000-000000000000';
        $data = ['artwork_variant_id' => $variantId];

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
        $variantId = '5492e85e-f1b2-41f5-0000-000000000000';
        $this->session([
            'Auth' => [
                'user_id' => 'user-uuid',
                'email' => 'user@example.com',
            ],
        ]);

        // Add an item to the cart.
        $data = ['artwork_variant_id' => $variantId];
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
