<?php
/**
 * Admin top bar element
 *
 * @var \Cake\View\View $this
 */
?>
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
