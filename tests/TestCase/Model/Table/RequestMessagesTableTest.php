<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RequestMessagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RequestMessagesTable Test Case
 */
class RequestMessagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RequestMessagesTable
     */
    protected $RequestMessages;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.RequestMessages',
        'app.Requests',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('RequestMessages') ? [] : ['className' => RequestMessagesTable::class];
        $this->RequestMessages = $this->getTableLocator()->get('RequestMessages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->RequestMessages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\RequestMessagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\RequestMessagesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
