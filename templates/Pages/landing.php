<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Diana Bonvini - Artist & Writer');
?>

<style>
    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .gradient-overlay {
        background: linear-gradient(45deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
    }

    .full-bleed {
        position: fixed;
        top: 64px;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: calc(100vh - 64px);
        object-fit: cover;
        object-position: center;
    }
</style>

<div class="relative h-screen w-screen overflow-hidden">
    <!-- Full-bleed Background -->
    <div class="full-bleed">
        <img src="<?= $this->Url->assetUrl('img/Landingpage/Landing-Page-Db.jpg') ?>"
             alt="Diana Bonvini Artistic Background"
             class="full-bleed transform transition-transform duration-1000 ease-out">
    </div>

    <!-- Gradient Overlay -->
    <div class="full-bleed gradient-overlay"></div>

    <!-- Centered Main Content -->
    <div class="relative z-10 flex items-center justify-center" style="height: calc(100vh - 64px);">
        <div class="text-center text-white animate-fadeIn">
            <h2 class="text-5xl font-serif mb-6">Where Art Meets Literature</h2>
            <p class="text-xl mb-12 max-w-2xl mx-auto">
                Experience the intersection of visual beauty and eloquent storytelling through contemporary creations.
            </p>
            <div class="space-x-8">
                <?= $this->Html->link(
                    'Explore Art Collection →',
                    ['controller' => 'Artworks', 'action' => 'index'],
                    [
                        'class' => 'px-10 py-4 bg-transparent border-2 border-amber-400 text-amber-400 hover:bg-amber-400 hover:text-black transition-all rounded-full',
                    ],
                ) ?>
                <?= $this->Html->link(
                    'Request Writing Service →',
                    ['controller' => 'WritingServiceRequests', 'action' => 'add'],
                    [
                        'class' => 'px-10 py-4 bg-amber-400 text-black hover:bg-amber-500 transform hover:scale-105 transition-all rounded-full',
                    ],
                ) ?>
            </div>
        </div>
    </div>
</div>
