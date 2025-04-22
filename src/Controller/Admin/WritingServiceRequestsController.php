<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\WritingServiceRequestsController as BaseWritingServiceRequestsController;
use Cake\Event\EventInterface;

/**
 * WritingServiceRequests Controller (Admin prefix)
 *
 * Inherits all actions (index, view, add, edit, delete, etc.)
 * and reuses the non‑prefixed WritingServiceRequests templates.
 */
class WritingServiceRequestsController extends BaseWritingServiceRequestsController
{
    /**
     * beforeRender hook.
     * Point Cake to use templates/WritingServiceRequests instead of templates/Admin/WritingServiceRequests
     *
     * @param \Cake\Event\EventInterface $event The beforeRender event.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        $this->viewBuilder()->setTemplatePath('WritingServiceRequests');
    }
}
