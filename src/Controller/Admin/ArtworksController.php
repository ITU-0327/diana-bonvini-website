<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController as BaseAdminController;
use App\Service\R2StorageService;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;
use GdImage;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * Artworks Controller (Admin)
 *
 * Admin interface for managing artworks. Uses dedicated admin templates.
 *
 * @property \App\Model\Table\ArtworksTable $Artworks
 */
class ArtworksController extends BaseAdminController
{
    /**
     * Index method - Shows all artworks with admin functionality
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $this->set('title', 'Artwork Management');

        // Include soft delete filter and load artwork variants
        $query = $this->Artworks->find()
            ->where(['Artworks.is_deleted' => 0])
            ->contain(['ArtworkVariants' => function ($q) {
                return $q->where(['ArtworkVariants.is_deleted' => 0]);
            }]);
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
        $artwork = $this->Artworks->newEmptyEntity();

        // Initialize empty variants for all size and print type combinations
        $artwork->set('artwork_variants', []);
        $sizes = ['A3', 'A2', 'A1'];
        $printTypes = ['canvas', 'print'];
        foreach ($sizes as $dim) {
            foreach ($printTypes as $pt) {
                $variant = $this->Artworks->ArtworkVariants->newEmptyEntity();
                $variant->dimension = $dim;
                $variant->print_type = $pt;
                $artwork->artwork_variants[] = $variant;
            }
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['availability_status'] = 'available';
            // only keep variants where price is provided
            if (isset($data['artwork_variants'])) {
                $data['artwork_variants'] = array_filter(
                    $data['artwork_variants'],
                    fn($v) => isset($v['price']) && $v['price'] !== '' && floatval($v['price']) > 0,
                );
            }

            $artwork = $this->Artworks->patchEntity($artwork, $data, [
                'associated' => ['ArtworkVariants'],
            ]);

            if (!$this->Artworks->save($artwork, ['associated' => ['ArtworkVariants']])) {
                $this->Flash->error(__('Unable to save artwork.'));
            } else {
                $this->Flash->success(__('Artwork saved.'));
                $file = $this->request->getData('image_path');
                if ($file instanceof UploadedFileInterface) {
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        $this->Flash->error(__('Image upload failed with code {0}.', $file->getError()));
                    } else {
                        $this->_processImageUpload($file, $artwork->artwork_id, $r2StorageService);
                    }
                }

                return $this->redirect(['action' => 'index']);
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
        // Load artwork with variants
        $artwork = $this->Artworks->get($id, contain: [
            'ArtworkVariants' => function ($q) {
                return $q->where(['ArtworkVariants.is_deleted' => 0]);
            },
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            // Get the data before we modify it
            $data = $this->request->getData();
            $file = $data['image_path'] ?? null;

            // Remove image_path from data before patching
            if (isset($data['image_path'])) {
                unset($data['image_path']);
            }

            // Filter out empty variant entries
            if (isset($data['artwork_variants'])) {
                $data['artwork_variants'] = array_filter(
                    $data['artwork_variants'],
                    fn($v) => isset($v['price']) && $v['price'] !== '',
                );
            }

            // Update entity with form data including variants
            $artwork = $this->Artworks->patchEntity($artwork, $data, [
                'associated' => ['ArtworkVariants'],
            ]);

            if (!$this->Artworks->save($artwork, ['associated' => ['ArtworkVariants']])) {
                $this->Flash->error(__('Unable to update artwork. Please check the form and try again.'));
            } else {
                $this->Flash->success(__('Artwork has been updated successfully.'));

                if ($file instanceof UploadedFileInterface) {
                    if ($file->getError() !== UPLOAD_ERR_OK) {
                        $this->Flash->error(__('Image upload failed with code {0}.', $file->getError()));
                    } else {
                        $this->_processImageUpload($file, $artwork->artwork_id, $r2StorageService);
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
        $validStatuses = ['available', 'sold'];

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
    private function _processImageUpload(UploadedFileInterface $file, string $artworkId, R2StorageService $r2StorageService): void
    {
        $this->_assertJpeg($file);

        // Save original on server
        $localPath = $this->_saveOriginalLocal($file, $artworkId);

        // Read it into memory for watermarking (or you could re‑use $file->getStream())
        $bytes = file_get_contents($localPath);
        if ($bytes === false) {
            throw new Exception("Failed to read saved file: $localPath");
        }

        // Generate watermarked JPG in memory
        $jpeg = $this->_createWatermarkedJpeg($bytes);
        $wmKey = "{$artworkId}_wm.jpg";

        if (!$r2StorageService->put($wmKey, $jpeg)) {
            $this->Flash->error(__('Failed to upload watermark to storage.'));
        }
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
            throw new Exception("Unable to create directory: $dir");
        }

        $target = $dir . DS . "$artworkId.jpg";
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
     * @param int      $width  Image width (pixels).
     * @param int      $height Image height (pixels).
     * @throws \Exception If the font file is missing or GD cannot allocate colours.
     */
    private function _drawCornerDiagonalText(GdImage $canvas, int $width, int $height): void
    {
        // Retrieve watermark text
        $rawText = TableRegistry::getTableLocator()->get('ContentBlocks')->find()
            ->select(['value'])
            ->where(['slug' => 'watermark-text'])
            ->firstOrFail()
            ->value;

        // Pad the string
        $displayText = str_repeat(' ', 10)
            . str_replace(' ', str_repeat(' ', 15), $rawText)
            . str_repeat(' ', 10);

        $font = WWW_ROOT . 'font/MPLUSRounded1c-Medium.ttf';
        if (!is_readable($font)) {
            throw new Exception("Missing font at $font");
        }

        $wmColor = imagecolorallocatealpha($canvas, 200, 200, 200, 90);
        if ($wmColor === false) {
            throw new Exception('Unable to allocate watermark colour.');
        }

        // Diagonal geometry
        $diagLen = hypot($width, $height);
        $thetaRad = atan2($height, $width);
        $thetaDeg = rad2deg($thetaRad);
        $targetProj = $diagLen * 0.88;

        // Binary-search the largest font size that fits
        $lo = 10;
        $hi = 400;
        while ($lo < $hi) {
            $mid = intdiv($lo + $hi + 1, 2);

            $bbRaw = imagettfbbox($mid, 0, $font, $displayText);
            if ($bbRaw === false) {
                throw new RuntimeException(
                    "Unable to calculate bounding box for watermark text at size $mid",
                );
            }
            $bb = $bbRaw;

            $proj = hypot($bb[2] - $bb[0], $bb[1] - $bb[7]);
            $proj <= $targetProj ? $lo = $mid : $hi = $mid - 1;
        }
        $fontSize = $lo;

        // Compute positions for each diagonal
        [$x1, $y1] = $this->_calculateCenteredTextPosition(-$thetaDeg, $font, $fontSize, $displayText, $width, $height);
        [$x2, $y2] = $this->_calculateCenteredTextPosition(+$thetaDeg, $font, $fontSize, $displayText, $width, $height);

        // Draw each diagonal exactly once
        imagettftext($canvas, $fontSize, -$thetaDeg, $x1, $y1, $wmColor, $font, $displayText);
        imagettftext($canvas, $fontSize, +$thetaDeg, $x2, $y2, $wmColor, $font, $displayText);
    }

    /**
     * Calculate the [x,y] starting point to center a rotated text string
     * on an image of given dimensions.
     *
     * @param float  $angleDeg Angle to rotate text by (degrees).
     * @param string $font     Path to TTF font.
     * @param int    $fontSize Font size in points.
     * @param string $text     The text string.
     * @param int    $width    Canvas width.
     * @param int    $height   Canvas height.
     * @return array<int> [x, y] coordinates to pass to imagettftext().
     */
    private function _calculateCenteredTextPosition(
        float $angleDeg,
        string $font,
        int $fontSize,
        string $text,
        int $width,
        int $height,
    ): array {
        $bbRaw = imagettfbbox($fontSize, $angleDeg, $font, $text);
        if ($bbRaw === false) {
            throw new RuntimeException('Failed to compute rotated text bounding box');
        }
        $bb = $bbRaw;

        // find the bounding box extents
        $minX = min($bb[0], $bb[2], $bb[4], $bb[6]);
        $maxX = max($bb[0], $bb[2], $bb[4], $bb[6]);
        $minY = min($bb[1], $bb[3], $bb[5], $bb[7]);
        $maxY = max($bb[1], $bb[3], $bb[5], $bb[7]);

        // center of our canvas
        $imgCX = $width  / 2;
        $imgCY = $height / 2;

        // shift text so its center aligns with image center
        $x = (int)round($imgCX - ($minX + $maxX) / 2);
        $y = (int)round($imgCY - ($minY + $maxY) / 2);

        return [$x, $y];
    }
}
