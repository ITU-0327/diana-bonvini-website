<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

/**
 * Artworks Controller
 *
 * @property \App\Model\Table\ArtworksTable $Artworks
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 */
class ArtworksController extends AppController
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

        $this->Authentication->addUnauthenticatedActions(['index', 'view']);
    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $query = $this->Artworks->find()
            ->where(['Artworks.is_deleted' => 0])
            ->contain([
                'ArtworkVariants' => function ($q) {
                    return $q->where([
                        'ArtworkVariants.is_deleted' => 0,
                    ]);
                },
            ]);

        // Add status filtering if provided
        $status = $this->request->getQuery('status');
        if ($status && in_array($status, ['available', 'sold'])) {
            $query->where(['Artworks.availability_status' => $status]);
        }

        $artworks = $this->paginate($query);

        $this->set(compact('artworks', 'status'));
    }

    /**
     * View method
     *
     * @param string|null $id Artwork id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $artwork = $this->Artworks->get($id, contain: [
            'ArtworkVariants' => function ($q) {
                return $q->where([
                    'ArtworkVariants.is_deleted' => 0,
                ]);
            },
        ]);

        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Authentication->getIdentity();
        $userId = $user?->user_id;
        $sessionId = $this->request->getSession()->id();

        $conditions = $userId !== null ? ['user_id' => $userId] : ['session_id' => $sessionId];
        $cartsTable = TableRegistry::getTableLocator()->get('Carts');
        /** @var \App\Model\Entity\Cart $cart */
        $cart = $cartsTable->find()
            ->contain(['ArtworkVariantCarts.ArtworkVariants'])
            ->where($conditions)
            ->first();

        // Sum in-cart quantities for this artwork
        $inCart = 0;
        if ($cart) {
            foreach ($cart->artwork_variant_carts as $line) {
                if ($line->artwork_variant->artwork_id === $artwork->artwork_id) {
                    $inCart += $line->quantity;
                }
            }
        }

        // Calculate remaining using artwork's virtual stock field and in-cart quantity
        $remaining = max(0, $artwork->stock - $inCart);

        $this->set(compact('artwork', 'remaining'));
    }
}
