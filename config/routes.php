<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/landing.php)...
         */
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'landing'], ['_name' => 'home']);

        // Connect static pages
        $builder->connect('/about', ['controller' => 'Pages', 'action' => 'display', 'about']);
        $builder->connect('/contact', ['controller' => 'Pages', 'action' => 'display', 'contact']);
        $builder->connect('/writing-service-requests/info', ['controller' => 'Pages', 'action' => 'display', 'info']);
        $builder->connect('/coaching-service-requests/info', ['controller' => 'Pages', 'action' => 'display', 'coaching_info']);

        // Connect any additional page requests to the Pages controller.
        $builder->connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);

        // API routes for AJAX functionality
        $builder->connect('/writing-service-requests/fetch-messages/*', ['controller' => 'WritingServiceRequests', 'action' => 'fetchMessages']);
        $builder->connect('/writing-service-requests/fetch-messages/:id', ['controller' => 'WritingServiceRequests', 'action' => 'fetchMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+'])
            ->setPass(['id']);
        $builder->connect('/writing-service-requests/fetch-messages/:id/:lastMessageId', ['controller' => 'WritingServiceRequests', 'action' => 'fetchMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+', 'lastMessageId' => '[a-zA-Z0-9-]+'])
            ->setPass(['id', 'lastMessageId']);

        // AJAX endpoint for getting messages as HTML
        $builder->connect('/writing-service-requests/getMessages/:id', ['controller' => 'WritingServiceRequests', 'action' => 'getMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+'])
            ->setPass(['id']);

        // Coaching service requests API routes
        $builder->connect('/coaching-service-requests/fetch-messages/*', ['controller' => 'CoachingServiceRequests', 'action' => 'fetchMessages']);
        $builder->connect('/coaching-service-requests/fetch-messages/:id', ['controller' => 'CoachingServiceRequests', 'action' => 'fetchMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+'])
            ->setPass(['id']);
        $builder->connect('/coaching-service-requests/fetch-messages/:id/:lastMessageId', ['controller' => 'CoachingServiceRequests', 'action' => 'fetchMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+', 'lastMessageId' => '[a-zA-Z0-9-]+'])
            ->setPass(['id', 'lastMessageId']);

        // Payment routes for writing service requests
        $builder->connect('/writing-service-requests/payDirect', ['controller' => 'WritingServiceRequests', 'action' => 'payDirect']);
        $builder->connect('/writing-service-requests/pay/:id/:paymentId', ['controller' => 'WritingServiceRequests', 'action' => 'pay'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+', 'paymentId' => '[a-zA-Z0-9-]+'])
            ->setPass(['id', 'paymentId']);
        $builder->connect('/writing-service-requests/payment-success/:id/:paymentId', ['controller' => 'WritingServiceRequests', 'action' => 'paymentSuccess'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+', 'paymentId' => '[a-zA-Z0-9-]+'])
            ->setPass(['id', 'paymentId']);
        $builder->connect('/writing-service-requests/checkPaymentStatus/:id', ['controller' => 'WritingServiceRequests', 'action' => 'checkPaymentStatus'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+'])
            ->setPass(['id']);

        // Payment routes for coaching service requests
        $builder->connect('/coaching-service-requests/payDirect', ['controller' => 'CoachingServiceRequests', 'action' => 'payDirect']);
        $builder->connect('/coaching-service-requests/pay/:id/:paymentId', ['controller' => 'CoachingServiceRequests', 'action' => 'pay'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+', 'paymentId' => '[a-zA-Z0-9-]+'])
            ->setPass(['id', 'paymentId']);
        $builder->connect('/coaching-service-requests/payment-success/:id/:paymentId', ['controller' => 'CoachingServiceRequests', 'action' => 'paymentSuccess'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+', 'paymentId' => '[a-zA-Z0-9-]+'])
            ->setPass(['id', 'paymentId']);
        $builder->connect('/coaching-service-requests/checkPaymentStatus/:id', ['controller' => 'CoachingServiceRequests', 'action' => 'checkPaymentStatus'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+'])
            ->setPass(['id']);

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * It is NOT recommended to use fallback routes after your initial prototyping phase!
         * See https://book.cakephp.org/5/en/development/routing.html#fallbacks-method for more information
         */
        $builder->fallbacks();
    });

    // Admin Routes – for CMS management and other admin functions.
    // These routes will be accessible via URLs like /admin/cms-blocks, /admin/users, etc.
    $routes->prefix('Admin', function (RouteBuilder $builder): void {
        // Connect the base path for the admin area to the Admin dashboard
        // This will be the first page an admin user sees after login
        $builder->connect('/', ['controller' => 'Admin', 'action' => 'dashboard'], ['_name' => 'admin_dashboard']);

        // Orders management routes for handling customer purchases
        // Provides access to view, edit, and change status of all orders
        $builder->connect('/orders', ['controller' => 'Orders', 'action' => 'index']);
        $builder->connect('/orders/pending', ['controller' => 'Orders', 'action' => 'pending']);
        $builder->connect('/orders/processing', ['controller' => 'Orders', 'action' => 'processing']);
        $builder->connect('/orders/shipped', ['controller' => 'Orders', 'action' => 'shipped']);
        $builder->connect('/orders/completed', ['controller' => 'Orders', 'action' => 'completed']);

        // Content management routes for website content
        // Allows admins to update and manage site content blocks
        $builder->connect('/content-blocks', ['controller' => 'ContentBlocks', 'action' => 'index']);

        // Writing service request management
        // For handling and managing client writing and proofreading service requests
        $builder->connect('/writing-service-requests', ['controller' => 'WritingServiceRequests', 'action' => 'index']);

        // Coaching service request management  
        // For handling and managing client coaching service requests
        $builder->connect('/coaching-service-requests', ['controller' => 'CoachingServiceRequests', 'action' => 'index']);

        // AJAX endpoint for fetching new messages in chat
        $builder->connect('/writing-service-requests/fetch-messages/:id', ['controller' => 'WritingServiceRequests', 'action' => 'fetchMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+'])
            ->setPass(['id']);
        $builder->connect('/writing-service-requests/fetch-messages/:id/:lastMessageId', ['controller' => 'WritingServiceRequests', 'action' => 'fetchMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+', 'lastMessageId' => '[a-zA-Z0-9-]+'])
            ->setPass(['id', 'lastMessageId']);

        // AJAX endpoint for getting messages as HTML
        $builder->connect('/writing-service-requests/getMessages/:id', ['controller' => 'WritingServiceRequests', 'action' => 'getMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+'])
            ->setPass(['id']);

        // AJAX endpoint for fetching new messages in coaching chat
        $builder->connect('/coaching-service-requests/fetch-messages/:id', ['controller' => 'CoachingServiceRequests', 'action' => 'fetchMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+'])
            ->setPass(['id']);
        $builder->connect('/coaching-service-requests/fetch-messages/:id/:lastMessageId', ['controller' => 'CoachingServiceRequests', 'action' => 'fetchMessages'])
            ->setPatterns(['id' => '[a-zA-Z0-9-]+', 'lastMessageId' => '[a-zA-Z0-9-]+'])
            ->setPass(['id', 'lastMessageId']);

        // AJAX endpoint for getting available time slots
        $builder->connect('/writing-service-requests/get-available-time-slots', ['controller' => 'WritingServiceRequests', 'action' => 'getAvailableTimeSlots']);

        // AJAX endpoint for getting available coaching time slots
        $builder->connect('/coaching-service-requests/get-available-time-slots', ['controller' => 'CoachingServiceRequests', 'action' => 'getAvailableTimeSlots']);

        // Endpoint for sending time slots to clients
        $builder->connect('/writing-service-requests/send-time-slots/:id', ['controller' => 'WritingServiceRequests', 'action' => 'sendTimeSlots'])
            ->setPatterns(['id' => '[a-zA-Z0-9\-_]+'])
            ->setPass(['id']);

        // Test route for debugging
        $builder->connect('/writing-service-requests/test-routing/:id', ['controller' => 'WritingServiceRequests', 'action' => 'testRouting'])
            ->setPatterns(['id' => '[a-zA-Z0-9\-_]+'])
            ->setPass(['id']);

        // Endpoint for sending coaching time slots to clients
        $builder->connect('/coaching-service-requests/send-time-slots/:id', ['controller' => 'CoachingServiceRequests', 'action' => 'sendTimeSlots'])
            ->setPatterns(['id' => '[a-zA-Z0-9\-_]+'])
            ->setPass(['id']);

        // Google Calendar Auth routes
        $builder->connect('/google-auth', ['controller' => 'GoogleAuth', 'action' => 'index']);
        $builder->connect('/google-auth/callback', ['controller' => 'GoogleAuth', 'action' => 'callback']);
        $builder->connect('/google-auth/view-calendar', ['controller' => 'GoogleAuth', 'action' => 'viewCalendar']);

        // Artworks management for the art e-commerce section
        // Allows adding, editing, and removing artwork products
        $builder->connect('/artworks', ['controller' => 'Artworks', 'action' => 'index']);

        // Users management for customer accounts
        // For managing user accounts and permissions
        $builder->connect('/users', ['controller' => 'Users', 'action' => 'index']);

        // Fallback routes for admin controllers.
        // This will create standard routes for all admin controllers
        $builder->fallbacks(DashedRoute::class);
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
