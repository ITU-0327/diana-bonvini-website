<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Contact Me');
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Contact Me']) ?>

    <div class="bg-white shadow rounded-lg p-6 cms-content">
        <?= $this->ContentBlock->html('contact‑page') ?>
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
                'linkedin-link',
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
