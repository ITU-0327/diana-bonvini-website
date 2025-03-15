<?php
/**
 * Default Layout for Diana Bonvini Website using Tailwind CSS
 * with a Profile Dropdown Card similar to your provided screenshot.
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
    <!-- Tailwind CSS CDN for rapid development -->
    <script src="https://cdn.tailwindcss.com"></script>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="bg-gray-50">
<!-- Navigation -->
<?= $this->element('navbar') ?>

<!-- Main Content -->
<main class="container mx-auto py-10">
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
</main>

<!-- Footer -->
<footer class="bg-gray-800 text-gray-300 py-4">
    <div class="container mx-auto text-center">
        &copy; <?= date('Y') ?> Diana Bonvini. All rights reserved.
    </div>
</footer>

<?= $this->fetch('scriptBottom') ?>
</body>
</html>
