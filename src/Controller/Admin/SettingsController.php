<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController as BaseAdminController;
use Cake\Http\Response;

/**
 * Settings Controller (Admin prefix)
 *
 * Manages application settings from an administrative perspective.
 */
class SettingsController extends BaseAdminController
{
    /**
     * Index method - Settings dashboard
     *
     * @return void
     */
    public function index(): void
    {
        $this->set('title', 'Site Settings');

        // Example settings - In a real application, these would be fetched from the database
        $settings = [
            'site' => [
                'site_name' => 'Diana Bonvini Art',
                'site_tagline' => 'Unique Artistic Creations',
                'contact_email' => 'contact@dianabonviniart.com',
                'phone' => '+1234567890',
                'address' => '123 Art Street, Creative City',
            ],
            'business' => [
                'tax_rate' => 10.00,
                'shipping_fee' => 15.00,
                'currency' => 'AUD',
                'timezone' => 'Australia/Melbourne',
            ],
            'social' => [
                'facebook' => 'https://facebook.com/dianabonviniart',
                'instagram' => 'https://instagram.com/dianabonviniart',
                'twitter' => 'https://twitter.com/dianabonviniart',
                'linkedin' => 'https://linkedin.com/in/dianabonviniart',
            ],
            'notifications' => [
                'new_order' => true,
                'low_stock' => true,
                'new_user' => true,
                'writing_request' => true,
            ],
        ];

        $this->set(compact('settings'));
    }

    /**
     * Update settings
     *
     * @return \Cake\Http\Response|null
     */
    public function update(): ?Response
    {
        $this->request->allowMethod(['post', 'put']);

        // In a real application, you would validate and save the settings to a database or config file
        $this->Flash->success('Settings have been saved successfully.');

        return $this->redirect(['action' => 'index']);
    }
}
