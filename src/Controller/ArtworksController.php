<?php
declare(strict_types=1);

namespace App\Controller;

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
            $data = $this->request->getData();
            $image = $data['image_path'] ?? null;

            // Extracted upload+watermark logic:
            $watermarked = $this->_processImageUpload($image);
            $data['image_path'] = $watermarked;

            // Default status
            $data['availability_status'] = 'available';

            $artwork = $this->Artworks->patchEntity($artwork, $data);
            if ($this->Artworks->save($artwork)) {
                $this->Flash->success(__('The artwork has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The artwork could not be saved. Please, try again.'));
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

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data  = $this->request->getData();
            $image = $data['image_path'] ?? null;

            if ($image instanceof UploadedFileInterface && $image->getError() === UPLOAD_ERR_OK) {
                $data['image_path'] = $this->_processImageUpload($image);
            }

            $artwork = $this->Artworks->patchEntity($artwork, $data);
            if ($this->Artworks->save($artwork)) {
                $this->Flash->success(__('The artwork has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The artwork could not be saved. Please, try again.'));
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
     * @param \Psr\Http\Message\UploadedFileInterface|null $file
     * @return string|null  Relative path under img/Artworks or null
     * @throws \Exception
     */
    private function _processImageUpload(?UploadedFileInterface $file): ?string
    {
        if (
            !($file instanceof UploadedFileInterface)
            || $file->getError() !== UPLOAD_ERR_OK
        ) {
            return null;
        }

        if ($file->getClientMediaType() !== 'image/jpeg') {
            $this->Flash->error(__('Only JPEG images are allowed.'));

            return null;
        }

        $originalPath = $this->_saveOriginalImage($file);

        $dir  = WWW_ROOT . 'img' . DS . dirname($originalPath);
        $name = basename($originalPath);
        $wmName = 'wm_' . $name;
        $watermarkedRelative = dirname($originalPath) . '/' . $wmName;
        $watermarkedFull     = $dir . DS . $wmName;

        $this->_addTiledWatermark(
            WWW_ROOT . 'img' . DS . $originalPath,
            $watermarkedFull,
        );

        return $watermarkedRelative;
    }

    /**
     * Moves the uploaded file into img/Artworks and
     * returns its relative path (e.g. "Artworks/12345_pic.jpeg").
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file
     * @return string
     */
    private function _saveOriginalImage(UploadedFileInterface $file): string
    {
        $fileName     = time() . '_' . $file->getClientFilename();
        $relativeDir  = 'Artworks';
        $relativePath = $relativeDir . '/' . $fileName;
        $targetDir    = WWW_ROOT . 'img' . DS . $relativeDir;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $file->moveTo($targetDir . DS . $fileName);

        return $relativePath;
    }

    /**
     * Adds a tiled text watermark for JPEGs and writes out a PNG.
     *
     * @param string $originalPath Full path to the JPEG source.
     * @param string $outputPath   Full path where the PNG should be written.
     * @throws \Exception
     */
    private function _addTiledWatermark(string $originalPath, string $outputPath): void
    {
        $this->_assertOriginalIsJpeg($originalPath);

        $source = imagecreatefromjpeg($originalPath);
        if (!$source instanceof GdImage) {
            throw new Exception("Failed to load JPEG: $originalPath");
        }

        $watermark = null;
        try {
            [$w, $h] = [imagesx($source), imagesy($source)];
            $watermark = $this->_createTransparentCanvas($w, $h);
            $this->_drawTiledText($watermark, $w, $h);

            imagecopy($source, $watermark, 0, 0, 0, 0, $w, $h);
            $this->_ensureDirectoryExists(dirname($outputPath));

            if (!imagepng($source, $outputPath)) {
                throw new Exception("Failed to save PNG: $outputPath");
            }
        } finally {
            imagedestroy($source);
            if ($watermark instanceof GdImage) {
                imagedestroy($watermark);
            }
        }
    }

    /**
     * Asserts that the original image is a JPEG and readable.
     *
     * @param string $path Full path to the JPEG source.
     * @throws \Exception
     */
    private function _assertOriginalIsJpeg(string $path): void
    {
        if (!is_readable($path)) {
            throw new Exception("File not found or unreadable: $path");
        }
        $info = getimagesize($path);
        if ($info === false || $info['mime'] !== 'image/jpeg') {
            throw new Exception("Only JPEG originals are supported: $path");
        }
    }

    /**
     * Creates a truecolor canvas with transparency.
     *
     * @param int $width
     * @param int $height
     * @return \GdImage
     * @throws \Exception
     */
    private function _createTransparentCanvas(int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        if (!$canvas instanceof GdImage) {
            throw new Exception('Failed to create watermark canvas');
        }
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        if ($transparent === false) {
            imagedestroy($canvas);
            throw new Exception('Failed to allocate transparent color');
        }
        imagefill($canvas, 0, 0, $transparent);

        return $canvas;
    }

    /**
     * Draws the tiled watermark text onto the canvas.
     *
     * @param \GdImage $canvas
     * @param int      $width
     * @param int      $height
     * @throws \Exception
     */
    private function _drawTiledText(GdImage $canvas, int $width, int $height): void
    {
        $contentBlocks = TableRegistry::getTableLocator()->get('ContentBlocks');

        $block = $contentBlocks->find()
            ->select(['value'])
            ->where(['slug' => 'watermark-text'])
            ->first();

        if (!$block) {
            throw new Exception("Content block ‘logo’ not found in CMS");
        }

        $text = $block->value;
        $fontSize = 40;
        $angle= -45;
        $fontPath = WWW_ROOT . 'font/arial.ttf';

        if (!is_readable($fontPath)) {
            throw new Exception("Font file not found: $fontPath");
        }
        $color = imagecolorallocatealpha($canvas, 225, 225, 225, 96);
        if ($color === false) {
            throw new Exception('Failed to allocate text color');
        }

        for ($y = -100; $y < $height + 100; $y += 200) {
            for ($x = -100; $x < $width + 100; $x += 400) {
                imagettftext($canvas, $fontSize, $angle, $x, $y, $color, $fontPath, $text);
            }
        }
    }

    /**
     * Ensures the directory exists, or throws.
     *
     * @param string $dir
     * @throws \Exception
     */
    private function _ensureDirectoryExists(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new Exception("Failed to create directory: $dir");
        }
    }
}
