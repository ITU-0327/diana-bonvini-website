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
        <?= $this->Html->css('styles.css') ?>
        <?= $this->fetch('meta') ?>
        <?= $this->fetch('css') ?>
        <?= $this->fetch('script') ?>
    </head>
    <!-- Using flex-col and min-h-screen so that the main content grows -->
    <body class="bg-gray-50 min-h-screen flex flex-col">
        <!-- Navigation -->
        <?= $this->element('navbar') ?>

        <!-- Main Content set to flex-grow to push the footer down -->
        <main class="flex-grow container mx-auto py-10">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </main>

        <!-- Footer will always stick to the bottom -->
        <footer class="bg-gray-800 text-gray-300 py-4">
            <div class="container mx-auto text-center">
                &copy; <?= date('Y') ?> Diana Bonvini. All rights reserved.
            </div>
        </footer>

        <?= $this->fetch('scriptBottom') ?>
    </body>
</html>
