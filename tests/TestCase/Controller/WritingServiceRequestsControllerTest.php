<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\WritingServiceRequestsController Test Case
 *
 * @uses \App\Controller\WritingServiceRequestsController
 */
class WritingServiceRequestsControllerTest extends TestCase
{
    use IntegrationTestTrait;

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
     * Test index method
     *
     * @return void
     * @uses \App\Controller\WritingServiceRequestsController::index()
     */
    public function testIndex(): void
    {
        $this->enableCsrfToken();

        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);

        $this->get('/writing-service-requests');

        $this->assertResponseOk();

        $this->assertResponseContains('Writing Service Requests');

        $this->assertResponseContains('creative_writing');

        $this->assertTemplate('index');

        $this->assertResponseContains('test.pdf');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\WritingServiceRequestsController::view()
     */
    public function testView(): void
    {
        $this->enableCsrfToken();

        $this->session([
            'Auth' => [
                'user_id' => '17fe31f7-2f61-4176-a036-172eed559e6f',
                'email' => 'tony.hsieh@example.com',
            ],
        ]);

        $requestId = 'ea42d088-4396-46d7-92d1-4e60c59b2ef7';

        $this->get('/writing-service-requests/view/' . $requestId);

        $this->assertResponseOk();

        $this->assertResponseContains('creative_writing');
        $this->assertResponseContains('under_5000');

        $this->assertTemplate('view');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\WritingServiceRequestsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\WritingServiceRequestsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\WritingServiceRequestsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test info method
     *
     * @return void
     * @uses \App\Controller\WritingServiceRequestsController::info()
     */
    public function testInfo(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
