<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Artwork> $artworks
 */
?>

<?php
$user = $this->getRequest()->getAttribute('identity');
$userType = $user?->get('user_type');
?>

<div class="max-w-6xl mx-auto px-4 py-8">

    <h1 class="text-3xl font-bold mb-6">Art Gallery</h1>

    <!-- Filter Buttons: Only two options -->
    <div class="mb-6 flex space-x-2">
        <button id="filter-available" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            Art for Sale
        </button>
        <button id="filter-sold" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            Art Sold
        </button>

        <?php if ($userType === 'admin') : ?>
            <a href="<?= $this->Url->build(['controller' => 'Artworks', 'action' => 'add']) ?>"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                ➕ Add New Artwork
            </a>
        <?php endif; ?>

    </div>

    <!-- Artworks Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($artworks as $artwork) : ?>
            <!-- Single Artwork Card -->
            <a href="<?= $this->Url->build(['action' => 'view', $artwork->artwork_id]) ?>"
               class="artwork-card bg-white rounded shadow p-4 flex flex-col"
               data-status="<?= h($artwork->availability_status) ?>">
                <!-- Artwork Image -->
                <div class="mb-4">
                    <?= $this->Html->image($artwork->image_path, [
                        'alt' => $artwork->title,
                        'class' => 'max-w-full h-auto rounded',
                    ]) ?>
                </div>

                <!-- Artwork Details -->
                <h2 class="text-xl font-semibold mb-1"><?= h($artwork->title) ?></h2>
                <p class="text-gray-800 font-semibold mb-4">$<?= $this->Number->format($artwork->price) ?></p>

                <!-- Action Buttons or Sold Label -->
                <div class="mt-auto">
                    <?php if ($artwork->availability_status === 'available') : ?>
                        <div class="flex space-x-2">
                            <?= $this->Form->create(null, [
                                'url' => [
                                    'controller' => 'Carts',
                                    'action' => 'buyNow',
                                    $artwork->artwork_id,
                                ],
                            ]) ?>
                            <?= $this->Form->button('Buy Now', [
                                'class' => 'bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700',
                            ]) ?>
                            <?= $this->Form->end() ?>

                            <?= $this->Form->create(null, [
                                'url' => [
                                    'controller' => 'Carts',
                                    'action' => 'add',
                                    $artwork->artwork_id,
                                ],
                            ]) ?>
                            <?= $this->Form->button('Add to Cart', [
                                'class' => 'bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400',
                            ]) ?>
                            <?= $this->Form->end() ?>
                        </div>
                    <?php else : ?>
                        <div class="text-center">
                            <span class="text-red-600 font-bold">Sold</span>
                        </div>
                    <?php endif; ?>

                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to filter cards by status
        function filterCards(status) {
            document.querySelectorAll('.artwork-card').forEach(function(card) {
                // Display the card if its data-status matches the filter, otherwise hide it
                card.style.display = (card.getAttribute('data-status') === status) ? 'flex' : 'none';
            });
        }

        // Initially show available artworks
        filterCards('available');

        // Event listener for the "Show Available Art" button
        document.getElementById('filter-available').addEventListener('click', function() {
            filterCards('available');
        });

        // Event listener for the "Show Sold Art" button
        document.getElementById('filter-sold').addEventListener('click', function() {
            filterCards('sold');
        });
    });
</script>
