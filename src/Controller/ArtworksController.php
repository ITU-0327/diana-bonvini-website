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
        $this->_drawCornerDiagonalText($im, $width, $height);

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
     * Draw one watermark string on each diagonal (“＼” and “／”) using
     * a single pass per diagonal.  Five blank “letter slots” are added
     * before and after the text, and every internal space is expanded to
     * ten blank “letter slots” so the watermark has obvious gaps.
     *
     * @param \GdImage $canvas Destination GD image.
     * @param int      $width  Image width  (pixels).
     * @param int      $height Image height (pixels).
     *
     * @throws \Exception If the font file is missing or GD cannot allocate colours.
     */
    private function _drawCornerDiagonalText(GdImage $canvas, int $width, int $height): void
    {
        /* 1. Retrieve watermark text -------------------------------------------- */
        $rawText = TableRegistry::getTableLocator()
            ->get('ContentBlocks')
            ->find()
            ->select(['value'])
            ->where(['slug' => 'watermark-text'])
            ->firstOrFail()
            ->value;

        /* 2. Pad the string:
         *    – add five spaces at both ends
         *    – replace every internal space with ten spaces                  */
        $displayText = str_repeat(' ', 10)
            . str_replace(' ', str_repeat(' ', 25), $rawText)
            . str_repeat(' ', 10);

        /* 3. Font and colour (single fill) -------------------------------------- */
        $font = WWW_ROOT . 'font/MPLUSRounded1c-Medium.ttf';
        if (!is_readable($font)) {
            throw new \Exception("Missing font at $font");
        }

        // Mid-grey, highly transparent (α = 90 → ≈ 71 % transparent)
        $wmColor = imagecolorallocatealpha($canvas, 200, 200, 200,  90);
        if ($wmColor === false) {
            throw new \Exception('Unable to allocate watermark colour.');
        }

        /* 4. Diagonal geometry --------------------------------------------------- */
        $diagLen    = hypot($width, $height);
        $thetaRad   = atan2($height, $width);
        $thetaDeg   = rad2deg($thetaRad);
        $targetProj = $diagLen * 0.88;              // leave 12 % margin

        /* 5. Binary-search the largest font size that fits ----------------------- */
        $lo = 10;
        $hi = 400;
        while ($lo < $hi) {
            $mid  = intdiv($lo + $hi + 1, 2);
            $bb   = imagettfbbox($mid, 0, $font, $displayText);
            $proj = hypot($bb[2] - $bb[0], $bb[1] - $bb[7]);
            ($proj <= $targetProj) ? $lo = $mid : $hi = $mid - 1;
        }
        $fontSize = $lo;

        /* 6. Helper: compute baseline so bbox centre == image centre ------------- */
        $baseFor = function (float $angleDeg)
        use ($font, $displayText, $fontSize, $width, $height): array {
            $bb = imagettfbbox($fontSize, $angleDeg, $font, $displayText);
            $minX = min($bb[0], $bb[2], $bb[4], $bb[6]);
            $maxX = max($bb[0], $bb[2], $bb[4], $bb[6]);
            $minY = min($bb[1], $bb[3], $bb[5], $bb[7]);
            $maxY = max($bb[1], $bb[3], $bb[5], $bb[7]);

            $imgCX = $width  / 2;
            $imgCY = $height / 2;

            return [
                (int)round($imgCX - ($minX + $maxX) / 2),   // x
                (int)round($imgCY - ($minY + $maxY) / 2),   // y
            ];
        };

        [$x1, $y1] = $baseFor(-$thetaDeg);  // “＼” diagonal
        [$x2, $y2] = $baseFor(+$thetaDeg);  // “／” diagonal

        /* 7. Draw each diagonal exactly once ------------------------------------ */
        imagettftext($canvas, $fontSize, -$thetaDeg, $x1, $y1,
            $wmColor, $font, $displayText);
        imagettftext($canvas, $fontSize, +$thetaDeg, $x2, $y2,
            $wmColor, $font, $displayText);
    }
}
