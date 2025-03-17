<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WritingServiceRequestsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WritingServiceRequestsTable Test Case
 */
class WritingServiceRequestsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WritingServiceRequestsTable
     */
    protected $WritingServiceRequests;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.WritingServiceRequests',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('WritingServiceRequests') ? [] : ['className' => WritingServiceRequestsTable::class];
        $this->WritingServiceRequests = $this->getTableLocator()->get('WritingServiceRequests', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->WritingServiceRequests);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\WritingServiceRequestsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\WritingServiceRequestsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
