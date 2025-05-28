<?php
/**
 * @var \App\View\AppView $this
 */

$this->assign('title', 'Diana Bonvini - Artist & Writer');

// Override the body class block (if used elsewhere)
$this->start('bodyClass');
echo 'relative text-white min-h-screen flex flex-col overflow-x-hidden';
$this->end();

// Override the background block with the landing background markup.
$this->start('background');
?>
<div class="fixed inset-0 -z-10">
    <?= $this->ContentBlock->image('landing-background', [
        'alt' => 'Landing Background',
        'class' => 'full-bleed',
    ]) ?>
    <div class="full-bleed gradient-overlay"></div>
</div>
<?php
$this->end();

// Override the main container classes for centered content.
$this->start('mainClass');
echo 'relative z-10 flex-grow flex items-center justify-center min-h-[calc(100vh-80px)]';
$this->end();
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
