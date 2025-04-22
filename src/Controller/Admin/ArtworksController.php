<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\ArtworksController as BaseArtworksController;
use Cake\Event\EventInterface;

class ArtworksController extends BaseArtworksController
{
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        // Look in templates/Artworks, not templates/Admin/Artworks
        $this->viewBuilder()->setTemplatePath('Artworks');
    }
}
