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
        Diana Bonvini | <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('normalize.min') ?>
    <?= $this->Html->css('bootstrap.min') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #2A9D8F;
            --text-color: #333;
            --bg-color: #f5f8fa;
            --sidebar-width: 260px;
            --topbar-height: 70px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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
            background-color: #FFFFFF; /* White sidebar */
            color: var(--text-color);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .menu-group {
            margin-bottom: 15px;
        }

        .menu-title {
            padding: 5px 15px;
            font-size: 12px;
            text-transform: uppercase;
            color: #6c757d;
        }

        .menu-item {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            color: var(--text-color);
            text-decoration: none;
            transition: background-color 0.2s;
            border-radius: 4px;
            margin: 2px 8px;
        }

        .menu-item:hover {
            background-color: rgba(42, 157, 143, 0.05);
            color: var(--primary-color);
            text-decoration: none;
        }

        .menu-item.active {
            background-color: var(--primary-color);
            color: white;
        }

        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 0;
            background-color: #F7F9FC; /* Light tint for content area */
            min-height: 100vh;
        }

        /* Header */
        .top-bar-container {
            position: sticky;
            top: 0;
            z-index: 99;
            background-color: #FFFFFF;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            width: 100%;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            height: var(--topbar-height);
        }

        .search-form {
            position: relative;
            width: 300px;
        }

        .search-form input {
            border-radius: 20px;
            padding-left: 35px;
            background-color: #f1f3f5;
            border: 1px solid #e0e0e0;
        }

        .search-form i {
            position: absolute;
            left: 12px;
            top: 10px;
            color: #aaa;
        }

        .user-menu {
            display: flex;
            align-items: center;
        }

        .theme-toggle, .notifications, .apps-menu {
            margin-right: 15px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f3f5;
            color: #666;
            cursor: pointer;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #e0e0e0;
        }

        /* Content wrapper */
        .content-wrapper {
            padding: 25px;
            overflow-y: auto;
        }
    </style>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>Diana Bonvini</h3>
        </div>

        <div class="sidebar-menu">
            <div class="menu-group">
                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Admin', 'action' => 'dashboard']) ?>" class="menu-item <?= $this->request->getParam('controller') === 'Admin' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="<?= $this->Url->build([
                    'prefix'     => 'Admin',
                    'controller' => 'Artworks',
                    'action'     => 'add'
                ]) ?>"
                   class="menu-item <?= $this->request->getParam('controller') === 'Artworks'
                   && $this->request->getParam('action') === 'add' ? 'active' : '' ?>">
                    <i class="fas fa-paint-brush"></i> Add Artwork
                </a>

                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'index']) ?>" class="menu-item <?= $this->request->getParam('controller') === 'Orders' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'index']) ?>" class="menu-item <?= $this->request->getParam('controller') === 'WritingServiceRequests' ? 'active' : '' ?>">
                    <i class="fas fa-pen"></i> Writing Services
                </a>
                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index']) ?>" class="menu-item <?= $this->request->getParam('controller') === 'Users' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'ContentBlocks', 'action' => 'index']) ?>" class="menu-item <?= $this->request->getParam('controller') === 'ContentBlocks' ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i> Content
                </a>
            </div>

            <div class="menu-group">
                <a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'Pages', 'action' => 'display', 'landing']) ?>" class="menu-item" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Website
                </a>
                <a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'Users', 'action' => 'logout']) ?>" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="content">
        <!-- Top Bar - Sticky Header -->
        <div class="top-bar-container">
            <div class="top-bar">
                <div class="search-form">
                </div>

                <div class="user-menu">
                    <div class="theme-toggle">
                        <i class="fas fa-sun"></i>
                    </div>
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="apps-menu">
                        <i class="fas fa-th"></i>
                    </div>
                    <img src="https://via.placeholder.com/35" class="user-avatar">
                </div>
            </div>
        </div>

        <!-- Main Content with proper scrolling -->
        <div class="content-wrapper">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<?= $this->Html->script('jquery-3.6.0.min') ?>
<?= $this->Html->script('bootstrap.bundle.min') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<?= $this->fetch('script') ?>
</body>
</html>
