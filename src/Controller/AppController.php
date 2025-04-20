<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Log\Log;
use Exception;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');

        $this->loadComponent('Authentication.Authentication');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * Helper method to send emails consistently across the application
     *
     * @param string $mailerClass The Mailer class to use
     * @param string $method The method to call on the Mailer
     * @param array $args The arguments to pass to the Mailer method
     * @param string|null $recipientEmail The recipient's email (for logging/flash messages)
     * @return bool Whether the email was sent successfully
     */
    protected function sendEmail(string $mailerClass, string $method, array $args, ?string $recipientEmail = null): bool
    {
        try {
            $mailer = new $mailerClass();
            $result = $mailer->send($method, $args);

            if ($result) {
                if ($recipientEmail) {
                    $this->Flash->success('Email has been sent to ' . $recipientEmail);
                }

                return true;
            } else {
                Log::error("Failed to send email using {$mailerClass}::{$method}. The mailer returned false.");

                return false;
            }
        } catch (Exception $e) {
            Log::error("Error sending email using {$mailerClass}::{$method}: " . $e->getMessage());

            return false;
        }
    }
}
