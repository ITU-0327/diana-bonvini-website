<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\ArtworksController as BaseArtworksController;
use App\Service\R2StorageService;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;
use GdImage;
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
     * @param \App\Service\R2StorageService $r2StorageService The R2 storage service instance.
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     * @throws \Exception
     */
    public function add(R2StorageService $r2StorageService)
    {
        $this->set('title', 'Add New Artwork');

        $artwork = $this->Artworks->newEmptyEntity();
        if ($this->request->is('post')) {
            try {
                // Get the data before we modify it
                $data = $this->request->getData();
                $file = $data['image_path'] ?? null;
                
                // Check if we have an image
                if (!$file instanceof UploadedFileInterface || $file->getError() !== UPLOAD_ERR_OK) {
                    $this->Flash->error(__('Please upload a valid image file.'));
                    return;
                }
                
                // Prepare the artwork data
                unset($data['image_path']);
                
                // Remove featured field as it doesn't exist in the database
                unset($data['featured']);
                $data['availability_status'] = $data['availability_status'] ?? 'available';
                $data['is_deleted'] = 0;
                
                // Let CakePHP handle timestamps automatically
                
                // Create the entity
                $artwork = $this->Artworks->patchEntity($artwork, $data);
                
                // Debug information
                $this->log('Attempting to save artwork with data: ' . json_encode($data), 'debug');
                $this->log('Validation errors (if any): ' . json_encode($artwork->getErrors()), 'debug');
                
                // Save the artwork
                if (!$this->Artworks->save($artwork)) {
                    $this->log('Failed to save artwork. Errors: ' . json_encode($artwork->getErrors()), 'error');
                    $this->Flash->error(__('Unable to save artwork. Please check the form and try again.'));
                    $this->set('errors', $artwork->getErrors());
                    return;
                }
                
                // Process the image
                try {
                    // Save the image locally first (this will always work)
                    $localPath = $this->_saveOriginalLocal($file, $artwork->artwork_id);
                    $this->log('Image saved locally to: ' . $localPath, 'debug');
                    
                    // Try to process with R2 but continue if it fails
                    try {
                        $this->_processImageUpload($file, $artwork->artwork_id, $r2StorageService);
                        $this->Flash->success(__('Artwork has been saved successfully with image.'));
                    } catch (\Exception $e) {
                        $this->log('R2 storage processing failed: ' . $e->getMessage(), 'error');
                        $this->Flash->success(__('Artwork has been saved successfully. Note: Cloud storage of the image failed, but the image was saved locally.'));
                    }
                } catch (\Exception $e) {
                    $this->log('Image processing failed: ' . $e->getMessage(), 'error');
                    $this->Flash->error(__('Artwork saved but image processing failed: {0}', $e->getMessage()));
                }
                
                return $this->redirect(['action' => 'index']);
                
            } catch (\Exception $e) {
                $this->log('Unexpected error in add artwork: ' . $e->getMessage(), 'error');
                $this->Flash->error(__('An unexpected error occurred: {0}', $e->getMessage()));
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
     * @param \App\Service\R2StorageService $r2StorageService The R2 storage service instance.
     * @param string|null $id Artwork id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException|\Exception When record not found.
     */
    public function edit(R2StorageService $r2StorageService, ?string $id = null)
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
                        // Save the image locally first (this will always work)
                        try {
                            $localPath = $this->_saveOriginalLocal($file, $artwork->artwork_id);
                            $this->log('Image saved locally to: ' . $localPath, 'debug');
                            
                            // Try to process with R2 but continue if it fails
                            try {
                                $this->_processImageUpload($file, $artwork->artwork_id, $r2StorageService);
                            } catch (\Exception $e) {
                                $this->log('R2 storage processing failed: ' . $e->getMessage(), 'error');
                                $this->Flash->warning(__('Artwork updated but cloud storage of the image failed. The image was saved locally.'));
                            }
                        } catch (\Exception $e) {
                            $this->log('Image processing failed: ' . $e->getMessage(), 'error');
                            $this->Flash->error(__('Image processing failed: {0}', $e->getMessage()));
                        }
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
    
    /**
     * Handles moving the uploaded file, validating it, adding a tiled watermark,
     * and returning the relative path for storage (or null on failure).
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The uploaded JPEG file.
     * @param string $artworkId The artwork ID used to name objects.
     * @param \App\Service\R2StorageService $r2StorageService The R2 storage service instance.
     * @throws \Exception If the file is not JPEG or any imaging/S3 error occurs.
     */
    protected function _processImageUpload(UploadedFileInterface $file, string $artworkId, R2StorageService $r2StorageService): void
    {
        $this->_assertJpeg($file);

        // We'll reuse a file that's already saved by _saveOriginalLocal
        $localPath = WWW_ROOT . 'img' . DS . 'Artworks' . DS . "{$artworkId}.jpg";
        
        if (!file_exists($localPath)) {
            throw new Exception("Original file not found at: {$localPath}");
        }

        // Read it into memory for watermarking
        $bytes = file_get_contents($localPath);
        if ($bytes === false) {
            throw new Exception("Failed to read saved file: {$localPath}");
        }

        // Generate watermarked JPG in memory
        $jpeg = $this->_createWatermarkedJpeg($bytes);
        $wmKey = "{$artworkId}_wm.jpg";

        // Save watermarked version locally too
        $watermarkedPath = WWW_ROOT . 'img' . DS . 'watermarked' . DS;
        if (!is_dir($watermarkedPath) && !mkdir($watermarkedPath, 0755, true)) {
            throw new Exception("Unable to create watermarked directory: {$watermarkedPath}");
        }
        
        $watermarkedFilePath = $watermarkedPath . "{$artworkId}.jpg";
        if (file_put_contents($watermarkedFilePath, $jpeg) === false) {
            throw new Exception("Failed to save watermarked image locally");
        }
        
        // Try to upload to R2, but if it fails we already have local copies
        if (!$r2StorageService->put($wmKey, $jpeg)) {
            throw new Exception("Failed to upload watermark to R2 storage");
        }
    }

    /**
     * Moves the uploaded JPEG into webroot/img/Artworks/{artworkId}.jpg.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file
     * @param string $artworkId
     * @return string Full filesystem path to the saved file.
     * @throws \Exception On any filesystem error.
     */
    protected function _saveOriginalLocal(UploadedFileInterface $file, string $artworkId): string
    {
        $dir = WWW_ROOT . 'img' . DS . 'Artworks';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new Exception("Unable to create directory: {$dir}");
        }

        $target = $dir . DS . "{$artworkId}.jpg";
        // moveTo will overwrite if file exists
        $file->moveTo($target);

        return $target;
    }

    /**
     * Ensures that the uploaded file is a JPEG image.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The upload to check.
     * @return void
     * @throws \Exception If the media type is not "image/jpeg".
     */
    protected function _assertJpeg(UploadedFileInterface $file): void
    {
        if ($file->getClientMediaType() !== 'image/jpeg') {
            throw new Exception('Only JPEG uploads are allowed.');
        }
    }

    /**
     * Applies a tiled text watermark to the given JPEG bytes and
     * returns the resulting image as a JPEG binary string.
     *
     * @param string $jpegBytes Raw JPEG file contents.
     * @return string JPEG-encoded binary string with watermark.
     * @throws \Exception On invalid image data or GD failures.
     */
    protected function _createWatermarkedJpeg(string $jpegBytes): string
    {
        $im = imagecreatefromstring($jpegBytes);
        if (!$im instanceof GdImage) {
            throw new Exception('Invalid JPEG data.');
        }

        $width = imagesx($im);
        $height = imagesy($im);
        $this->_drawTiledText($im, $width, $height);

        ob_start();
        imagejpeg($im, null, 75);
        $out = ob_get_clean();
        imagedestroy($im);

        if ($out === false) {
            throw new Exception('Failed to capture JPEG output.');
        }

        return $out;
    }

    /**
     * Draws the tiled watermark text onto the canvas.
     *
     * @param \GdImage $canvas The GD image resource to draw on.
     * @param int $width Width of the canvas/image.
     * @param int $height Height of the canvas/image.
     * @throws \Exception If the CMS block or font is missing, or GD fails.
     */
    protected function _drawTiledText(GdImage $canvas, int $width, int $height): void
    {
        $block = TableRegistry::getTableLocator()
            ->get('ContentBlocks')
            ->find()
            ->select(['value'])
            ->where(['slug' => 'watermark-text'])
            ->first();
        if (!$block) {
            throw new Exception('Watermark text not found.');
        }

        $font = WWW_ROOT . 'font/Arial.ttf';
        if (!is_readable($font)) {
            throw new Exception("Missing font at $font");
        }

        $color = imagecolorallocatealpha($canvas, 225, 225, 225, 96);
        if ($color === false) {
            throw new Exception('Unable to allocate watermark color.');
        }

        $size = 40;
        $angle = -45;
        for ($y = -100; $y < $height + 100; $y += 200) {
            for ($x = -100; $x < $width + 100; $x += 400) {
                imagettftext($canvas, $size, $angle, $x, $y, $color, $font, $block->value);
            }
        }
    }
}
