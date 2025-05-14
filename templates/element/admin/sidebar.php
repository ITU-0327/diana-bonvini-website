<?php
/**
 * Admin sidebar navigation element
 * 
 * @var \App\View\AppView $this
 */
?>
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

            <?= $this->Html->link(
                '<i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>',
                ['prefix' => 'Admin', 'controller' => 'Admin', 'action' => 'dashboard'],
                ['class' => 'menu-item ' . $isActive('Admin', 'dashboard'), 'escape' => false]
            ) ?>

            <div class="menu-title"><span>Content Management</span></div>

            <?= $this->Html->link(
                '<i class="fas fa-paint-brush"></i> <span>Artworks</span>',
                ['prefix' => 'Admin', 'controller' => 'Artworks', 'action' => 'index'],
                ['class' => 'menu-item ' . $isActive('Artworks'), 'escape' => false]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-file-alt"></i> <span>Content Blocks</span>',
                ['prefix' => 'Admin', 'controller' => 'ContentBlocks', 'action' => 'index'],
                ['class' => 'menu-item ' . $isActive('ContentBlocks'), 'escape' => false]
            ) ?>
        </div>

        <div class="menu-group">
            <div class="menu-title"><span>Business</span></div>

            <?= $this->Html->link(
                '<i class="fas fa-shopping-cart"></i> <span>Orders</span>',
                ['prefix' => 'Admin', 'controller' => 'Orders', 'action' => 'index'],
                ['class' => 'menu-item ' . $isActive('Orders'), 'escape' => false]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-pen"></i> <span>Writing Services</span>',
                ['prefix' => 'Admin', 'controller' => 'WritingServiceRequests', 'action' => 'index'],
                ['class' => 'menu-item ' . $isActive('WritingServiceRequests'), 'escape' => false]
            ) ?>
        </div>

        <div class="menu-group">
            <div class="menu-title"><span>Administration</span></div>

            <?= $this->Html->link(
                '<i class="fas fa-users"></i> <span>Users</span>',
                ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
                ['class' => 'menu-item ' . $isActive('Users'), 'escape' => false]
            ) ?>

        </div>

        <div class="menu-group">
            <div class="menu-title"><span>Quick Links</span></div>

            <?= $this->Html->link(
                '<i class="fas fa-external-link-alt"></i> <span>View Website</span>',
                ['_name' => 'home'],
                ['class' => 'menu-item', 'escape' => false, 'target' => '_blank']
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-sign-out-alt"></i> <span>Logout</span>',
                ['prefix' => false, 'controller' => 'Users', 'action' => 'logout'],
                ['class' => 'menu-item', 'escape' => false]
            ) ?>
        </div>
    </div>
</nav>
