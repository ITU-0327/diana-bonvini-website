<?php
/**
 * Navigation Bar for Diana Bonvini Website
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User|null $user
 */

$user = $this->getRequest()->getAttribute('identity');
$userType = $user?->get('user_type');
?>
<nav class="bg-white shadow">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Left: Logo (always visible) -->
            <div class="flex items-center">
                <a href="<?= $this->Url->build('/') ?>" class="flex items-center nav-logo">
                    <span class="font-bold text-2xl text-gray-800"><?= $this->ContentBlock->text('logo') ?></span>
                </a>
            </div>

            <!-- Middle: Navigation Menu (visible only on large screens, aligned to left) -->
            <div class="hidden md:flex items-center">
                <ul class="nav-menu">
                    <!-- Art Dropdown -->
                    <li class="relative group">
                        <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>">
                            Art
                            <svg class="inline ml-1 h-4 w-4" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <ul class="absolute left-0 top-full mt-0 min-w-[12rem] bg-white border border-gray-200 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-20">
                            <li>
                                <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    All Art
                                </a>
                            </li>
                            <li>
                                <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    New Arrivals
                                </a>
                            </li>
                            <li>
                                <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Collections
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Writing Services Dropdown -->
                    <li class="relative group">
                        <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'info']) ?>">
                            Writing Services
                            <svg class="inline ml-1 h-4 w-4" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <ul class="absolute left-0 top-full w-64 bg-white border border-gray-200 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                            <?php if ($userType === 'admin') : ?>
                                <li>
                                    <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'adminIndex']) ?>"
                                       class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        Check All Requests
                                    </a>
                                </li>
                            <?php else : ?>
                                <li>
                                    <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'add']) ?>"
                                       class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        Make a Request
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'index']) ?>"
                                       class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        View My Requests
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- About -->
                    <li>
                        <a href="<?= $this->Url->build('/about') ?>">About</a>
                    </li>

                    <!-- Contact -->
                    <li>
                        <a href="<?= $this->Url->build('/contact') ?>">Contact</a>
                    </li>
                </ul>
            </div>

            <!-- Right: Cart and User Profile (visible only on large screens) -->
            <div class="hidden md:flex items-center space-x-6">
                <!-- Cart -->
                <a href="<?= $this->Url->build(['controller' => 'Carts', 'action' => 'index']) ?>" class="relative">
                    <?= $this->Html->image('navbar/shopping-cart.png', ['class' => 'h-6 w-6']) ?>
                </a>
                <!-- User Profile or Login -->
                <?php if ($user) : ?>
                    <div class="relative group" id="profileDropdownWrapper">
                        <div class="w-16 h-16 rounded-full absolute -top-3 -left-3 z-10 pointer-events-auto group-hover:bg-transparent cursor-pointer"></div>
                        <span class="relative z-20 flex items-center justify-center h-9 w-9 rounded-full border border-gray-300 overflow-hidden nav-icon cursor-pointer">
                            <i data-lucide="user"></i>
                        </span>
                        <div class="absolute right-0 top-full mt-1 w-96 bg-white border border-gray-200 rounded shadow-lg p-6 opacity-0 translate-y-2 pointer-events-none group-hover:translate-y-0 group-hover:opacity-100 group-hover:pointer-events-auto transition-all duration-300 z-20">
                            <h5 class="text-gray-900 font-bold mb-4 text-2xl">User Profile</h5>
                            <div class="flex items-center space-x-4">
                                <span class="flex items-center justify-center h-20 w-20 rounded-full border border-gray-300 overflow-hidden">
                                    <i data-lucide="user" class="w-16 h-16"></i>
                                </span>

                                <div>
                                    <h4 class="text-gray-800 font-semibold text-xl">
                                        <?= h($user->first_name . ' ' . $user->last_name) ?>
                                    </h4>
                                    <p class="text-sm text-gray-500 flex items-center space-x-2 mt-1">
                                        <i class="fa-solid fa-envelope"></i>
                                        <span><?= h($user->email) ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="border-b border-gray-200 my-3"></div>
                            <ul class="space-y-2">
                                <li class="menu-item">
                                    <a href="<?= $this->Url->build(['controller' => 'Orders', 'action' => 'index']) ?>"
                                       class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-box h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">My Orders</p>
                                            <p class="text-xs text-gray-500">View Purchased Art</p>
                                        </div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'index']) ?>"
                                       class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-pen-nib h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">My Services</p>
                                            <p class="text-xs text-gray-500">Writing &amp; Proofreading</p>
                                        </div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'edit', $user->user_id]) ?>"
                                       class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-user-cog h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">Account Settings</p>
                                            <p class="text-xs text-gray-500">Profile &amp; Password</p>
                                        </div>
                                    </a>
                                </li>
                            </ul>

                            <!-- Log Out Button -->
                            <?= $this->Form->postLink(
                                'Log Out',
                                ['controller' => 'Users', 'action' => 'logout'],
                                [
                                    'class' => 'block w-full text-left px-4 py-2 mt-4 text-base text-gray-700 hover:bg-gray-100 rounded',
                                ],
                            ) ?>
                        </div>
                    </div>
                <?php else : ?>
                    <div>
                        <?= $this->Html->link('Login', ['controller' => 'Users', 'action' => 'login'], ['class' => 'text-indigo-600 hover:text-indigo-500 font-semibold']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button (only logo and this button are visible on small screens) -->
            <div class="block md:hidden">
                <button id="mobile-menu-button" class="text-gray-800 hover:text-teal-500 focus:outline-none">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu (hidden by default, toggled by the hamburger button) -->
    <div id="mobile-menu" class="hidden md:hidden bg-white/90 backdrop-blur-sm border-t border-gray-200">
        <ul class="px-4 pt-4 pb-3 space-y-1">
            <li>
                <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500">
                    Art
                </a>
            </li>
            <li>
                <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'info']) ?>"
                   class="block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500">
                    Writing Services
                </a>
            </li>
            <li>
                <a href="<?= $this->Url->build('/about') ?>"
                   class="block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500">
                    About
                </a>
            </li>
            <li>
                <a href="<?= $this->Url->build('/contact') ?>"
                   class="block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500">
                    Contact
                </a>
            </li>
            <?php if ($user) : ?>
                <li class="mt-2 border-t border-gray-200 pt-3">
                    <a href="<?= $this->Url->build(['controller' => 'Carts', 'action' => 'index']) ?>"
                       class="flex items-center space-x-2 px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500 nav-icon">
                        <?= $this->Html->image('navbar/shopping-cart.png', ['class' => 'h-5 w-5']) ?>
                        <span>Cart</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Orders', 'action' => 'index']) ?>"
                       class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-teal-500">
                        My Orders
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'index']) ?>"
                       class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-teal-500">
                        My Services
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'edit', $user->user_id]) ?>"
                       class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-teal-500">
                        Account Settings
                    </a>
                </li>
                <li class="mt-2 border-t border-gray-200 pt-3">
                    <?= $this->Form->postLink('Log Out', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'block px-3 py-2 text-base font-medium text-gray-700 hover:text-teal-500 rounded']) ?>
                </li>
            <?php else : ?>
                <li class="mt-2 border-t border-gray-200 pt-3">
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>"
                       class="block px-3 py-2 rounded-md text-base font-medium text-teal-500">
                        Login
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <script>
        const btn = document.getElementById('mobile-menu-button');
        const menu = document.getElementById('mobile-menu');
        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    </script>
</nav>
