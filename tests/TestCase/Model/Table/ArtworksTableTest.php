<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArtworksTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArtworksTable Test Case
 */
class ArtworksTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ArtworksTable
     */
    protected $Artworks;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Artworks',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Artworks') ? [] : ['className' => ArtworksTable::class];
        $this->Artworks = $this->getTableLocator()->get('Artworks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Artworks);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ArtworksTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
