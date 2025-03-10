<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ArtworksController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ArtworksController Test Case
 *
 * @uses \App\Controller\ArtworksController
 */
class ArtworksControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Artworks',
    ];
}
