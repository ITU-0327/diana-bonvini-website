<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\ArtworksController as BaseArtworksController;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Artworks Controller (Admin)
 *
 * Admin interface for managing artworks. Uses dedicated admin templates.
 */
class ArtworksController extends BaseArtworksController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Use admin layout
        $this->viewBuilder()->setLayout('admin');

        // By default, use the Admin/Artworks templates for all actions
        $this->viewBuilder()->setTemplatePath('Admin/Artworks');
    }

    /**
     * Override the beforeFilter to set authentication requirements
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Remove any unauthenticated actions for admin
        $this->Authentication->addUnauthenticatedActions([]);

        // Check for admin user
        $user = $this->Authentication->getIdentity();
        if (!$user || $user->user_type !== 'admin') {
            $this->Flash->error('You must be logged in as an administrator to access this area.');
            $this->redirect(['controller' => 'Users', 'action' => 'login', 'prefix' => false]);
        }
    }

    /**
     * Index method - Shows all artworks with admin functionality
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $this->set('title', 'Artwork Management');

        $query = $this->Artworks->find();
        $artworks = $this->paginate($query);

        $this->set(compact('artworks'));
    }

    /**
     * Add method - Customized for admin interface
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     * @throws \Exception
     */
    public function add()
    {
        $this->set('title', 'Add New Artwork');

        $artwork = $this->Artworks->newEmptyEntity();
        if ($this->request->is('post')) {
            $artwork = $this->Artworks->patchEntity($artwork, $this->request->getData());
            $artwork->availability_status = 'available';

            if (!$this->Artworks->save($artwork)) {
                $this->Flash->error(__('Unable to save artwork. Please check the form and try again.'));
            } else {
                $this->Flash->success(__('Artwork has been saved successfully.'));
                $file = $this->request->getData('image_path');
                if ($file instanceof UploadedFileInterface) {
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        $this->Flash->error(__('Image upload failed with code {0}.', $file->getError()));
                    } else {
                        $this->_processImageUpload($file, $artwork->artwork_id);
                    }
                }

                return $this->redirect(['action' => 'index']);
            }
        }

        // Get categories for dropdown
        $categories = [
            'painting' => 'Painting',
            'digital' => 'Digital Art',
            'photography' => 'Photography',
            'sculpture' => 'Sculpture',
            'mixed_media' => 'Mixed Media',
            'other' => 'Other',
        ];

        $this->set(compact('artwork', 'categories'));
    }

    /**
     * Edit method - Customized for admin interface
     *
     * @param string|null $id Artwork id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException|\Exception When record not found.
     */
    public function edit(?string $id = null)
    {
        $this->set('title', 'Edit Artwork');

        $artwork = $this->Artworks->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $artwork = $this->Artworks->patchEntity($artwork, $this->request->getData());

            if (!$this->Artworks->save($artwork)) {
                $this->Flash->error(__('Unable to update artwork. Please check the form and try again.'));
            } else {
                $this->Flash->success(__('Artwork has been updated successfully.'));
                $file = $this->request->getData('image_path');
                if ($file instanceof UploadedFileInterface) {
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        $this->Flash->error(__('Image upload failed with code {0}.', $file->getError()));
                    } else {
                        $this->_processImageUpload($file, $artwork->artwork_id);
                    }
                }

                return $this->redirect(['action' => 'index']);
            }
        }

        // Get categories for dropdown
        $categories = [
            'painting' => 'Painting',
            'digital' => 'Digital Art',
            'photography' => 'Photography',
            'sculpture' => 'Sculpture',
            'mixed_media' => 'Mixed Media',
            'other' => 'Other',
        ];

        $this->set(compact('artwork', 'categories'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Artwork id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $artwork = $this->Artworks->get($id);

        // Soft delete - set is_deleted flag to 1 instead of actually deleting
        $artwork->is_deleted = 1;

        if ($this->Artworks->save($artwork)) {
            $this->Flash->success(__('The artwork has been deleted.'));
        } else {
            $this->Flash->error(__('The artwork could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
