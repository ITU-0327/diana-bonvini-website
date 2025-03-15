<nav class="bg-white shadow">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Left Side: Logo and Navigation Links -->
            <div class="flex items-center space-x-8">
                <!-- Logo -->
                <a href="<?= $this->Url->build('/') ?>" class="flex items-center">
                    <span class="font-bold text-2xl text-gray-800">Diana Bonvini</span>
                </a>
                <!-- Navigation Menu -->
                <ul class="flex space-x-4">
                    <!-- Buy Art with Dropdown -->
                    <li class="menu-item relative group">
                        <a href="<?= $this->Url->build('/buy-art') ?>" class="block px-4 py-2 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded">
                            Buy Art
                            <svg class="inline ml-1 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>
                        <!-- Dropdown -->
                        <ul class="absolute left-0 top-full w-48 bg-white border border-gray-200 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                            <li class="menu-item"><a href="<?= $this->Url->build('/buy-art/all') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">All Art</a></li>
                            <li class="menu-item"><a href="<?= $this->Url->build('/buy-art/new') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">New Arrivals</a></li>
                            <li class="menu-item"><a href="<?= $this->Url->build('/buy-art/collections') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Collections</a></li>
                        </ul>
                    </li>
                    <!-- Writing & Proofreading Services with Dropdown -->
                    <li class="menu-item relative group">
                        <a href="<?= $this->Url->build('/writing-proofreading') ?>" class="block px-4 py-2 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded">
                            Writing &amp; Proofreading Services
                            <svg class="inline ml-1 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>
                        <!-- Dropdown -->
                        <ul class="absolute left-0 top-full w-64 bg-white border border-gray-200 rounded shadow-lg opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                            <li class="menu-item"><a href="<?= $this->Url->build('/writing/creative') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Creative Writing</a></li>
                            <li class="menu-item"><a href="<?= $this->Url->build('/writing/proofreading') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Proofreading</a></li>
                            <li class="menu-item"><a href="<?= $this->Url->build('/writing/editing') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Editing Services</a></li>
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
                <a href="<?= $this->Url->build('/shopping-cart') ?>" class="relative">
                    <svg class="h-6 w-6 text-gray-700 hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.4 5.6a1 1 0 001 1.4h12a1 1 0 001-1.4L17 13M7 13h10"></path>
                    </svg>
                    <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white bg-red-600 rounded-full">3</span>
                </a>
                <!-- User Profile with "Card" Style Dropdown -->
                <div class="relative group">
                    <button type="button" class="flex items-center focus:outline-none">
                        <img class="h-8 w-8 rounded-full" src="/img/user-placeholder.png" alt="User Profile">
                        <svg class="ml-1 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <!-- Dropdown Menu styled like your screenshot -->
                    <div class="absolute right-0 top-full w-72 bg-white border border-gray-200 rounded shadow-lg p-4 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-300 z-10">
                        <!-- User Info -->
                        <div class="flex items-center space-x-3 mb-4">
                            <img class="h-12 w-12 rounded-full" src="/img/user-placeholder.png" alt="User Avatar">
                            <div>
                                <h4 class="text-gray-800 font-semibold text-lg">Diana Bonvini</h4>
                                <p class="text-sm text-gray-500">Business Owner</p>
                                <p class="text-sm text-gray-500">dbdesignsaustralia@gmail.com</p>
                            </div>
                        </div>
                        <ul class="space-y-3">
                            <li class="menu-item flex items-center space-x-3">
                                <!-- Icon Placeholder -->
                                <div class="h-6 w-6 bg-gray-300 rounded"></div>
                                <div>
                                    <p class="text-gray-700 font-medium">My Orders</p>
                                    <p class="text-xs text-gray-500">View Purchased Art</p>
                                </div>
                            </li>
                            <li class="menu-item flex items-center space-x-3">
                                <!-- Icon Placeholder -->
                                <div class="h-6 w-6 bg-gray-300 rounded"></div>
                                <div>
                                    <p class="text-gray-700 font-medium">My Services</p>
                                    <p class="text-xs text-gray-500">Writing & Proofreading</p>
                                </div>
                            </li>
                            <li class="menu-item flex items-center space-x-3">
                                <!-- Icon Placeholder -->
                                <div class="h-6 w-6 bg-gray-300 rounded"></div>
                                <div>
                                    <p class="text-gray-700 font-medium">My Bookings</p>
                                    <p class="text-xs text-gray-500">Scheduled Sessions</p>
                                </div>
                            </li>
                            <li class="menu-item flex items-center space-x-3">
                                <!-- Icon Placeholder -->
                                <div class="h-6 w-6 bg-gray-300 rounded"></div>
                                <div>
                                    <p class="text-gray-700 font-medium">Account Settings</p>
                                    <p class="text-xs text-gray-500">Profile &amp; Password</p>
                                </div>
                            </li>
                        </ul>
                        <!-- Log Out Button -->
                        <?= $this->Form->postLink(
                            'Log Out',
                            ['controller' => 'Users', 'action' => 'logout'],
                            ['class' => 'block w-full text-left px-4 py-2 mt-4 text-sm text-gray-700 hover:bg-gray-100 rounded']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
