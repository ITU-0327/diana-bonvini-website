<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * UserOauths Controller
 *
 * @property \App\Model\Table\UserOauthsTable $UserOauths
 */
class UserOauthsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->UserOauths->find()
            ->contain(['Users']);
        $userOauths = $this->paginate($query);

        $this->set(compact('userOauths'));
    }

    /**
     * View method
     *
     * @param string|null $id User Oauth id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $userOauth = $this->UserOauths->get($id, contain: ['Users']);
        $this->set(compact('userOauth'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $userOauth = $this->UserOauths->newEmptyEntity();
        if ($this->request->is('post')) {
            $userOauth = $this->UserOauths->patchEntity($userOauth, $this->request->getData());
            if ($this->UserOauths->save($userOauth)) {
                $this->Flash->success(__('The user oauth has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user oauth could not be saved. Please, try again.'));
        }
        $users = $this->UserOauths->Users->find('list', limit: 200)->all();
        $this->set(compact('userOauth', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User Oauth id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $userOauth = $this->UserOauths->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $userOauth = $this->UserOauths->patchEntity($userOauth, $this->request->getData());
            if ($this->UserOauths->save($userOauth)) {
                $this->Flash->success(__('The user oauth has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user oauth could not be saved. Please, try again.'));
        }
        $users = $this->UserOauths->Users->find('list', limit: 200)->all();
        $this->set(compact('userOauth', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User Oauth id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $userOauth = $this->UserOauths->get($id);
        if ($this->UserOauths->delete($userOauth)) {
            $this->Flash->success(__('The user oauth has been deleted.'));
        } else {
            $this->Flash->error(__('The user oauth could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
