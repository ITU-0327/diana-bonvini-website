<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController as BaseAdminController;
use Cake\Http\Response;
use Psr\Http\Message\UploadedFileInterface;
use SplFileInfo;

/**
 * ContentBlocks Controller
 *
 * @property \App\Model\Table\ContentBlocksTable $ContentBlocks
 */
class ContentBlocksController extends BaseAdminController
{
    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        // Prepare a query for distinct parent values.
        $parentsQuery = $this->ContentBlocks->find('list', keyField: 'parent', valueField: 'parent')
            ->distinct(['parent']);

        $parents = $parentsQuery->toArray();

        $contentBlocks = $this->ContentBlocks->find();

        $this->set(compact('contentBlocks', 'parents'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     * @throws \Random\RandomException
     */
    public function add()
    {
        $contentBlock = $this->ContentBlocks->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // If type is "image", process the uploaded file
            if (!empty($data['type']) && $data['type'] === 'image') {
                $upload = $data['value'];
                $newPath = $this->_handleImageUpload($upload);
                $data['value'] = $newPath;
            }

            $contentBlock = $this->ContentBlocks->patchEntity($contentBlock, $data);
            if ($this->ContentBlocks->save($contentBlock)) {
                $this->Flash->success(__('The content block has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The content block could not be saved. Please, try again.'));
        }
        $this->set(compact('contentBlock'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Content Block id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Random\RandomException
     */
    public function edit(?string $id = null)
    {
        $contentBlock = $this->ContentBlocks->get($id);
        $oldValue = $contentBlock->value;

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            if ($contentBlock->type === 'image') {
                $uploaded = $data['value'];
                $newPath  = $this->_handleImageUpload($uploaded);
                if ($newPath) {
                    $data['value'] = $newPath;
                    $contentBlock->previous_value = $oldValue;
                } else {
                    $data['value'] = $oldValue;
                }
            }

            $this->ContentBlocks->patchEntity($contentBlock, $data);

            if ($contentBlock->type !== 'image' && $contentBlock->isDirty('value')) {
                $contentBlock->previous_value = $oldValue;
            }

            if ($this->ContentBlocks->save($contentBlock)) {
                $this->Flash->success(__('The content block has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The content block could not be saved. Please, try again.'));
        }

        $this->set(compact('contentBlock'));
    }

    /**
     * Revert method
     *
     * Restores the previous_value into value, and clears previous_value.
     *
     * @param string|null $id Content Block id.
     * @return \Cake\Http\Response|null Redirects on success or failure.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Http\Exception\MethodNotAllowedException When not POST.
     */
    public function revert(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post']);

        $contentBlock = $this->ContentBlocks->get($id);

        if (empty($contentBlock->previous_value)) {
            $this->Flash->error(__('Nothing to revert for this content block.'));

            return $this->redirect(['action' => 'index']);
        }

        $contentBlock->value = $contentBlock->previous_value;
        $contentBlock->previous_value = null;

        if ($this->ContentBlocks->save($contentBlock)) {
            $this->Flash->success(__('The content block has been reverted to its previous value.'));
        } else {
            $this->Flash->error(__('The content block could not be reverted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Handles an uploaded file for image‐type content blocks.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $upload The uploaded file object.
     * @param string|null $filenamePrefix Optional prefix for the filename.
     * @return string|null Relative path under webroot/img/ if saved, or null otherwise.
     * @throws \Random\RandomException
     */
    protected function _handleImageUpload(UploadedFileInterface $upload, ?string $filenamePrefix = null): ?string
    {
        if ($upload->getError() !== UPLOAD_ERR_OK) {
            if ($upload->getError() === UPLOAD_ERR_INI_SIZE) {
                $this->Flash->error(__('The file you uploaded is too big'));
            }

            return null;
        }

        $clientFilename = $upload->getClientFilename() ?? '';
        $rawExt = pathinfo($clientFilename, PATHINFO_EXTENSION);
        $extension = preg_replace(
            '/[^a-z0-9]/',
            '',
            strtolower($rawExt),
        );

        $prefix = $filenamePrefix ? $filenamePrefix . '.' : '';
        $filename = $prefix . md5(random_bytes(10)) . '.' . $extension;

        $destDir = new SplFileInfo(WWW_ROOT . 'img' . DS . 'content_blocks' . DS);
        if (!$destDir->isDir()) {
            mkdir($destDir->getPathname(), 0777, true);
        }

        $upload->moveTo($destDir->getPathname() . DS . $filename);

        return 'content_blocks/' . $filename;
    }
}
