<?php

use Cake\Mailer\Transport\SmtpTransport;/*
 * Local configuration file to provide any overrides to your app.php configuration.
 * Copy and save this file as app_local.php and make changes as required.
 * Note: It is not recommended to commit files with credentials such as app_local.php
 * into source code version control.
 */

return [
    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => filter_var(env('DEBUG', true), FILTER_VALIDATE_BOOLEAN),

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', 'e072e466f11fad159f3a6c477293ad37b446c21d0687f41b2fab6cd172fd6bab'),
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * See app.php for more configuration options.
     */
    'Datasources' => [
        'default' => [
            'host' => 'diana-bonvini-development-database.cv8yk4a280vk.ap-southeast-2.rds.amazonaws.com',
            /*
             * CakePHP will use the default DB port based on the driver selected
             * MySQL on MAMP uses port 8889, MAMP users will want to uncomment
             * the following line and set the port accordingly
             */
            //'port' => 'non_standard_port_number',

            'username' => 'dom',
            'password' => 'uiF6O/vse7QDXGTQ',

            'database' => 'diana_bonvini_dev',
            /*
             * If not using the default 'public' schema with the PostgreSQL driver
             * set it here.
             */
            //'schema' => 'myapp',

            /*
             * You can use a DSN string to set the entire configuration
             */
            'url' => env('DATABASE_URL', null),
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'host' => 'localhost',
            //'port' => 'non_standard_port_number',
            'username' => 'superuser',
            'password' => 'superuserpassword',
            'database' => 'NEW',
            //'schema' => 'myapp',
            'url' => env('DATABASE_TEST_URL', null),
        ],
    ],

    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        'default' => [
            'className' => 'Smtp',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => env('SMTP_USERNAME', 'dqii0004@student.monash.edu'),
            'password' => env('SMTP_PASSWORD', 'lnoq baxv kunn klif'),
            'tls' => true,
            'timeout' => 30,
        ],
    ],
    'Stripe' => [
        'secret' => env('STRIPE_SECRET_API_KEY', 'sk_test_51RE3PlP3fcJoUdt0AZlBUJib4yHfBCtgTonVcbz02XvGK246BwbKB5gO6BynODmFRpPYaV4sQHsVnpXI4hbq1zTH00gBACrr5I'),
        'publishable' => env('STRIPE_PUBLISHABLE_API_KEY', 'pk_test_51RE3PlP3fcJoUdt0cskJVWaAY4ByYcBAmXSPMj7LW2axatUSLKB24CSvivb00z2YmFKgoXxd9oDRWphePPHF6Njo00GEwkGncL'),
    ],
    'GoogleMaps' => [
        'key' => env('GOOGLE_MAPS_API_KEY', 'AIzaSyBI9BfDR6Xb4CqyA-nF2uLkwcMsrQSYAJA'),
    ],
];
