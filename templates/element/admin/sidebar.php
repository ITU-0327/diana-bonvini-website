<?php
/**
 * Admin sidebar navigation element
 */
?>
<div class="sidebar-nav">
    <ul class="nav-list">
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>',
                ['controller' => 'Admin', 'action' => 'dashboard', 'prefix' => 'Admin'],
                ['escape' => false, 'class' => $this->request->getParam('action') === 'dashboard' ? 'active' : ''],
            ) ?>
        </li>

        <!-- Artworks Management -->
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-paint-brush"></i> <span>Artworks</span>',
                ['controller' => 'Artworks', 'action' => 'index', 'prefix' => 'Admin'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Artworks' ? 'active' : ''],
            ) ?>
        </li>

        <!-- Orders Management -->
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-shopping-cart"></i> <span>Orders</span>',
                ['controller' => 'Orders', 'action' => 'index', 'prefix' => 'Admin'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Orders' ? 'active' : ''],
            ) ?>
        </li>

        <!-- Writing Service Requests -->
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-pen"></i> <span>Writing Services</span>',
                ['controller' => 'WritingServiceRequests', 'action' => 'index', 'prefix' => 'Admin'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'WritingServiceRequests' ? 'active' : ''],
            ) ?>
        </li>

        <!-- Content Management -->
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-newspaper"></i> <span>Content Blocks</span>',
                ['controller' => 'ContentBlocks', 'action' => 'index', 'prefix' => 'Admin'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'ContentBlocks' ? 'active' : ''],
            ) ?>
        </li>

        <!-- Pages Management -->
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-file-alt"></i> <span>Pages</span>',
                ['controller' => 'Pages', 'action' => 'index', 'prefix' => 'Admin'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Pages' ? 'active' : ''],
            ) ?>
        </li>

        <!-- Users Management -->
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-users"></i> <span>Users</span>',
                ['controller' => 'Users', 'action' => 'index', 'prefix' => 'Admin'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Users' ? 'active' : ''],
            ) ?>
        </li>

        <!-- Settings -->
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-cogs"></i> <span>Settings</span>',
                ['controller' => 'Settings', 'action' => 'index', 'prefix' => 'Admin'],
                ['escape' => false, 'class' => $this->request->getParam('controller') === 'Settings' ? 'active' : ''],
            ) ?>
        </li>

        <!-- View Website -->
        <li class="nav-item">
            <?= $this->Html->link(
                '<i class="fas fa-external-link-alt"></i> <span>View Website</span>',
                ['controller' => 'Pages', 'action' => 'display', 'home', 'prefix' => false],
                ['escape' => false, 'target' => '_blank'],
            ) ?>
        </li>
    </ul>
</div>
