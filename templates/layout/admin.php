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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        Admin: <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('normalize.min') ?>
    <?= $this->Html->css('bootstrap.min') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 80px;
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            color: #fff;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            transition: width 0.3s;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: #fff;
        }

        .sidebar-menu i {
            width: 20px;
            margin-right: 1rem;
            font-size: 1rem;
            text-align: center;
        }

        .sidebar-menu span {
            white-space: nowrap;
            overflow: hidden;
        }

        /* When sidebar is collapsed */
        .sidebar.collapsed .sidebar-header h3 {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu span {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu a {
            justify-content: center;
            padding: 0.75rem 0;
        }

        .sidebar.collapsed .sidebar-menu i {
            margin-right: 0;
            font-size: 1.25rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s;
            width: calc(100% - var(--sidebar-width));
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* Top Nav */
        .top-nav {
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .toggle-menu {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--dark-color);
        }

        .top-nav-content {
            padding: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }

            .sidebar .sidebar-header h3,
            .sidebar .sidebar-menu span {
                display: none;
            }

            .sidebar .sidebar-menu a {
                justify-content: center;
                padding: 0.75rem 0;
            }

            .sidebar .sidebar-menu i {
                margin-right: 0;
                font-size: 1.25rem;
            }

            .main-content {
                margin-left: var(--sidebar-collapsed-width);
                width: calc(100% - var(--sidebar-collapsed-width));
            }
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: var(--success-color) !important;
        }

        .text-info {
            color: var(--info-color) !important;
        }

        .text-warning {
            color: var(--warning-color) !important;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        .text-gray-300 {
            color: #dddfeb !important;
        }

        .text-gray-800 {
            color: #5a5c69 !important;
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <div class="sidebar-menu">
            <?= $this->Html->link(
                '<i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>',
                ['prefix' => 'Admin', 'controller' => 'Admin', 'action' => 'dashboard'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Admin' ? 'active' : ''],
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-paint-brush"></i> <span>Artworks</span>',
                ['prefix' => 'Admin', 'controller' => 'Artworks', 'action' => 'index'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Artworks' ? 'active' : ''],
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-shopping-cart"></i> <span>Orders</span>',
                ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'index'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Orders' ? 'active' : ''],
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-pen"></i> <span>Writing Services</span>',
                ['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'index'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'WritingServiceRequests' ? 'active' : ''],
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-users"></i> <span>Users</span>',
                ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Users' ? 'active' : ''],
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-file-alt"></i> <span>Content Blocks</span>',
                ['prefix' => 'Admin', 'controller' => 'ContentBlocks', 'action' => 'index'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'ContentBlocks' ? 'active' : ''],
            ) ?>

            <div class="dropdown-divider"></div>

            <?= $this->Html->link(
                '<i class="fas fa-external-link-alt"></i> <span>View Website</span>',
                ['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'landing'],
                ['escape' => false, 'target' => '_blank'],
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-sign-out-alt"></i> <span>Logout</span>',
                ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'],
                ['escape' => false],
            ) ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-nav">
            <div class="toggle-menu">
                <i class="fas fa-bars"></i>
            </div>
            <div class="d-flex align-items-center">
                <span class="mr-2">Welcome, <?= $this->request->getSession()->read('Auth.username') ?? 'Admin' ?></span>
                <?= $this->Html->link(
                    'Logout',
                    ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'],
                    ['class' => 'btn btn-sm btn-outline-danger'],
                ) ?>
            </div>
        </div>

        <div class="top-nav-content">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800"><?= $this->fetch('title') ?></h1>
            </div>

            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </div>
</div>

<?= $this->Html->script('jquery-3.6.0.min') ?>
<?= $this->Html->script('bootstrap.bundle.min') ?>

<script>
    $(document).ready(function() {
            // Toggle sidebar
            $('.toggle-menu').on('click', function() {
                $('.sidebar').toggleClass('collapsed');
                $('.main-content').toggleClass('expanded');
            });

            // Responsive handling
            function checkScreenSize() {
                if (window.innerWidth < 992) {
                    $('.sidebar').addClass('collapsed');
                    $('.main-content').addClass('expanded');
                }
            }

            // Check on load
            checkScreenSize();

            // Check on resize
            $(window).resize(function() {
                checkScreenSize();
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Initialize popovers
            $('[data-toggle="popover"]').popover();

            // Auto dismiss flash messages
            setTimeout(function() {
                $('.alert-dismissible').fadeOut(500);
            }, 5000);
        });
</script>
</body>
</html>
