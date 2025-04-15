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
        $contentBlock = $this->ContentBlocks->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
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
     * Delete method
     *
     * @param string|null $id Content Block id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $contentBlock = $this->ContentBlocks->get($id);
        if ($this->ContentBlocks->delete($contentBlock)) {
            $this->Flash->success(__('The content block has been deleted.'));
        } else {
            $this->Flash->error(__('The content block could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
