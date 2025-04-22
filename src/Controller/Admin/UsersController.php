<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\UsersController as BaseUsersController;
use Cake\Event\EventInterface;

/**
 * Users Controller (Admin prefix)
 *
 * Inherits all actions from App\Controller\UsersController
 * and reuses the non‑prefixed Users templates.
 */
class UsersController extends BaseUsersController
{
    /**
     * beforeRender hook.
     * Point Cake to use templates/Users instead of templates/Admin/Users
     *
     * @param \Cake\Event\EventInterface $event The beforeRender event.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        $this->viewBuilder()->setTemplatePath('Users');
    }
}
