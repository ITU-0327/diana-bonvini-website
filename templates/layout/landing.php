<?php
/**
 * Landing Layout for Diana Bonvini Website
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

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <?= $this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css'); ?>
    <?= $this->Html->css('styles.css') ?>

    <style>
        .gradient-overlay {
            background: linear-gradient(45deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
        }

        .full-bleed {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
    </style>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="relative text-white min-h-screen flex flex-col overflow-x-hidden">

<!-- Background -->
<div class="fixed inset-0 -z-10">
    <img src="<?= $this->Url->assetUrl('img/Landingpage/Landing-Page-Db.jpg') ?>"
         alt="Landing Background"
         class="full-bleed">
    <div class="full-bleed gradient-overlay"></div>
</div>


<!-- Main content -->
<main class="relative z-10 flex-grow flex items-center justify-center min-h-[calc(100vh-80px)]">
    <?= $this->fetch('content') ?>
</main>

<?= $this->fetch('scriptBottom') ?>
<?= $this->Html->script('https://unpkg.com/lucide@latest') ?>
<script>lucide.createIcons();</script>
</body>
</html>
