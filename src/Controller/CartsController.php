<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Carts Controller
 *
 * @property \App\Model\Table\CartsTable $Carts
 * @property \App\Model\Table\ArtworkCartsTable $ArtworkCarts
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

        $this->Authentication->addUnauthenticatedActions(['index', 'add', 'remove']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        $userId = $user?->user_id;
        $sessionId = $this->request->getSession()->id();

        $conditions = [];
        if ($userId !== null) {
            $conditions[] = ['user_id' => $userId];
        } else {
            $conditions[] = ['session_id' => $sessionId];
        }

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

        // Retrieve the artwork to check its availability and deletion status.
        $artworksTable = $this->fetchTable('Artworks');
        try {
            /** @var \App\Model\Entity\Artwork $artwork */
            $artwork = $artworksTable->get($artworkId);
        } catch (RecordNotFoundException $e) {
            $this->Flash->error('Artwork not found.');

            return $this->redirect($this->referer());
        }

        if ($artwork->availability_status !== 'available' || $artwork->is_deleted) {
            $this->Flash->error('Artwork is not available.');

            return $this->redirect($this->referer());
        }

        // Retrieve the user ID if logged in; otherwise, use session ID
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        $userId = $user?->user_id;
        $sessionId = $this->request->getSession()->id();

        if ($userId !== null) {
            $conditions[] = ['user_id' => $userId];
        } else {
            $conditions[] = ['session_id' => $sessionId];
        }

        // Find an existing cart for the user or the current session, loading associated artwork items
        $cart = $this->Carts->find()
            ->contain(['ArtworkCarts' => function ($q) use ($artworkId) {
                return $q->where([
                    'ArtworkCarts.artwork_id' => $artworkId,
                    'ArtworkCarts.is_deleted' => 0,
                ]);
            }])
            ->where($conditions)
            ->first();

        // If no cart exists, create a new one
        if (!$cart) {
            $cart = $this->Carts->newEmptyEntity();
            if ($userId !== null) {
                $cart->user_id = $userId;
            } else {
                $cart->session_id = $sessionId;
            }
            if (!$this->Carts->save($cart)) {
                $this->Flash->error('Unable to create cart.');

                return $this->redirect($this->referer());
            }
        }

        // Check if the artwork is already in the cart
        $cartItems = $cart->artwork_carts;
        if (!empty($cartItems)) {
            // Since it's art, do not allow duplicate items; just notify the user.
            $this->Flash->success('Item already in cart.');
        } else {
            // Create a new cart item
            $cartItem = $this->Carts->ArtworkCarts->newEntity([
                'cart_id'    => $cart->cart_id,
                'artwork_id' => $artworkId,
                'quantity'   => 1,
            ]);
            if ($this->Carts->ArtworkCarts->save($cartItem)) {
                $this->Flash->success('Item added to cart.');

                // Retrieve all non-deleted cart items for this cart
                $updatedCartItems = $this->Carts->ArtworkCarts->find()
                    ->where([
                        'cart_id' => $cart->cart_id,
                        'is_deleted' => 0,
                    ])
                    ->toArray();

                $this->request->getSession()->write('Cart.items', $updatedCartItems);
            } else {
                debug($cartItem->getErrors());
                $this->Flash->error('Unable to add item to cart.');
            }
        }

        return $this->redirect($this->referer());
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

        // Retrieve the current user (if logged in) and session ID
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        $userId = $user?->user_id;
        $sessionId = $this->request->getSession()->id();

        // Build conditions based on user or session
        $conditions = [];
        if ($userId !== null) {
            $conditions[] = ['user_id' => $userId];
        } else {
            $conditions[] = ['session_id' => $sessionId];
        }

        // Find the current cart for the user or session
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
                } catch (RecordNotFoundException $e) {
                    // Cart might have already been deleted; do nothing.
                }
            }
        } else {
            $this->Flash->error('Unable to remove item from cart.');
        }

        return $this->redirect($this->referer());
    }
}
