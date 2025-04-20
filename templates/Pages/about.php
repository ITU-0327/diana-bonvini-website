<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'About Me');
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'About Me']) ?>

    <!-- Content Card -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="prose max-w-none cms-content">
            <?= $this->ContentBlock->html('about-content') ?>
        </div>
    </div>
</div>
