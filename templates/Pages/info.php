<?php
/**
 * @var \App\View\AppView $this
 */

$this->assign('title', 'Writing Services');
?>

<div class="max-w-4xl mx-auto p-6">
    <?= $this->element('page_title', ['title' => 'Writing Services']) ?>

    <div class="space-y-10 text-gray-700 content-block">

        <section>
            <?= $this->ContentBlock->html('writing-service-1') ?>
        </section>

        <section>
            <?= $this->ContentBlock->html('writing-service-2') ?>
        </section>

        <section>
            <?= $this->ContentBlock->html('writing-service-3') ?>
        </section>

        <!-- Booking button -->
        <div class="text-center">
            <?= $this->Html->link(
                'Book Writing Service Now',
                ['controller' => 'WritingServiceRequests', 'action' => 'add'],
                [
                    'class' => 'inline-block bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition',
                ],
            ) ?>
        </div>
    </div>
</div>
