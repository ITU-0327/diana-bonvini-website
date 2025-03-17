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

        $this->Authentication->addUnauthenticatedActions(['add']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Carts->find()
            ->contain(['Users']);
        $carts = $this->paginate($query);

        $this->set(compact('carts'));
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
            $cart->user_id = $userId;
            $cart->session_id = $sessionId;
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

                // Write the updated cart items to the session so your test can verify it.
                $this->request->getSession()->write('Cart.items', $updatedCartItems);
            } else {
                $this->Flash->error('Unable to add item to cart.');
            }
        }

        return $this->redirect($this->referer());
    }

    /**
     * Delete method
     *
     * @param string|null $id Cart id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $cart = $this->Carts->get($id);
        if ($this->Carts->delete($cart)) {
            $this->Flash->success(__('The cart has been deleted.'));
        } else {
            $this->Flash->error(__('The cart could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
