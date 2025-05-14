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
    <?= $this->Html->css('admin-styles') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
<div class="wrapper <?= isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true' ? 'sidebar-collapsed' : '' ?>">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>Diana Bonvini</h3>
        </div>

        <div class="sidebar-menu">
            <div class="menu-group">
                <?php
                // Helper to determine if menu item is active
                $isActive = function ($controller, $action = null) {
                    $currentController = $this->request->getParam('controller');
                    $currentAction = $this->request->getParam('action');

                    if ($controller === $currentController) {
                        if ($action === null || $action === $currentAction) {
                            return 'active';
                        }
                    }

                    return '';
                };
                ?>

                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Admin', 'action' => 'dashboard']) ?>"
                   class="menu-item <?= $isActive('Admin', 'dashboard') ?>">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>

                <div class="menu-title"><span>Content Management</span></div>

                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Artworks', 'action' => 'index']) ?>"
                   class="menu-item <?= $isActive('Artworks') ?>">
                    <i class="fas fa-paint-brush"></i> <span>Artworks</span>
                </a>

                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'ContentBlocks', 'action' => 'index']) ?>"
                   class="menu-item <?= $isActive('ContentBlocks') ?>">
                    <i class="fas fa-file-alt"></i> <span>Content Blocks</span>
                </a>
            </div>

            <div class="menu-group">
                <div class="menu-title"><span>Business</span></div>

                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'index']) ?>"
                   class="menu-item <?= $isActive('Orders') ?>">
                    <i class="fas fa-shopping-cart"></i> <span>Orders</span>
                </a>

                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'index']) ?>"
                   class="menu-item <?= $isActive('WritingServiceRequests') ?>">
                    <i class="fas fa-pen"></i> <span>Writing Services</span>
                </a>
            </div>

            <div class="menu-group">
                <div class="menu-title"><span>Administration</span></div>

                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index']) ?>"
                   class="menu-item <?= $isActive('Users') ?>">
                    <i class="fas fa-users"></i> <span>Users</span>
                </a>

            </div>

            <div class="menu-group">
                <div class="menu-title"><span>Quick Links</span></div>

                <a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'landing']) ?>" class="menu-item" target="_blank">
                    <i class="fas fa-external-link-alt"></i> <span>View Website</span>
                </a>

                <a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'Users', 'action' => 'logout']) ?>" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="content">
        <!-- Top Bar - Sticky Header -->
        <div class="top-bar-container">
            <div class="top-bar">
                <div class="d-flex align-items-center">
                    <div class="sidebar-toggle mr-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </div>
                    <h4 class="page-title mb-0"><?= $this->fetch('title') ?></h4>
                </div>

                <div class="user-menu">
                    <div class="user-menu-item">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="user-menu-item position-relative">
                        <i class="fas fa-bell"></i>
                        <span class="badge badge-danger position-absolute" style="top: 0; right: 0; font-size: 0.6rem;">3</span>
                    </div>
                    <div class="user-dropdown dropdown">
                        <?php
                        $identity = $this->request->getAttribute('identity');
                        $userName = $identity ? ($identity->first_name . ' ' . $identity->last_name) : 'Admin User';
                        $userInitials = $identity ? substr($identity->first_name, 0, 1) . substr($identity->last_name, 0, 1) : 'AU';
                        ?>
                        <div class="user-avatar dropdown-toggle d-flex align-items-center justify-content-center bg-primary text-white"
                             id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= h($userInitials) ?>
                        </div>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="userDropdown">
                            <div class="dropdown-item d-flex align-items-center py-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3"
                                     style="width: 40px; height: 40px; font-weight: 600;">
                                    <?= h($userInitials) ?>
                                </div>
                                <div>
                                    <div class="font-weight-bold"><?= h($userName) ?></div>
                                    <small class="text-muted">Administrator</small>
                                </div>
                            </div>
                            <div class="dropdown-divider m-0"></div>
                            <?= $this->Html->link(
                                '<i class="fas fa-user-circle"></i> My Profile',
                                ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'profile'],
                                ['class' => 'dropdown-item', 'escape' => false]
                            ) ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-cog"></i> Settings',
                                ['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index'],
                                ['class' => 'dropdown-item', 'escape' => false]
                            ) ?>
                            <div class="dropdown-divider m-0"></div>
                            <?= $this->Html->link(
                                '<i class="fas fa-sign-out-alt"></i> Logout',
                                ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'],
                                ['class' => 'dropdown-item', 'escape' => false]
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content with proper scrolling -->
        <div class="content-wrapper">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div class="overlay" id="sidebarOverlay"></div>
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

        // Flash message auto-close
        window.setTimeout(function() {
            $(".alert-dismissible").fadeTo(500, 0).slideUp(500, function() {
                $(this).remove();
            });
        }, 5000);
    });
</script>
</body>
</html>
