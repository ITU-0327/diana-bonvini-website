<?php
/**
 * Admin Layout template
 *
 * @var \App\View\AppView $this
 */
$siteTitle = 'Diana Bonvini Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $siteTitle ?> | <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon', '/favicon-16x16.ico', ['sizes' => '16x16']) ?>
    <?= $this->Html->meta('icon', '/favicon-32x32.ico', ['sizes' => '32x32']) ?>

    <!-- Core CSS -->
    <?= $this->Html->css('normalize.min') ?>
    <?= $this->Html->css('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css'); ?>
    <?= $this->Html->css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'); ?>
    <?= $this->Html->css('admin_styles') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
<div class="wrapper <?= isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true' ? 'sidebar-collapsed' : '' ?>">
    <!-- Sidebar -->
    <?= $this->element('admin/sidebar') ?>

    <!-- Content -->
    <div class="content">
        <!-- Top Bar - Sticky Header -->
        <?= $this->element('admin/top_bar') ?>

        <!-- Main Content with proper scrolling -->
        <div class="content-wrapper">
            <div class="flash-messages-container">
                <?= $this->Flash->render() ?>
            </div>
            <?= $this->fetch('content') ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<?= $this->fetch('scriptBottom') ?>
<?= $this->Html->script('https://code.jquery.com/jquery-3.6.0.min.js') ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js') ?>
<?= $this->Html->script('https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js') ?>

<script>
    $(document).ready(function() {
        // Sidebar toggle functionality
        $('#sidebarToggle').on('click', function() {
            $('.wrapper').toggleClass('sidebar-collapsed');
            $('.sidebar').toggleClass('expanded');

            // Store preference in cookie
            if ($('.wrapper').hasClass('sidebar-collapsed')) {
                document.cookie = 'sidebar_collapsed=true; path=/; max-age=2592000'; // 30 days
            } else {
                document.cookie = 'sidebar_collapsed=false; path=/; max-age=2592000';
            }
        });

        // Close sidebar on overlay click (mobile)
        $('#sidebarOverlay').on('click', function() {
            $('.sidebar').removeClass('expanded');
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Custom file input label
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });

        // Flash Message Functionality - Enhanced version
        window.dismissFlashMessage = function(element) {
            if (element) {
                element.classList.add('hidden');
                setTimeout(() => {
                    element.remove();
                }, 300);
            }
        };
        
        // Auto-dismiss flash messages after 5 seconds
        const autoMessages = document.querySelectorAll('.message.auto-dismiss');
        autoMessages.forEach(function(message) {
            setTimeout(() => {
                if (message && !message.classList.contains('hidden')) {
                    dismissFlashMessage(message);
                }
            }, 5000);
        });
        
        // Keyboard accessibility - dismiss with Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                const visibleMessages = document.querySelectorAll('.message:not(.hidden)');
                visibleMessages.forEach(function(message) {
                    dismissFlashMessage(message);
                });
            }
        });
    });
</script>
</body>
</html>
