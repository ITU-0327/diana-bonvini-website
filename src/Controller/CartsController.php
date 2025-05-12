<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\ArtworkVariant;
use App\Model\Entity\Cart;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Carts Controller
 *
 * @property \App\Model\Table\CartsTable $Carts
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
        [$userId, $sessionId] = array_values($this->_getUserAndSession());
        $conditions = $this->_buildCartConditions($userId, $sessionId);

        // Retrieve the cart with associated ArtworkCarts and their Artworks
        $cart = $this->Carts->find()
            ->contain([
                // load the size & artwork for each line
                'ArtworkVariantCarts.ArtworkVariants.Artworks' => function ($q) {
                    return $q->where([
                        'Artworks.is_deleted' => 0,
                        'Artworks.availability_status' => 'available',
                        'ArtworkVariants.is_deleted' => 0,
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
     * @return \Cake\Http\Response|null Redirects back to referring page.
     */
    public function add(): ?Response
    {
        if (!$this->request->is(['post', 'put'])) {
            throw new NotFoundException('Invalid request method.');
        }

        // pull from URL or form
        $artworkVariantId = $this->request->getData('artwork_variant_id');
        $quantity = max(1, (int)$this->request->getData('quantity', 1));

        if (!$artworkVariantId) {
            $this->Flash->error('No size selected.');

            return $this->redirect($this->referer());
        }

        // make sure the variant (and its artwork) is still available
        $variant = $this->_getValidVariant($artworkVariantId);
        if (!$variant) {
            return $this->redirect($this->referer());
        }
        $artwork = $variant->artwork;
        $maxCopies = $artwork->max_copies;

        // find-or-make the cart
        [$userId, $sessionId] = array_values($this->_getUserAndSession());
        $conditions = $this->_buildCartConditions($userId, $sessionId);
        $cart = $this->_findOrCreateCart($conditions, $userId, $sessionId);
        if (!$cart) {
            $this->Flash->error('Unable to create cart.');

            return $this->redirect($this->referer());
        }

        $currentTotal = $this->Carts->ArtworkVariantCarts->find()
            ->select(['sum' => 'SUM(quantity)'])
            ->matching('ArtworkVariants', function ($q) use ($artwork) {
                return $q->where([
                    'ArtworkVariants.artwork_id' => $artwork->artwork_id,
                    'ArtworkVariants.is_deleted' => false,
                ]);
            })
            ->where(['ArtworkVariantCarts.cart_id' => $cart->cart_id])
            ->first()
            ->get('sum') ?? 0;

        // look for an existing line for this variant
        /** @var \App\Model\Entity\ArtworkVariantCart|null $existing */
        $existing = $this->Carts->ArtworkVariantCarts->find()
            ->where([
                'cart_id' => $cart->cart_id,
                'artwork_variant_id' => $artworkVariantId,
            ])
            ->first();

        $newTotal = $currentTotal + $quantity;

        if ($newTotal > $maxCopies) {
            $remaining = $maxCopies - $currentTotal;
            $this->Flash->error(
                "Only $remaining total print" . ($remaining === 1 ? '' : 's')
                . " remaining for “$artwork->title.”",
            );

            return $this->redirect($this->referer());
        }

        // Proceed with existing‐or‐new logic
        if ($existing) {
            $existing->quantity += $quantity;
            if ($this->Carts->ArtworkVariantCarts->save($existing)) {
                $this->Flash->success("Updated quantity to $existing->quantity.");
            } else {
                $this->Flash->error('Could not update quantity.');
            }
        } else {
            if ($this->_addArtworkVariantToCart($cart, $artworkVariantId, $quantity)) {
                $this->Flash->success('Item added to cart.');
            } else {
                $this->Flash->error('Unable to add to cart.');
            }
        }

        return $this->redirect(['controller' => 'Artworks', 'action' => 'index']);
    }

    /**
     * Remove a cart item from the cart by artwork ID.
     *
     * @param string|null $artworkVariantId The artwork variant ID to remove from the cart.
     * @return \Cake\Http\Response|null Redirects back to the referring page.
     */
    public function remove(?string $artworkVariantId = null): ?Response
    {
        if (!$this->request->is(['post', 'delete'])) {
            throw new NotFoundException('Invalid request method.');
        }

        $artworkVariantId = $artworkVariantId ?? $this->request->getData('artwork_variant_id');
        if (!$artworkVariantId) {
            $this->Flash->error('No artwork specified.');

            return $this->redirect($this->referer());
        }

        [$userId, $sessionId] = array_values($this->_getUserAndSession());
        $conditions = $this->_buildCartConditions($userId, $sessionId);

        /** @var \App\Model\Entity\Cart $cart */
        $cart = $this->Carts->find()
            ->contain(['ArtworkVariantCarts'])
            ->where($conditions)
            ->first();

        if (!$cart) {
            $this->Flash->error('Cart not found.');

            return $this->redirect($this->referer());
        }

        $cartItem = $this->Carts->ArtworkVariantCarts->find()
            ->where([
                'cart_id' => $cart->cart_id,
                'artwork_variant_id' => $artworkVariantId,
            ])
            ->first();

        if (!$cartItem) {
            $this->Flash->error('Item not in cart.');

            return $this->redirect($this->referer());
        }

        if ($this->Carts->ArtworkVariantCarts->delete($cartItem)) {
            $this->Flash->success('Item removed.');
            // if cart empty, delete the cart record
            $remaining = $this->Carts->ArtworkVariantCarts->find()
                ->where(['cart_id' => $cart->cart_id])
                ->count();
            if ($remaining === 0) {
                $this->Carts->delete($cart);
            }
        } else {
            $this->Flash->error('Unable to remove item from cart.');
        }

        return $this->redirect($this->referer());
    }

    /**
     * Update quantities in the cart.
     *
     * @return \Cake\Http\Response|null Redirects to the index action.
     */
    public function updateQuantities(): ?Response
    {
        $this->request->allowMethod(['post']);

        // Fetch cart for this user/session
        [$userId, $sessionId] = array_values($this->_getUserAndSession());
        $conditions = $this->_buildCartConditions($userId, $sessionId);
        /** @var \App\Model\Entity\Cart|null $cart */
        $cart = $this->Carts->find()
            ->contain(['ArtworkVariantCarts.ArtworkVariants.Artworks'])
            ->where($conditions)
            ->first();

        if (!$cart) {
            $this->Flash->error('Cart not found.');

            return $this->redirect(['action' => 'index']);
        }

        // Posted quantities: [artwork_variant_cart_id => newQty]
        $quantities = $this->request->getData('quantities') ?: [];

        // Loop through each line
        foreach ($quantities as $lineId => $newQty) {
            $newQty = (int)max(1, $newQty);
            /** @var \App\Model\Entity\ArtworkVariantCart $line */
            $line = $this->Carts->ArtworkVariantCarts->get($lineId, contain: ['ArtworkVariants.Artworks']);

            // Compute how many of this artwork are sold
            $variant = $line->artwork_variant;
            $artwork = $variant->artwork;
            $max = $artwork->max_copies;
            // sum sold across confirmed/completed orders
            $soldCount = $this->fetchTable('ArtworkVariantOrders')->find()
                ->select(['sum' => 'SUM(ArtworkVariantOrders.quantity)'])
                ->where([
                    'ArtworkVariantOrders.artwork_variant_id' => $variant->artwork_variant_id,
                    'ArtworkVariantOrders.is_deleted'         => false,
                ])
                ->first()
                ->get('sum') ?? 0;

            // sum existing in cart excluding this line
            $inCart = $this->Carts->ArtworkVariantCarts->find()
                ->matching('ArtworkVariants', function ($q) use ($artwork) {
                    return $q->where([
                        'ArtworkVariants.artwork_id' => $artwork->artwork_id,
                        'ArtworkVariants.is_deleted' => false,
                    ]);
                })
                ->where(['ArtworkVariantCarts.cart_id' => $cart->cart_id])
                ->select(['sum' => 'SUM(ArtworkVariantCarts.quantity)'])
                ->first()
                ->get('sum') ?? 0;

            // remove this line's old qty so we can re-add with new
            $inCart -= $line->quantity;

            $available = $max - $soldCount - $inCart;
            if ($available < 1) {
                $this->Flash->error(
                    "No more copies available for " . $artwork->title . ".",
                );
                continue;
            }

            if ($newQty < 1 || $newQty > $available) {
                $this->Flash->error("Quantity for '" . $artwork->title . "' can only be between 1 and " . $available . ".");
                continue;
            }

            // save updated quantity
            $line->quantity = $newQty;
            if (!$this->Carts->ArtworkVariantCarts->save($line)) {
                $this->Flash->error("Could not update quantity for '" . $artwork->title . "'.");
            }
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Retrieves the artwork and validates its availability.
     *
     * @param string $artworkVariantId The artwork variant ID to validate.
     * @return \App\Model\Entity\ArtworkVariant|null
     */
    protected function _getValidVariant(string $artworkVariantId): ?ArtworkVariant
    {
        /** @var \App\Model\Table\ArtworkVariantsTable $artworkVariantsTable */
        $artworkVariantsTable = $this->getTableLocator()->get('ArtworkVariants');
        $variant = $artworkVariantsTable->get($artworkVariantId, contain: ['Artworks']);

        // check its parent artwork
        if (
            $variant->is_deleted
            || $variant->artwork->is_deleted
            || $variant->artwork->availability_status !== 'available'
        ) {
            $this->Flash->error('That artwork/size is not available.');

            return null;
        }

        return $variant;
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
     * @return \App\Model\Entity\Cart|null
     */
    protected function _findOrCreateCart(array $conditions, ?string $userId, string $sessionId): ?Cart
    {
        $cart = $this->Carts->find()
            ->where($conditions)
            ->first();
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
     * @param \App\Model\Entity\Cart $cart The cart to add the artwork to.
     * @param string $artworkVariantId The artwork variant ID to add.
     * @param int $quantity The quantity to add.
     * @return bool True on success, false on failure.
     */
    protected function _addArtworkVariantToCart(Cart $cart, string $artworkVariantId, int $quantity = 1): bool
    {
        $cartItem = $this->Carts->ArtworkVariantCarts->newEntity([
            'cart_id' => $cart->cart_id,
            'artwork_variant_id' => $artworkVariantId,
            'quantity' => $quantity,
        ]);
        if ($this->Carts->ArtworkVariantCarts->save($cartItem)) {
            // refresh the session cache if you're storing it there
            $updatedCartItems = $this->Carts->ArtworkVariantCarts->find()
                ->where(['cart_id' => $cart->cart_id])
                ->toArray();
            $this->request->getSession()->write('Cart.items', $updatedCartItems);

            return true;
        }

        return false;
    }
}
