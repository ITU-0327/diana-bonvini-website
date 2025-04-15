<?php
/**
 * @var \App\View\AppView $this
 */

$this->assign('title', 'Diana Bonvini - Artist & Writer');
$this->extend('/layout/landing');
?>

<div class="text-center animate-fadeIn max-w-2xl px-4">
    <h2 class="text-3xl sm:text-4xl md:text-6xl font-bold mb-6 text-white">
        <?= $this->ContentBlock->text('landing-title') ?>
    </h2>
    <p class="text-2xl mb-8 text-white">
        <?= $this->ContentBlock->text('landing-subtitle') ?>
    </p>

    <!-- Button Container -->
    <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
        <!-- Button #1 -->
        <?= $this->Html->link(
            $this->ContentBlock->text('landing-button-1'),
            ['controller' => 'Artworks', 'action' => 'index'],
            [
                'class' => 'inline-block whitespace-nowrap px-6 md:px-8 py-3 md:py-4 text-base md:text-lg font-bold bg-white text-teal-500 border-2 border-white hover:bg-teal-400 hover:text-white hover:border-teal-400 transition-all rounded-full transform hover:scale-105',
            ],
        ) ?>

        <!-- Button #2 -->
        <?= $this->Html->link(
            $this->ContentBlock->text('landing-button-2'),
            ['controller' => 'WritingServiceRequests', 'action' => 'add'],
            [
                'class' => 'inline-block whitespace-nowrap px-6 md:px-8 py-3 md:py-4 text-base md:text-lg font-bold bg-transparent text-white border-2 border-white hover:bg-teal-400 hover:text-white hover:border-teal-400 transition-all rounded-full transform hover:scale-105',
            ],
        ) ?>
    </div>
</div>
