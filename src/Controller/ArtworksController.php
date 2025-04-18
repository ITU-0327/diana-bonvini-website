<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Exception;

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
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Artworks->find()->where(['is_deleted' => 0]);
        $artworks = $this->paginate($query);

        $this->set(compact('artworks'));
    }

    /**
     * View method
     *
     * @param string|null $id Artwork id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $artwork = $this->Artworks->get($id, contain: []);
        $this->set(compact('artwork'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $artwork = $this->Artworks->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $image = $data['image_path'];
            if ($image && $image->getError() === UPLOAD_ERR_OK) {
                $fileName = time() . '_' . $image->getClientFilename();
                $relativePath = 'Artworks/' . $fileName;
                $targetPath = WWW_ROOT . 'img' . DS . $relativePath;

                $dir = WWW_ROOT . 'img' . DS . 'Artworks';
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $image->moveTo($targetPath);

                $this->addTiledWatermark($targetPath, $targetPath);

                $data['image_path'] = $relativePath;
            } else {
                $data['image_path'] = null;
            }

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
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $artwork = $this->Artworks->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $artwork = $this->Artworks->patchEntity($artwork, $this->request->getData());
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
     * @param string $originalPath
     * @param string $outputPath
     * @return void
     * @throws \Exception
     */
    private function addTiledWatermark(string $originalPath, string $outputPath): void
    {
        if (!file_exists($originalPath) || !is_file($originalPath)) {
            throw new Exception("Original image does not exist or is not a valid file: $originalPath");
        }

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $info = getimagesize($originalPath);
        if (!$info || !isset($info['mime'])) {
            throw new Exception("Unable to obtain image type: $originalPath");
        }

        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $im = imagecreatefromjpeg($originalPath);
                break;
            case 'image/png':
                $im = imagecreatefrompng($originalPath);
                break;
            case 'image/webp':
                $im = imagecreatefromwebp($originalPath);
                break;
            default:
                throw new Exception("Unsupported picture types: $mime");
        }

        if (!$im) {
            throw new Exception("Unable to load original image resource: $originalPath");
        }

        $width = imagesx($im);
        $height = imagesy($im);

        $watermark = imagecreatetruecolor($width, $height);
        imagesavealpha($watermark, true);
        $transparent = imagecolorallocatealpha($watermark, 0, 0, 0, 127);
        imagefill($watermark, 0, 0, $transparent);

        $text = 'Diana Bonvini';
        $fontSize = 50;
        $angle = -45;
        $fontPath = WWW_ROOT . 'font/arial.ttf';
        if (!file_exists($fontPath)) {
            throw new Exception("Files not found: $fontPath");
        }
        $textColor = imagecolorallocatealpha($watermark, 225, 225, 225, 75);

        for ($y = -100; $y < $height + 100; $y += 200) {
            for ($x = -100; $x < $width + 100; $x += 400) {
                imagettftext($watermark, $fontSize, $angle, $x, $y, $textColor, $fontPath, $text);
            }
        }

        imagecopy($im, $watermark, 0, 0, 0, 0, $width, $height);

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($im, $outputPath, 90);
                break;
            case 'image/png':
                imagepng($im, $outputPath);
                break;
            case 'image/webp':
                imagewebp($im, $outputPath);
                break;
        }

        imagedestroy($im);
        imagedestroy($watermark);
    }
}
