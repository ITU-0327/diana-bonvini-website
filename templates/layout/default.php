<?php
/**
 * Default Layout for Diana Bonvini Website using Tailwind CSS
 * with view blocks for page-specific customizations.
 *
 * @var \App\View\AppView $this
 */
$siteTitle = 'Diana Bonvini Art & Writing';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $siteTitle ?>: <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon', '/favicon-16x16.ico', ['sizes' => '16x16']) ?>
    <?= $this->Html->meta('icon', '/favicon-32x32.ico', ['sizes' => '32x32']) ?>

    <!-- Tailwind CSS CDN -->
    <?= $this->Html->css('normalize.min') ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <?= $this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'); ?>
    <?= $this->Html->css('styles') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="<?= $this->fetch('bodyClass', 'bg-gray-50 min-h-screen flex flex-col') ?>">
<!-- Block for custom background content -->
<?= $this->fetch('background') ?>

<!-- Navigation -->
<?= $this->element('navbar') ?>

<!-- Main Content -->
<main class="<?= $this->fetch('mainClass', 'flex-grow container mx-auto py-10') ?>">
    <div class="flash-messages-container">
        <?= $this->Flash->render() ?>
    </div>
    <?= $this->fetch('content') ?>
</main>

<footer class="bg-gradient-to-r from-teal-600 to-teal-800 text-white py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <h3 class="text-2xl font-bold"><?= $this->ContentBlock->text('logo') ?></h3>
            </div>
            <div class="flex space-x-4">
                <?= $this->ContentBlock->url(
                    'instagram-link',
                    [
                        'text' => '<i class="fab fa-instagram"></i>',
                        'target' => '_blank',
                        'rel' => 'noopener noreferrer',
                        'class' => 'hover:text-gray-300 transition',
                        'escape' => false,
                    ],
                ) ?>
                <?= $this->ContentBlock->url(
                    'linkedin-link',
                    [
                        'text' => '<i class="fab fa-linkedin-in"></i>',
                        'target' => '_blank',
                        'rel' => 'noopener noreferrer',
                        'class' => 'hover:text-gray-300 transition',
                        'escape' => false,
                    ],
                ) ?>
            </div>
        </div>
        <div class="mt-4 border-t border-teal-400 pt-4 text-center text-sm">
            <?= $this->ContentBlock->text('copyright-notice'); ?>
        </div>
    </div>
</footer>

<?= $this->fetch('scriptBottom') ?>
<?= $this->Html->script('https://unpkg.com/lucide@latest') ?>
<?= $this->Html->script('local-time-converter', ['v' => '1']); ?>
<script>
    lucide.createIcons();
    
    // Flash Message Functionality
    function dismissFlashMessage(element) {
        if (element) {
            element.classList.add('hidden');
            setTimeout(() => {
                element.remove();
            }, 300);
        }
    }
    
    // Auto-dismiss flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const autoMessages = document.querySelectorAll('.message.auto-dismiss');
        autoMessages.forEach(function(message) {
            setTimeout(() => {
                if (message && !message.classList.contains('hidden')) {
                    dismissFlashMessage(message);
                }
            }, 5000);
        });
    });
    
    // Keyboard accessibility - dismiss with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const visibleMessages = document.querySelectorAll('.message:not(.hidden)');
            visibleMessages.forEach(function(message) {
                dismissFlashMessage(message);
            });
        }
    });
    
    // Automatically convert all server timestamps to local time
    if (window.LocalTimeConverter && typeof window.localTimeConverter === 'object') {
      // localTimeConverter instance from script
    }
</script>
</body>
</html>
