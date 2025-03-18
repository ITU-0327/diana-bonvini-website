<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User|null $user
 */

$user = $this->getRequest()->getAttribute('identity');
?>
<nav class="bg-white shadow">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Left Side: Logo and Navigation Links -->
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <a href="<?= $this->Url->build('/') ?>" class="flex items-center">
                    <span class="font-bold text-2xl text-gray-800">diana bonvini.</span>
                </a>
                <!-- Navigation Menu -->
                <ul class="flex space-x-4">
                    <!-- Buy Art with Dropdown -->
                    <li class="menu-item relative group">
                        <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>"
                            class="block px-4 py-2 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded">
                            Art
                            <svg class="inline ml-1 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>
                        <!-- Dropdown -->
                        <ul class="absolute left-0 top-full w-48 bg-white border border-gray-200 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                            <li class="menu-item">
                                <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    All Art
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    New Arrivals
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'index']) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Collections
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Writing & Proofreading Services with Dropdown -->
                    <li class="menu-item relative group">
                        <!-- Main link: Goes to WritingServiceRequests/index -->
                        <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'index']) ?>"
                           class="block px-4 py-2 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded">
                            Writing Services
                            <svg class="inline ml-1 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>

                        <!-- Dropdown -->
                        <ul class="absolute left-0 top-full w-64 bg-white border border-gray-200 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                            <li class="menu-item">
                                <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'info', '?' => ['service' => 'creative']]) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Creative Writing
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'info', '?' => ['service' => 'proofreading']]) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Proofreading
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="<?= $this->Url->build(['controller' => 'WritingServiceRequests', 'action' => 'info', '?' => ['service' => 'editing']]) ?>"
                                   class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Editing Services
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Simple Menu Items -->
                    <li class="menu-item">
                        <a href="<?= $this->Url->build('/about') ?>" class="block px-4 py-2 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded">About</a>
                    </li>
                    <li class="menu-item">
                        <a href="<?= $this->Url->build('/contact') ?>" class="block px-4 py-2 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded">Contact</a>
                    </li>
                </ul>
            </div>
            <!-- Right Side: Shopping Cart and User Profile -->
            <div class="flex items-center space-x-6">
                <!-- Shopping Cart -->
                <a href="<?= $this->Url->build(['controller' => 'Carts', 'action' => 'index']) ?>" class="relative">
                    <?= $this->Html->image('navbar/shopping-cart.png', ['class' => 'h-6 w-6']) ?>
                </a>
                <!-- User Profile with "Card" Style Dropdown -->
                <?php if ($user) : ?>
                    <!-- Profile Dropdown for logged-in users -->
                    <div class="relative group">
                        <button type="button" class="flex items-center focus:outline-none hover:bg-transparent">
                            <?= $this->Html->image('user-placeholder.jpg', [
                                'class' => 'h-9 w-9 rounded-full',
                                'alt' => 'User Profile',
                            ]) ?>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 top-full w-96 bg-white border border-gray-200 rounded shadow-lg p-6 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                            <!-- Heading -->
                            <h5 class="text-gray-900 font-bold mb-4 text-2xl">User Profile</h5>

                            <!-- User Info -->
                            <div class="flex items-center space-x-4">
                                <?= $this->Html->image('user-placeholder.jpg', [
                                    'class' => 'h-20 w-20 rounded-full',
                                    'alt' => 'User Profile',
                                ]) ?>
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

                            <!-- Divider -->
                            <div class="border-b border-gray-200 my-3"></div>

                            <!-- Menu Items (Icons and Text Unchanged) -->
                            <ul class="space-y-2">
                                <li class="menu-item">
                                    <a href="#" class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-box h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">My Orders</p>
                                            <p class="text-xs text-gray-500">View Purchased Art</p>
                                        </div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-pen-nib h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">My Services</p>
                                            <p class="text-xs text-gray-500">Writing &amp; Proofreading</p>
                                        </div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="#" class="flex items-center space-x-3 p-2 pl-3 hover:bg-gray-100 rounded transition duration-200">
                                        <i class="fa-solid fa-calendar-check h-6 w-6 text-indigo-500"></i>
                                        <div>
                                            <p class="text-gray-700 font-medium text-base">My Bookings</p>
                                            <p class="text-xs text-gray-500">Scheduled Sessions</p>
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

                            <!-- Log Out Button -->
                            <?= $this->Form->postLink(
                                'Log Out',
                                ['controller' => 'Users', 'action' => 'logout'],
                                [
                                    'class' => 'block w-full text-left px-4 py-2 mt-4 text-base text-gray-700 hover:bg-gray-100 rounded'
                                ]
                            ) ?>
                        </div>
                    </div>
                <?php else : ?>
                    <div>
                        <?= $this->Html->link('Login', ['controller' => 'Users', 'action' => 'login'], [
                            'class' => 'text-indigo-600 hover:text-indigo-500 font-semibold',
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
