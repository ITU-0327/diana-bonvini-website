<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * ContentBlocks Controller
 *
 * @property \App\Model\Table\ContentBlocksTable $ContentBlocks
 */
class ContentBlocksController extends AppController
{
    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        // Prepare a query for distinct parent values.
        $parentsQuery = $this->ContentBlocks->find('list', [
            'keyField' => 'parent',
            'valueField' => 'parent',
        ])->distinct(['parent']);

        $parents = $parentsQuery->toArray();

        $query = $this->ContentBlocks->find();
        $contentBlocks = $this->paginate($query);

        $this->set(compact('contentBlocks', 'parents'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $contentBlock = $this->ContentBlocks->newEmptyEntity();
        if ($this->request->is('post')) {
            $contentBlock = $this->ContentBlocks->patchEntity($contentBlock, $this->request->getData());
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
     */
    public function edit(?string $id = null)
    {
        $contentBlock = $this->ContentBlocks->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $oldValue = $contentBlock->value;

            $this->ContentBlocks->patchEntity($contentBlock, $this->request->getData());

            if ($contentBlock->isDirty('value')) {
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

        $contentBlock->value          = $contentBlock->previous_value;
        $contentBlock->previous_value = null;

        if ($this->ContentBlocks->save($contentBlock)) {
            $this->Flash->success(__('The content block has been reverted to its previous value.'));
        } else {
            $this->Flash->error(__('The content block could not be reverted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
