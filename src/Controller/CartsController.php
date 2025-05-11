<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Artwork;
use App\Model\Entity\Cart;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Carts Controller
 *
 * @property \App\Model\Table\CartsTable $Carts
 * @property \App\Model\Table\ArtworkVariantCartsTable $ArtworkCarts
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class CartsController extends AppController
{
    /**
     * Before filter method.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event The event object.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'index',
            'add',
            'remove',
            'buyNow',
        ]);
    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        // Get user/session info and build conditions
        $data = $this->_getUserAndSession();
        $conditions = $this->_buildCartConditions($data['userId'], $data['sessionId']);

        // Retrieve the cart with associated ArtworkCarts and their Artworks
        $cart = $this->Carts->find()
            ->contain([
                'ArtworkCarts' => function ($q) {
                    return $q->where(['ArtworkCarts.is_deleted' => 0]);
                },
                'ArtworkCarts.Artworks' => function ($q) {
                    return $q->where([
                        'Artworks.is_deleted' => 0,
                        'Artworks.availability_status' => 'available',
                    ]);
                },
            ])
            ->where($conditions)
            ->first();

        $this->set(compact('cart'));
    }

    /**
     * Add an artwork to the cart.
     *
     * @param string|null $artworkId The artwork ID to add.
     * @return \Cake\Http\Response|null Redirects back to referring page.
     */
    public function add(?string $artworkId = null): ?Response
    {
        if (!$this->request->is(['post', 'put'])) {
            throw new NotFoundException('Invalid request method.');
        }

        if (!$artworkId) {
            $artworkId = $this->request->getData('artwork_id');
        }
        if (!$artworkId) {
            $this->Flash->error('No artwork specified.');

            return $this->redirect($this->referer());
        }

        // Retrieve and validate the artwork
        $artwork = $this->_getValidArtwork($artworkId);
        if (!$artwork) {
            return $this->redirect($this->referer());
        }

        // Get user/session info and build conditions
        $data = $this->_getUserAndSession();
        $conditions = $this->_buildCartConditions($data['userId'], $data['sessionId']);

        // Find an existing cart or create a new one
        $cart = $this->_findOrCreateCart($conditions, $data['userId'], $data['sessionId'], $artworkId);
        if (!$cart) {
            $this->Flash->error('Unable to create cart.');

            return $this->redirect($this->referer());
        }

        // Check if the artwork is already in the cart, or add it if not
        if (!empty($cart->artwork_carts)) {
            $this->Flash->success('Item already in cart.');
        } else {
            if ($this->_addArtworkToCart($cart, $artworkId)) {
                $this->Flash->success('Item added to cart.');
            } else {
                $this->Flash->error('Unable to add item to cart.');
            }
        }

        return $this->redirect($this->referer());
    }

    /**
     * Buy an artwork directly.
     *
     * @param string|null $artworkId The artwork ID to buy.
     * @return \Cake\Http\Response|null Redirects to the cart page.
     */
    public function buyNow(?string $artworkId = null): ?Response
    {
        if (!$this->request->is(['post', 'put'])) {
            throw new NotFoundException('Invalid request method.');
        }

        if (!$artworkId) {
            $artworkId = $this->request->getData('artwork_id');
        }
        if (!$artworkId) {
            $this->Flash->error('No artwork specified.');

            return $this->redirect($this->referer());
        }

        // Retrieve and validate the artwork
        $artwork = $this->_getValidArtwork($artworkId);
        if (!$artwork) {
            return $this->redirect($this->referer());
        }

        // Get user/session info and build conditions
        $data = $this->_getUserAndSession();
        $conditions = $this->_buildCartConditions($data['userId'], $data['sessionId']);

        // Find an existing cart or create a new one
        $cart = $this->_findOrCreateCart($conditions, $data['userId'], $data['sessionId'], $artworkId);
        if (!$cart) {
            $this->Flash->error('Unable to create cart.');

            return $this->redirect($this->referer());
        }

        // Check if the artwork is already in the cart, or add it if not
        if (!empty($cart->artwork_carts)) {
            $this->Flash->success('Item already in cart.');
        } else {
            if ($this->_addArtworkToCart($cart, $artworkId)) {
                $this->Flash->success('Item added to cart.');
            } else {
                $this->Flash->error('Unable to add item to cart.');
            }
        }

        // Redirect directly to the cart page
        return $this->redirect(['controller' => 'Carts', 'action' => 'index']);
    }

    /**
     * Remove a cart item from the cart by artwork ID.
     *
     * @param string|null $artworkId The artwork ID to remove from the cart.
     * @return \Cake\Http\Response|null Redirects back to the referring page.
     */
    public function remove(?string $artworkId = null): ?Response
    {
        if (!$this->request->is(['post', 'delete'])) {
            throw new NotFoundException('Invalid request method.');
        }

        if (!$artworkId) {
            $artworkId = $this->request->getData('artwork_id');
        }
        if (!$artworkId) {
            $this->Flash->error('No artwork specified.');

            return $this->redirect($this->referer());
        }

        // Get user/session info and build conditions
        $data = $this->_getUserAndSession();
        $conditions = $this->_buildCartConditions($data['userId'], $data['sessionId']);

        $cart = $this->Carts->find()
            ->contain(['ArtworkCarts'])
            ->where($conditions)
            ->first();

        if (!$cart) {
            $this->Flash->error('Cart not found.');

            return $this->redirect($this->referer());
        }

        // Find the cart item corresponding to the artwork ID in this cart
        $cartItem = $this->Carts->ArtworkCarts->find()
            ->where([
                'cart_id'    => $cart->cart_id,
                'artwork_id' => $artworkId,
            ])
            ->first();

        if (!$cartItem) {
            $this->Flash->error('Cart item not found.');

            return $this->redirect($this->referer());
        }

        // Delete the cart item
        if ($this->Carts->ArtworkCarts->delete($cartItem)) {
            $this->Flash->success('Item removed from cart.');

            // Check if the cart is now empty
            $remainingItems = $this->Carts->ArtworkCarts->find()
                ->where(['cart_id' => $cart->cart_id])
                ->count();

            if ($remainingItems === 0) {
                // No remaining items; delete the cart silently.
                try {
                    $this->Carts->delete($cart);
                } catch (RecordNotFoundException) {
                    // Cart might have already been deleted.
                }
            }
        } else {
            $this->Flash->error('Unable to remove item from cart.');
        }

        return $this->redirect($this->referer());
    }

    /**
     * Retrieves the artwork and validates its availability.
     *
     * @param string $artworkId
     * @return \App\Model\Entity\Artwork|null
     */
    protected function _getValidArtwork(string $artworkId): ?Artwork
    {
        $artworksTable = $this->fetchTable('Artworks');
        try {
            /** @var \App\Model\Entity\Artwork $artwork */
            $artwork = $artworksTable->get($artworkId);
        } catch (RecordNotFoundException) {
            $this->Flash->error('Artwork not found.');

            return null;
        }
        if ($artwork->availability_status !== 'available' || $artwork->is_deleted) {
            $this->Flash->error('Artwork is not available.');

            return null;
        }

        return $artwork;
    }

    /**
     * Retrieves user and session information.
     *
     * @return array{userId: ?string, sessionId: string}
     */
    protected function _getUserAndSession(): array
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        $userId = $user?->user_id;
        $sessionId = $this->request->getSession()->id();

        return compact('userId', 'sessionId');
    }

    /**
     * Builds conditions for finding the cart based on user or session.
     *
     * @param string|null $userId
     * @param string $sessionId
     * @return array<string, string>
     */
    protected function _buildCartConditions(?string $userId, string $sessionId): array
    {
        return $userId !== null ? ['user_id' => $userId] : ['session_id' => $sessionId];
    }

    /**
     * Finds an existing cart or creates a new one.
     *
     * @param array<string, string> $conditions
     * @param string|null $userId
     * @param string $sessionId
     * @param string|null $artworkId Optional artwork ID to limit contained cart items.
     * @return \App\Model\Entity\Cart|null
     */
    protected function _findOrCreateCart(array $conditions, ?string $userId, string $sessionId, ?string $artworkId = null): ?Cart
    {
        $query = $this->Carts->find();
        if ($artworkId !== null) {
            $query->contain(['ArtworkCarts' => function ($q) use ($artworkId) {
                return $q->where([
                    'ArtworkCarts.artwork_id' => $artworkId,
                    'ArtworkCarts.is_deleted' => 0,
                ]);
            }]);
        }
        $cart = $query->where($conditions)->first();
        if (!$cart) {
            $cart = $this->Carts->newEmptyEntity();
            if ($userId !== null) {
                $cart->user_id = $userId;
            } else {
                $cart->session_id = $sessionId;
            }
            if (!$this->Carts->save($cart)) {
                return null;
            }
        }

        return $cart;
    }

    /**
     * Adds an artwork to the cart.
     *
     * @param \App\Model\Entity\Cart $cart
     * @param string $artworkId
     * @return bool
     */
    protected function _addArtworkToCart(Cart $cart, string $artworkId): bool
    {
        if (!empty($cart->artwork_carts)) {
            return false;
        }
        $cartItem = $this->Carts->ArtworkCarts->newEntity([
            'cart_id'    => $cart->cart_id,
            'artwork_id' => $artworkId,
            'quantity'   => 1,
        ]);
        if ($this->Carts->ArtworkCarts->save($cartItem)) {
            $updatedCartItems = $this->Carts->ArtworkCarts->find()
                ->where([
                    'cart_id'    => $cart->cart_id,
                    'is_deleted' => 0,
                ])
                ->toArray();
            $this->request->getSession()->write('Cart.items', $updatedCartItems);

            return true;
        }

        return false;
    }
}
