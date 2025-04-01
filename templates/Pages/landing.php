<?php
$this->assign('title', 'Diana Bonvini - Artist & Writer');
$this->extend('/layout/landing');
?>

<div class="text-center animate-fadeIn max-w-2xl px-4">
    <h2 class="text-5xl font-serif mb-6">Where Art Meets Literature</h2>
    <p class="text-xl mb-12">
        Experience the intersection of visual beauty and eloquent storytelling through contemporary creations.
    </p>
    <div class="space-x-8">
        <?= $this->Html->link(
            'Explore Art Collection →',
            ['controller' => 'Artworks', 'action' => 'index'],
            [
                'class' => 'px-10 py-4 bg-transparent border-2 border-amber-400 text-amber-400 hover:bg-amber-400 hover:text-black transition-all rounded-full',
            ]
        ) ?>
        <?= $this->Html->link(
            'Request Writing Service →',
            ['controller' => 'WritingServiceRequests', 'action' => 'add'],
            [
                'class' => 'px-10 py-4 bg-amber-400 text-black hover:bg-amber-500 transform hover:scale-105 transition-all rounded-full',
            ]
        ) ?>
    </div>
</div>
