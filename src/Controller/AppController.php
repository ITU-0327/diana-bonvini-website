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
        $this->loadComponent('FormProtection', [
            'unlockedActions' => [
                'add', 
                'edit', 
                'uploadDocument', 
                'paymentSuccess',
                'sendTimeSlots',
                'sendMessage', 
                'sendPaymentRequest',
                'updateStatus',
                'setPrice',
                'markAsPaid',
                'fetchMessages',
                'getAvailableTimeSlots'
            ],
            'unlockedFields' => [
                'document', 
                'message_text', 
                'time_slots', 
                'amount', 
                'description',
                'status',
                'final_price',
                'payment_id',
                '_csrfToken',
                'writing_service_request_id',
                'coaching_service_request_id'
            ],
            'validatePost' => false, // Temporarily disable for debugging
        ]);
    }
}
