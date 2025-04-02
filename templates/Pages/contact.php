<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Contact Me');
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header with left underline -->
    <div class="flex flex-col items-start mb-8">
        <h1 class="text-3xl uppercase text-gray-800">Contact Me</h1>
        <div class="mt-1 w-16 h-[2px] bg-gray-800"></div>
    </div>

    <!-- Contact Card -->
    <div class="bg-white shadow rounded-lg p-6">
        <p class="text-gray-700 mb-6">
            If you have any questions about writing services, artwork, or custom commissions, feel free to reach out.
        </p>

        <!-- Contact Info -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Contact Info</h2>
            <ul class="space-y-1 text-gray-700">
                <li><strong>Email:</strong> diana@example.com</li>
                <li><strong>Phone:</strong> +61 400 123 456</li>
                <li><strong>Location:</strong> Adelaide, Australia</li>
            </ul>
        </div>

        <!-- Social Links -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">Follow Me</h2>
            <div class="flex gap-4 items-center text-gray-600">
                <a href="https://instagram.com/yourprofile" target="_blank" rel="noopener noreferrer"
                   class="flex items-center gap-2 hover:text-pink-500 transition">
                    <i data-lucide="instagram" class="w-5 h-5"></i>
                    <span class="sr-only">Instagram</span>
                </a>
                <a href="https://linkedin.com/in/yourprofile" target="_blank" rel="noopener noreferrer"
                   class="flex items-center gap-2 hover:text-blue-700 transition">
                    <i data-lucide="linkedin" class="w-5 h-5"></i>
                    <span class="sr-only">LinkedIn</span>
                </a>
            </div>
        </div>
    </div>
</div>
