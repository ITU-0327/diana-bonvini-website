<?php
$this->assign('title', 'Diana Bonvini - Artist & Writer');
$this->extend('/layout/landing');
?>

<div class="text-center animate-fadeIn max-w-2xl px-4">
    <h2 class="text-6xl font-bold mb-6 text-white">Where Art Meets Literature</h2>
    <p class="text-2xl mb-8 text-white">
        Experience the intersection of visual beauty and eloquent storytelling through contemporary creations.
    </p>

    <!-- Button Container -->
    <div class="flex justify-center items-center space-x-4">
        <!-- Button #1 -->
        <?= $this->Html->link(
            'EXPLORE ART COLLECTION',
            ['controller' => 'Artworks', 'action' => 'index'],
            [
                'class' => 'inline-block whitespace-nowrap px-8 py-4 text-lg font-bold bg-white text-teal-500 border-2 border-white hover:bg-teal-400 hover:text-white hover:border-teal-400 transition-all rounded-full transform hover:scale-105',
            ]
        ) ?>

        <!-- Button #2 -->
        <?= $this->Html->link(
            'REQUEST WRITING SERVICE',
            ['controller' => 'WritingServiceRequests', 'action' => 'add'],
            [
                'class' => 'inline-block whitespace-nowrap px-8 py-4 text-lg font-bold bg-transparent text-white border-2 border-white hover:bg-teal-400 hover:text-white hover:border-teal-400 transition-all rounded-full transform hover:scale-105',
            ]
        ) ?>
    </div>
</div>
