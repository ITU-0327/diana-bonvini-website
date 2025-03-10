<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\UserOauthsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\UserOauthsController Test Case
 *
 * @uses \App\Controller\UserOauthsController
 */
class UserOauthsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.UserOauths',
        'app.Users',
    ];
}
