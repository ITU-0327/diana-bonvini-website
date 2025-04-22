<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\OrdersController as BaseOrdersController;
use Cake\Event\EventInterface;

/**
 * Orders Controller (Admin prefix)
 *
 * Inherits all actions (index, view, etc.)
 * and reuses the non‑prefixed Orders templates.
 */
class OrdersController extends BaseOrdersController
{
    /**
     * beforeRender hook.
     * Use the templates/Orders/ folder instead of templates/Admin/Orders/
     *
     * @param \Cake\Event\EventInterface $event The beforeRender event.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        // Point Cake to the shared Orders templates
        $this->viewBuilder()->setTemplatePath('Orders');
    }
}
