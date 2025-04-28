<?php
declare(strict_types=1);

namespace App\Controller;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;
use GdImage;
use Psr\Http\Message\UploadedFileInterface;

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
        $query = $this->Artworks->find()->where(['is_deleted' => 0]);
        $artworks = $this->paginate($query);

        $this->set(compact('artworks'));
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
        $artwork = $this->Artworks->get($id);
        $this->set(compact('artwork'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     * @throws \Exception
     */
    public function add()
    {
        $artwork = $this->Artworks->newEmptyEntity();
        if ($this->request->is('post')) {
            $artwork = $this->Artworks->patchEntity($artwork, $this->request->getData());
            $artwork->availability_status = 'available';

            if (!$this->Artworks->save($artwork)) {
                $this->Flash->error(__('Unable to save artwork.'));
            } else {
                $this->Flash->success(__('Artwork saved.'));
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
        $this->set(compact('artwork'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Artwork id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException|\Exception When record not found.
     */
    public function edit(?string $id = null)
    {
        $artwork = $this->Artworks->get($id);
        if ($this->request->is(['patch','post','put'])) {
            $artwork = $this->Artworks->patchEntity($artwork, $this->request->getData());

            if (!$this->Artworks->save($artwork)) {
                $this->Flash->error(__('Unable to update artwork.'));
            } else {
                $this->Flash->success(__('Artwork updated.'));
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
        $this->set(compact('artwork'));
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
        if ($this->Artworks->delete($artwork)) {
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
     * @throws \Exception If the file is not JPEG or any imaging/S3 error occurs.
     */
    private function _processImageUpload(UploadedFileInterface $file, string $artworkId): void
    {
        $this->_assertJpeg($file);

        // Save original on server
        $localPath = $this->_saveOriginalLocal($file, $artworkId);

        // Read it into memory for watermarking (or you could re‑use $file->getStream())
        $bytes = file_get_contents($localPath);
        if ($bytes === false) {
            throw new Exception("Failed to read saved file: {$localPath}");
        }

        // Generate watermarked JPG in memory
        $jpeg = $this->_createWatermarkedJpeg($bytes);
        $wmKey = "{$artworkId}_wm.jpg";

        // Upload only the watermark to R2
        $this->_putR2Object($wmKey, $jpeg);
    }

    /**
     * Moves the uploaded JPEG into webroot/img/Artworks/{artworkId}.jpg.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file
     * @param string                                  $artworkId
     * @return string Full filesystem path to the saved file.
     * @throws \Exception On any filesystem error.
     */
    private function _saveOriginalLocal(UploadedFileInterface $file, string $artworkId): string
    {
        $dir  = WWW_ROOT . 'img' . DS . 'Artworks';
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
    private function _assertJpeg(UploadedFileInterface $file): void
    {
        if ($file->getClientMediaType() !== 'image/jpeg') {
            throw new Exception('Only JPEG uploads are allowed.');
        }
    }

    /**
     * Creates and returns a configured AWS S3 client pointed at your R2 endpoint.
     *
     * @return \Aws\S3\S3Client
     */
    private function _getR2Client(): S3Client
    {
        $r2 = Configure::read('R2');
        $creds = new Credentials($r2['accessKeyId'], $r2['secretAccessKey']);

        return new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => "https://{$r2['accountId']}.r2.cloudflarestorage.com",
            'use_path_style_endpoint' => true,
            'credentials' => $creds,
        ]);
    }

    /**
     * Uploads a payload to R2 under the given key, with the given ACL.
     *
     * @param string $key  Object key (e.g. "originals/123.jpg")
     * @param string $body Raw bytes or stream resource
     * @return void
     * @throws \Exception On any S3 error
     */
    private function _putR2Object(string $key, string $body): void
    {
        $client = $this->_getR2Client();
        $bucket = Configure::read('R2.bucket');

        // Map only the types you care about:
        $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        // Pick from the map or fall back
        $contentType = $map[$ext] ?? 'application/octet-stream';

        $client->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'Body' => $body,
            'ContentType' => $contentType,
            'ContentDisposition' => 'inline; filename="' . basename($key) . '"',
            'CacheControl' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Applies a tiled text watermark to the given JPEG bytes and
     * returns the resulting image as a PNG binary string.
     *
     * @param string $jpegBytes Raw JPEG file contents.
     * @return string           PNG‐encoded binary string with watermark.
     * @throws \Exception      On invalid image data or GD failures.
     */
    private function _createWatermarkedJpeg(string $jpegBytes): string
    {
        $im = imagecreatefromstring($jpegBytes);
        if (!$im instanceof GdImage) {
            throw new Exception('Invalid JPEG data.');
        }

        $width = imagesx($im);
        $height = imagesy($im);
        $this->_drawDiagonalText($im, $width, $height);

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
     * @param int      $width  Width of the canvas/image.
     * @param int      $height Height of the canvas/image.
     * @throws \Exception If the CMS block or font is missing, or GD fails.
     */
    private function _drawTiledText(GdImage $canvas, int $width, int $height): void
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

        $size  = 40;
        $angle = -45;
        for ($y = -100; $y < $height + 100; $y += 200) {
            for ($x = -100; $x < $width + 100; $x += 400) {
                imagettftext($canvas, $size, $angle, $x, $y, $color, $font, $block->value);
            }
        }
    }

    private function _drawDiagonalText(GdImage $canvas, int $width, int $height): void
    {
        /* 1. get text and font ---------------------------------------------------- */
        $block = TableRegistry::getTableLocator()
            ->get('ContentBlocks')
            ->find()
            ->select(['value'])
            ->where(['slug' => 'watermark-text'])
            ->firstOrFail();

        $font = WWW_ROOT . 'font/Arial.ttf';
        if (!is_readable($font)) {
            throw new \Exception("Missing font at $font");
        }

        /* 2. text color --------------------------------------------- */
        $color = imagecolorallocatealpha($canvas, 225, 225, 225, 96);
        if ($color === false) {
            throw new \Exception('Unable to allocate watermark color.');
        }

        /* 3. text details ------------------------------------------------------------- */
        $size   = 80;
        $step   = 300;
        $margin = max($width, $height);

        $c = $height / 2 - $width / 2;

        for ($x = -$margin; $x < $width + $margin; $x += $step) {
            $y = $x + $c;
            imagettftext($canvas, $size, -45, (int)$x, (int)$y, $color, $font, $block->value);
        }

        $d = $height / 2 + $width / 2;

        for ($x = -$margin; $x < $width + $margin; $x += $step) {
            $y = -$x + $d;
            imagettftext($canvas, $size, 45, (int)$x, (int)$y, $color, $font, $block->value);
        }
    }
}
