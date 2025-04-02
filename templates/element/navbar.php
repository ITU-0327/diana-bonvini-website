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
    <!-- Inline CSS for animated effects on menu, logo, and icons -->
    <style>
        /* Animated Gradient Underline for Menu Links */
        .nav-menu {
            display: flex;
            gap: 1rem;
        }
        .nav-menu li {
            position: relative;
        }
        .nav-menu a {
            font-weight: 600;
            color: #404041;
            padding: 0.5rem 0.75rem;
            transition: color 0.3s ease, transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .nav-menu a::before {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%) scaleX(0);
            transform-origin: center;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #008080, #7FFFD4);
            transition: transform 0.3s ease;
        }
        .nav-menu a:hover::before,
        .nav-menu a:focus::before {
            transform: translateX(-50%) scaleX(1);
        }
        .nav-menu a:hover,
        .nav-menu a:focus {
            color: #008080;
            transform: translateY(-2px);
        }

        /* Animated Effect for Logo */
        .nav-logo {
            position: relative;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .nav-logo::before {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%) scaleX(0);
            transform-origin: center;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, #008080, #7FFFD4);
            transition: transform 0.3s ease;
        }
        .nav-logo:hover::before,
        .nav-logo:focus::before {
            transform: translateX(-50%) scaleX(1);
        }
        .nav-logo:hover,
        .nav-logo:focus {
            color: #008080;
            transform: translateY(-2px);
        }

        /* Animated Effect for Icons (Cart and Profile) */
        .nav-icon {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .nav-icon:hover,
        .nav-icon:focus {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,128,128,0.3);
        }
    </style>

    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Left Side: Logo and Navigation Links -->
            <div class="flex items-center space-x-8">
                <!-- Logo with animated effect -->
                <a href="<?= $this->Url->build('/') ?>" class="flex items-center nav-logo">
                    <span class="font-bold text-2xl text-gray-800">diana bonvini.</span>
                </a>
                <!-- Reworked Navigation Menu -->
                <ul class="nav-menu">
                    <!-- Art Dropdown -->
                    <li class="relative group">
                        <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>">
                            Art
                            <svg class="inline ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <!-- Dropdown -->
                        <ul class="absolute left-0 top-full w-48 bg-white border border-gray-200 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
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
                            <svg class="inline ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        <!-- Dropdown -->
                        <ul class="absolute left-0 top-full w-64 bg-white border border-gray-200 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                            <?php if ($userType === 'admin'): ?>
                                <li>
                                    <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'adminIndex']) ?>"
                                       class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        Check All Requests
                                    </a>
                                </li>
                            <?php else: ?>
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
            <!-- Right Side: Shopping Cart and User Profile -->
            <div class="flex items-center space-x-6">
                <!-- Shopping Cart with animated icon effect -->
                <a href="<?= $this->Url->build(['controller' => 'Carts', 'action' => 'index']) ?>" class="relative nav-icon">
                    <?= $this->Html->image('navbar/shopping-cart.png', ['class' => 'h-6 w-6']) ?>
                </a>
                <!-- User Profile Dropdown with animated icon effect -->
                <?php if ($user): ?>
                    <div class="relative group">
            <span class="flex items-center justify-center h-9 w-9 rounded-full border border-gray-300 overflow-hidden nav-icon">
              <i data-lucide="user"></i>
            </span>
                        <div class="absolute right-0 top-full w-96 bg-white border border-gray-200 rounded shadow-lg p-6 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                            <h5 class="text-gray-900 font-bold mb-4 text-2xl">User Profile</h5>
                            <div class="flex items-center space-x-4">
                <span class="flex items-center justify-center h-20 w-20 rounded-full border border-gray-300 overflow-hidden">
                  <i data-lucide="user" class="w-16 h-16"></i>
                </span>
                                <div>
                                    <h4 class="text-gray-800 font-semibold text-xl"><?= h($user->first_name . ' ' . $user->last_name) ?></h4>
                                    <p class="text-sm text-gray-500 flex items-center space-x-2 mt-1">
                                        <i class="fa-solid fa-envelope"></i>
                                        <span><?= h($user->email) ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="border-b border-gray-200 my-3"></div>
                            <ul class="space-y-2">
                                <li class="menu-item">
                                    <a href="<?= $this->Url->build(['controller' => 'Orders', 'action' => 'index']) ?>" class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-box h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">My Orders</p>
                                            <p class="text-xs text-gray-500">View Purchased Art</p>
                                        </div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'index']) ?>" class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-pen-nib h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">My Services</p>
                                            <p class="text-xs text-gray-500">Writing &amp; Proofreading</p>
                                        </div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-user-cog h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">Account Settings</p>
                                            <p class="text-xs text-gray-500">Profile &amp; Password</p>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                            <?= $this->Form->postLink('Log Out', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'block w-full text-left px-4 py-2 mt-4 text-base text-gray-700 hover:bg-gray-100 rounded']) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div>
                        <?= $this->Html->link('Login', ['controller' => 'Users', 'action' => 'login'], ['class' => 'text-indigo-600 hover:text-indigo-500 font-semibold']) ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-800 hover:text-teal-500 focus:outline-none">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white/90 backdrop-blur-sm">
        <ul class="px-4 pt-4 pb-3 space-y-1">
            <li>
                <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>"
                   class="block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500">Art</a>
            </li>
            <li>
                <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'info']) ?>"
                   class="block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500">Writing Services</a>
            </li>
            <li>
                <a href="<?= $this->Url->build('/about') ?>"
                   class="block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500">About</a>
            </li>
            <li>
                <a href="<?= $this->Url->build('/contact') ?>"
                   class="block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500">Contact</a>
            </li>
            <?php if ($user): ?>
                <li>
                    <?= $this->Form->postLink('Log Out', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'block px-3 py-2 rounded-md text-base font-medium text-gray-800 hover:text-teal-500']) ?>
                </li>
            <?php else: ?>
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>"
                       class="block px-3 py-2 rounded-md text-base font-medium text-teal-500">Login</a>
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
