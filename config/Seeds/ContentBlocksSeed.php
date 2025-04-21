<?php
declare(strict_types=1);

use Migrations\BaseSeed;

/**
 * ContentBlocks seed.
 */
class ContentBlocksSeed extends BaseSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/migrations/4/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $data = [
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
                'updated_at' => '2025-04-17 14:05:57',
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
                'updated_at' => '2025-04-15 22:21:40',
            ],
            [
                'content_block_id' => '3b97ca23-2e75-438f-9ff3-a782ec5b2c6f',
                'parent' => '',
                'slug' => 'favicon',
                'label' => 'Favicon',
                'description' => 'The icon for the website',
                'type' => 'image',
                'value' => '/favicon.ico',
                'previous_value' => null,
                'created_at' => '2025-04-16 00:17:25',
                'updated_at' => '2025-04-16 00:19:28',
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
                'content_block_id' => '47b2f703-fce6-4404-b161-86087393fa47',
                'parent' => 'contact',
                'slug' => 'contact-page-intro',
                'label' => 'Contact Page Intro',
                'description' => 'This is the intro in the contact page.',
                'type' => 'text',
                'value' => 'If you have any questions about writing services, artwork, or custom commissions, feel free to reach out.',
                'previous_value' => null,
                'created_at' => '2025-04-17 18:36:41',
                'updated_at' => '2025-04-17 18:36:41',
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
                'updated_at' => '2025-04-15 22:21:40',
            ],
            [
                'content_block_id' => '752a6ff2-42ed-45aa-968f-f984ecbf5288',
                'parent' => 'contact',
                'slug' => 'contact‑page',
                'label' => 'Contact Page',
                'description' => 'This is the structure of the whole contact page',
                'type' => 'html',
                'value' => '<p>{{contact-page-intro}}</p><h2><strong>Contact Info</strong></h2><ul><li><strong>Email:</strong> {{email}}</li><li><strong>Phone:</strong> {{phone-number}}</li></ul><h2><strong>Follow Me</strong></h2>',
                'previous_value' => null,
                'created_at' => '2025-04-17 18:12:15',
                'updated_at' => '2025-04-17 19:07:21',
            ],
            [
                'content_block_id' => '8f2153fe-6389-48f0-9ee3-aeddbf223b67',
                'parent' => '',
                'slug' => 'watermark-text',
                'label' => 'Watermark Text',
                'description' => 'This is the text that will be display for the watermark.',
                'type' => 'text',
                'value' => 'Diana Bonvini',
                'previous_value' => null,
                'created_at' => '2025-04-18 22:10:04',
                'updated_at' => '2025-04-18 22:10:04',
            ],
            [
                'content_block_id' => '8f21ae0c-7732-4d39-9b5c-ef2af8c964b0',
                'parent' => 'about',
                'slug' => 'about-content',
                'label' => 'About Page Content',
                'description' => 'About page content',
                'type' => 'html',
                'value' => 'I am passionate about researching and writing important ideas and stories that need to be told. With over two decades of experience in writing and editing for media, government and not for profit organisations, I help unique stories and truths come to life. I support my freelance writing by undertaking roles in office and operational administration where I can contribute my experience and skills to high performing teams.<br><br>As an office manager, I value systems and processes that improve productivity and sustainability in the workplace. I enjoy taking the lead on office improvement projects and executing on the team\'s ideas to streamline operations, particularly during times of change. I am renowned for my meticulous attention to detail, reliability, and consistency in delivering solutions and results that exceed my team\'s expectations.<br><br>With my versatile work style, and years of experience across a range of industries, I am role ready from day one to add value and contribute positively to the culture in any organisation.',
                'previous_value' => null,
                'created_at' => '2025-04-17 17:40:41',
                'updated_at' => '2025-04-18 16:06:58',
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
                'updated_at' => '2025-04-16 01:04:09',
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
                'updated_at' => '2025-04-17 16:26:11',
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
                'updated_at' => '2025-04-17 15:28:08',
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
                'updated_at' => '2025-04-15 22:17:08',
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

        $this->table('content_blocks')
            ->insert($data)
            ->save();
    }
}
