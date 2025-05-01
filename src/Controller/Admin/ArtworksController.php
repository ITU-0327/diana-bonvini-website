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
                
                // Debug uploaded file info
                if ($file instanceof UploadedFileInterface) {
                    $this->log('Received file: ' . $file->getClientFilename() . 
                               ', size: ' . $file->getSize() . 
                               ', type: ' . $file->getClientMediaType() . 
                               ', error: ' . $file->getError(), 'debug');
                } else {
                    $this->log('No file uploaded or invalid file', 'debug');
                }
                
                // Check if we have an image
                if (!$file instanceof UploadedFileInterface || $file->getError() !== UPLOAD_ERR_OK) {
                    $this->Flash->error(__('Please upload a valid image file.'));
                    return;
                }
                
                // Prepare the artwork data
                unset($data['image_path']);
                
                // Remove fields that don't exist in the database
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
                
                $this->log('Artwork saved with ID: ' . $artwork->artwork_id, 'debug');
                
                // Process the image
                try {
                    // Save the image locally first
                    $localPath = $this->_saveOriginalLocal($file, $artwork->artwork_id);
                    $this->log('Image saved locally to: ' . $localPath, 'debug');
                    
                    // Process image (create watermark and upload to R2 if possible)
                    try {
                        $this->_processImageUpload($file, $artwork->artwork_id, $r2StorageService);
                        $this->Flash->success(__('Artwork has been saved successfully with image.'));
                        
                        // Verify watermarked file exists
                        $watermarkedPath = WWW_ROOT . 'img' . DS . 'watermarked' . DS . "{$artwork->artwork_id}.jpg";
                        if (file_exists($watermarkedPath) && is_readable($watermarkedPath)) {
                            $this->log('Verified watermarked image exists at: ' . $watermarkedPath, 'debug');
                            
                            // Set appropriate file permissions to ensure web server can read it
                            chmod($watermarkedPath, 0644);
                            
                            // Clear browser cache for this image by appending a timestamp
                            $this->set('imageTimestamp', time());
                        } else {
                            $this->log('WARNING: Watermarked image not found at: ' . $watermarkedPath, 'warning');
                        }
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

        $this->set(compact('artwork'));
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
            // Get the data before we modify it
            $data = $this->request->getData();
            $file = $data['image_path'] ?? null;
            
            // Remove image_path from data before patching
            if (isset($data['image_path'])) {
                unset($data['image_path']);
            }
            
            
            // Update entity with form data
            $artwork = $this->Artworks->patchEntity($artwork, $data);

            if (!$this->Artworks->save($artwork)) {
                $this->Flash->error(__('Unable to update artwork. Please check the form and try again.'));
                $this->log('Failed to save artwork. Errors: ' . json_encode($artwork->getErrors()), 'error');
            } else {
                $this->Flash->success(__('Artwork has been updated successfully.'));
                $this->log('Artwork updated with ID: ' . $artwork->artwork_id, 'debug');
                
                // Process image upload if present
                if ($file instanceof UploadedFileInterface) {
                    $this->log('Processing uploaded image for artwork: ' . $artwork->artwork_id, 'debug');
                    
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        $this->Flash->error(__('Image upload failed with code {0}.', $file->getError()));
                        $this->log('Image upload error: ' . $file->getError(), 'error');
                    } else {
                        // Save the image locally first
                        try {
                            $localPath = $this->_saveOriginalLocal($file, $artwork->artwork_id);
                            $this->log('Image saved locally to: ' . $localPath, 'debug');
                            
                            // Process image (create watermark and upload to R2 if possible)
                            try {
                                $this->_processImageUpload($file, $artwork->artwork_id, $r2StorageService);
                                $this->Flash->success(__('Image updated successfully.'));
                                
                                // Verify watermarked file exists
                                $watermarkedPath = WWW_ROOT . 'img' . DS . 'watermarked' . DS . "{$artwork->artwork_id}.jpg";
                                if (file_exists($watermarkedPath) && is_readable($watermarkedPath)) {
                                    $this->log('Verified watermarked image exists at: ' . $watermarkedPath, 'debug');
                                    
                                    // Set appropriate file permissions to ensure web server can read it
                                    chmod($watermarkedPath, 0644);
                                    
                                    // Clear browser cache for this image by appending a timestamp
                                    $this->set('imageTimestamp', time());
                                } else {
                                    $this->log('WARNING: Watermarked image not found at: ' . $watermarkedPath, 'warning');
                                }
                            } catch (\Exception $e) {
                                $this->log('R2 storage processing failed: ' . $e->getMessage(), 'error');
                                $this->Flash->warning(__('Artwork updated but cloud storage of the image failed. The image was saved locally.'));
                            }
                        } catch (\Exception $e) {
                            $this->log('Image processing failed: ' . $e->getMessage(), 'error');
                            $this->Flash->error(__('Image processing failed: {0}', $e->getMessage()));
                        }
                    }
                } else {
                    $this->log('No new image uploaded for artwork: ' . $artwork->artwork_id, 'debug');
                }

                return $this->redirect(['action' => 'index']);
            }
        }

        $this->set(compact('artwork'));
    }

    /**
     * View method - Displays detailed information about a specific artwork
     *
     * @param string|null $id Artwork id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $this->set('title', 'View Artwork');
        
        // Get the artwork with any related orders
        $artwork = $this->Artworks->get($id, [
            'contain' => [],
        ]);
        
        // Try to find any orders related to this artwork
        $ArtworkOrders = TableRegistry::getTableLocator()->get('ArtworkOrders');
        $artworkOrders = $ArtworkOrders->find()
            ->where(['artwork_id' => $id])
            ->contain(['Orders' => ['Users']])
            ->all();
        
        $this->set(compact('artwork', 'artworkOrders'));
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
     * Update the availability status of an artwork
     *
     * @param string|null $id Artwork id.
     * @return \Cake\Http\Response|null Redirects to view.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function updateStatus(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post']);
        $artwork = $this->Artworks->get($id);
        
        $newStatus = $this->request->getData('availability_status');
        $validStatuses = ['available', 'sold', 'pending', 'reserved'];
        
        if (in_array($newStatus, $validStatuses)) {
            $artwork->availability_status = $newStatus;
            
            if ($this->Artworks->save($artwork)) {
                $this->Flash->success(__('The artwork status has been updated to {0}.', ucfirst($newStatus)));
            } else {
                $this->Flash->error(__('The artwork status could not be updated. Please try again.'));
            }
        } else {
            $this->Flash->error(__('Invalid status provided.'));
        }
        
        return $this->redirect(['action' => 'view', $id]);
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

        try {
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

            // Save watermarked version locally
            $watermarkedPath = WWW_ROOT . 'img' . DS . 'watermarked' . DS;
            if (!is_dir($watermarkedPath)) {
                if (!mkdir($watermarkedPath, 0755, true)) {
                    throw new Exception("Unable to create watermarked directory: {$watermarkedPath}");
                }
                $this->log("Created watermarked directory at {$watermarkedPath}", 'debug');
            }
            
            $watermarkedFilePath = $watermarkedPath . "{$artworkId}.jpg";
            $bytesWritten = file_put_contents($watermarkedFilePath, $jpeg);
            if ($bytesWritten === false) {
                throw new Exception("Failed to save watermarked image locally");
            } else {
                $this->log("Successfully saved watermarked image to {$watermarkedFilePath} ({$bytesWritten} bytes)", 'debug');
                // Ensure file permissions are correct
                chmod($watermarkedFilePath, 0644);
            }
            
            // Try to upload to R2, but continue even if it fails (we already have local copies)
            try {
                if (!$r2StorageService->put($wmKey, $jpeg)) {
                    $this->log("Warning: Failed to upload watermark to R2 storage", 'warning');
                } else {
                    $this->log("Successfully uploaded watermark to R2 storage with key {$wmKey}", 'debug');
                }
            } catch (Exception $e) {
                // Just log the error, don't rethrow as we have local copies
                $this->log("R2 upload error: " . $e->getMessage(), 'warning');
            }
        } catch (Exception $e) {
            $this->log("Error in _processImageUpload: " . $e->getMessage(), 'error');
            throw $e; // Rethrow for higher level error handling
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
        try {
            $dir = WWW_ROOT . 'img' . DS . 'Artworks';
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new Exception("Unable to create directory: {$dir}");
                }
                $this->log("Created directory at {$dir}", 'debug');
            }

            $target = $dir . DS . "{$artworkId}.jpg";
            // moveTo will overwrite if file exists
            $file->moveTo($target);
            
            // Set appropriate permissions
            chmod($target, 0644);
            
            $this->log("Successfully saved original image to {$target}", 'debug');
            
            // Verify the file actually exists and is readable
            if (!file_exists($target) || !is_readable($target)) {
                throw new Exception("File was moved but cannot be verified at: {$target}");
            }
            
            return $target;
        } catch (Exception $e) {
            $this->log("Error in _saveOriginalLocal: " . $e->getMessage(), 'error');
            throw $e; // Rethrow for higher level error handling
        }
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
