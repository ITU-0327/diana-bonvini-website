<?php
/**
 * Admin Layout template
 *
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        Diana Bonvini Admin | <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <!-- Core CSS -->
    <?= $this->Html->css('normalize.min') ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?= $this->Html->css('admin-style') ?>

    <style>
        :root {
            --primary-color: #2A9D8F;
            --secondary-color: #E76F51;
            --success-color: #2ecc71;
            --info-color: #3498db;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --text-color: #333;
            --bg-color: #f8f9fc;
            --sidebar-width: 260px;
            --topbar-height: 70px;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        /* Layout */
        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #2A9D8F 0%, #207268 100%);
            color: #fff;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: width 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .menu-group {
            margin-bottom: 15px;
        }

        .menu-title {
            padding: 5px 20px;
            font-size: 12px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
            border-left-color: rgba(255, 255, 255, 0.5);
        }

        .menu-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-left-color: #fff;
            font-weight: 600;
        }

        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* Main Content */
        .content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 0;
            background-color: #F7F9FC;
            min-height: 100vh;
            transition: margin-left 0.3s, width 0.3s;
        }

        /* Header */
        .top-bar-container {
            position: sticky;
            top: 0;
            z-index: 99;
            background-color: #FFFFFF;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            width: 100%;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 25px;
            height: var(--topbar-height);
        }

        .sidebar-toggle {
            font-size: 18px;
            color: #555;
            cursor: pointer;
        }

        .page-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .user-menu {
            display: flex;
            align-items: center;
        }

        .user-menu-item {
            margin-left: 15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fc;
            color: #555;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .user-menu-item:hover {
            background-color: #eef1f6;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 15px;
            cursor: pointer;
            object-fit: cover;
        }

        .user-dropdown {
            position: relative;
        }

        .dropdown-menu {
            min-width: 200px;
            border: none;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 0;
            overflow: hidden;
        }

        .dropdown-item {
            padding: 12px 15px;
            font-size: 14px;
            color: var(--text-color);
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fc;
        }

        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: var(--primary-color);
        }

        /* Content wrapper */
        .content-wrapper {
            padding: 25px;
        }

        /* Responsive Sidebar */
        .sidebar-collapsed .sidebar {
            width: 70px;
        }

        .sidebar-collapsed .sidebar .sidebar-header h3,
        .sidebar-collapsed .sidebar .menu-item span,
        .sidebar-collapsed .sidebar .menu-title {
            display: none;
        }

        .sidebar-collapsed .sidebar .menu-item {
            justify-content: center;
            padding: 15px 0;
        }

        .sidebar-collapsed .sidebar .menu-item i {
            margin-right: 0;
            font-size: 18px;
        }

        .sidebar-collapsed .content {
            margin-left: 70px;
            width: calc(100% - 70px);
        }

        /* Custom Styles for Components */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 25px;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
        }

        .card-header h6 {
            font-weight: 700;
            color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: #218a7e;
            border-color: #1e7d71;
        }

        .badge-primary {
            background-color: var(--primary-color);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .pagination .page-link {
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar .sidebar-header h3,
            .sidebar .menu-item span,
            .sidebar .menu-title {
                display: none;
            }

            .sidebar .menu-item {
                justify-content: center;
                padding: 15px 0;
            }

            .sidebar .menu-item i {
                margin-right: 0;
                font-size: 18px;
            }

            .content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }

            .sidebar.expanded {
                width: var(--sidebar-width);
                z-index: 1050;
            }

            .sidebar.expanded .sidebar-header h3,
            .sidebar.expanded .menu-item span,
            .sidebar.expanded .menu-title {
                display: block;
            }

            .sidebar.expanded .menu-item {
                justify-content: flex-start;
                padding: 12px 20px;
            }

            .sidebar.expanded .menu-item i {
                margin-right: 10px;
                font-size: 16px;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1040;
            }

            .sidebar.expanded + .overlay {
                display: block;
            }
        }
    </style>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
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
                $isActive = function($controller, $action = null) {
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
                        $userInitials = $identity ? (substr($identity->first_name, 0, 1) . substr($identity->last_name, 0, 1)) : 'AU';
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
                            <a class="dropdown-item" href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'profile']) ?>">
                                <i class="fas fa-user-circle"></i> My Profile
                            </a>
                            <a class="dropdown-item" href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index']) ?>">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <div class="dropdown-divider m-0"></div>
                            <a class="dropdown-item" href="<?= $this->Url->build(['prefix' => false, 'controller' => 'Users', 'action' => 'logout']) ?>">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

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

<?= $this->fetch('script') ?>
</body>
</html>
