<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ContentBlocksFixture
 */
class ContentBlocksFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'content_block_id' => '17c8f794-d61c-4c2f-9b39-aff162eaa839',
                'parent' => 'landing',
                'slug' => 'landing-button-2',
                'label' => 'Landing Page Button (right)',
                'description' => 'Text for the button.',
                'type' => 'text',
                'value' => 'REQUEST WRITING SERVICE',
                'previous_value' => null,
                'created_at' => '2025-04-15 18:37:42',
                'updated_at' => '2025-04-15 18:49:45',
            ],
            [
                'content_block_id' => '35fa8c56-f270-46f9-b516-c8a441eaccc9',
                'parent' => '',
                'slug' => 'email',
                'label' => 'Email',
                'description' => 'The official contact email.',
                'type' => 'url',
                'value' => 'dbdesignsaustralia@gmail.com',
                'previous_value' => null,
                'created_at' => '2025-04-15 18:54:07',
                'updated_at' => '2025-04-15 18:54:32',
            ],
            [
                'content_block_id' => '44d36c7f-8430-4728-84cb-6c800aadfe9d',
                'parent' => 'footer',
                'slug' => 'copyright-notice',
                'label' => 'Copyright notice',
                'description' => 'The copyright notice in the footer',
                'type' => 'text',
                'value' => '&copy; {{currentYear}} Diana Bonvini. All rights reserved.',
                'previous_value' => null,
                'created_at' => '2025-04-15 18:59:19',
                'updated_at' => '2025-04-15 19:03:22',
            ],
            [
                'content_block_id' => '598fe280-3863-4029-8c74-5d9a300437e3',
                'parent' => '',
                'slug' => 'linkedin-link',
                'label' => 'LinkedIn Link',
                'description' => 'The link for LinkedIn',
                'type' => 'url',
                'value' => 'https://www.linkedin.com/in/dianabonvini/',
                'previous_value' => null,
                'created_at' => '2025-04-15 19:09:22',
                'updated_at' => '2025-04-15 19:09:22',
            ],
            [
                'content_block_id' => 'b5c4a7a9-6ab9-4e8f-874c-a67accae6851',
                'parent' => 'landing',
                'slug' => 'landing-background',
                'label' => 'Landing Background',
                'description' => 'The background in the landing page',
                'type' => 'image',
                'value' => 'Landingpage/Landing-Page-Db.jpg',
                'previous_value' => null,
                'created_at' => '2025-04-15 20:54:59',
                'updated_at' => '2025-04-15 20:54:59',
            ],
            [
                'content_block_id' => 'b83ec092-fa2f-44a9-946e-e9ddc888c7ab',
                'parent' => 'landing',
                'slug' => 'landing-title',
                'label' => 'Landing Page Title',
                'description' => 'Main title of the landing page.',
                'type' => 'text',
                'value' => 'Where Art Meets Literature',
                'previous_value' => null,
                'created_at' => '2025-04-15 18:29:05',
                'updated_at' => '2025-04-15 18:30:16',
            ],
            [
                'content_block_id' => 'b8d37fda-0f48-4c1f-b8a4-3c7685cde158',
                'parent' => '',
                'slug' => 'logo',
                'label' => 'Logo (text)',
                'description' => 'The logo in text.',
                'type' => 'text',
                'value' => 'diana bonvini',
                'previous_value' => null,
                'created_at' => '2025-04-15 18:55:27',
                'updated_at' => '2025-04-15 18:55:27',
            ],
            [
                'content_block_id' => 'c7400d11-05f3-4d7f-9df9-e8a3ebf2ba0b',
                'parent' => 'landing',
                'slug' => 'landing-subtitle',
                'label' => 'Landing Page Subtitle',
                'description' => 'Subtitle for the landing page.',
                'type' => 'text',
                'value' => 'Experience the intersection of visual beauty and eloquent storytelling through contemporary creations.',
                'previous_value' => null,
                'created_at' => '2025-04-15 18:34:35',
                'updated_at' => '2025-04-15 18:34:35',
            ],
            [
                'content_block_id' => 'd0cf8ba6-edce-40ad-9d53-f4f240e06a30',
                'parent' => '',
                'slug' => 'instagram-link',
                'label' => 'Instagram Link',
                'description' => 'The link for Instagram',
                'type' => 'url',
                'value' => 'https://www.instagram.com/',
                'previous_value' => null,
                'created_at' => '2025-04-15 19:08:22',
                'updated_at' => '2025-04-15 19:08:22',
            ],
            [
                'content_block_id' => 'd15eb34c-bf99-4f8f-b649-73af5e288941',
                'parent' => '',
                'slug' => 'phone-number',
                'label' => 'Phone Number',
                'description' => 'Phone Number for contact',
                'type' => 'text',
                'value' => '+61 400 123 456',
                'previous_value' => null,
                'created_at' => '2025-04-15 20:52:36',
                'updated_at' => '2025-04-15 20:52:36',
            ],
            [
                'content_block_id' => 'fdb046d0-1941-4097-b38e-b5e684b119f3',
                'parent' => 'landing',
                'slug' => 'landing-button-1',
                'label' => 'Landing Page Button (left)',
                'description' => 'Text on the button.',
                'type' => 'text',
                'value' => 'EXPLORE ART COLLECTION',
                'previous_value' => null,
                'created_at' => '2025-04-15 18:36:42',
                'updated_at' => '2025-04-15 18:49:36',
            ],
        ];
        parent::init();
    }
}
