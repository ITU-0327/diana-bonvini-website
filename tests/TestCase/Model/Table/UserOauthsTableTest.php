<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UserOauthsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UserOauthsTable Test Case
 */
class UserOauthsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UserOauthsTable
     */
    protected $UserOauths;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.UserOauths',
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
        $config = $this->getTableLocator()->exists('UserOauths') ? [] : ['className' => UserOauthsTable::class];
        $this->UserOauths = $this->getTableLocator()->get('UserOauths', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->UserOauths);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\UserOauthsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\UserOauthsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
