<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Contact Me');
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Contact Me']) ?>

    <!-- Contact Card -->
    <div class="bg-white shadow rounded-lg p-6">
        <p class="text-gray-700 mb-6">
            If you have any questions about writing services, artwork, or custom commissions, feel free to reach out.
        </p>

        <!-- Contact Info -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Contact Info</h2>
            <ul class="space-y-1 text-gray-700">
                <li><strong>Email:</strong> <?= $this->ContentBlock->url('email') ?></li>
                <li><strong>Phone:</strong> <?= $this->ContentBlock->text('phone-number') ?></li>
            </ul>
        </div>

        <!-- Social Links -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Follow Me</h2>
            <div class="flex gap-4 items-center text-gray-600">
                <?= $this->ContentBlock->url(
                    'instagram-link',
                    [
                        'text' => '<i data-lucide="instagram" class="w-5 h-5"></i>
                                    <span class="sr-only">Instagram</span>',
                        'target' => '_blank',
                        'rel' => 'noopener noreferrer',
                        'class' => 'flex items-center gap-2 hover:text-pink-500 transition',
                        'escape' => false,
                    ],
                ) ?>
                <?= $this->ContentBlock->url(
                    'instagram-link',
                    [
                        'text' => '<i data-lucide="linkedin" class="w-5 h-5"></i>
                                    <span class="sr-only">LinkedIn</span>',
                        'target' => '_blank',
                        'rel' => 'noopener noreferrer',
                        'class' => 'flex items-center gap-2 hover:text-blue-700 transition',
                        'escape' => false,
                    ],
                ) ?>
            </div>
        </div>
    </div>
</div>
