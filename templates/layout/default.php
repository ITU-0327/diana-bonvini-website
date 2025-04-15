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
    <title><?= $siteTitle ?>: <?= $this->fetch('title') ?></title>
    <?= $this->Html->meta('icon') ?>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <?= $this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'); ?>
    <?= $this->Html->css('styles.css') ?>

    <!-- Block for additional head content -->
    <?= $this->fetch('headExtras') ?>

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
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
</main>

<footer class="bg-gradient-to-r from-teal-600 to-teal-800 text-white py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <h3 class="text-2xl font-bold"><?= $this->ContentBlock->text('logo') ?></h3>
            </div>
            <div class="flex space-x-4">
                <a href="<?= $this->ContentBlock->text('instagram-link') ?>" class="hover:text-gray-300 transition">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="<?= $this->ContentBlock->text('linkedin-link') ?>" class="hover:text-gray-300 transition">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
        <div class="mt-4 border-t border-teal-400 pt-4 text-center text-sm">
            <?= $this->ContentBlock->text('copyright-notice'); ?>
        </div>
    </div>
</footer>

<?= $this->fetch('scriptBottom') ?>
<?= $this->Html->script('https://unpkg.com/lucide@latest') ?>
<script>
    lucide.createIcons();
</script>
</body>
</html>
